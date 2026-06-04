<?php

namespace App\Support;

use App\Models\Company;
use App\Models\Department;
use App\Models\Position;
use App\Models\SubDepartment;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

class OrganizationExcelImporter
{
    public function import(string $path): array
    {
        $fullPath = realpath($path);

        if (! $fullPath || ! is_file($fullPath)) {
            throw new RuntimeException("Excel file not found: {$path}");
        }

        $rows = $this->readRows($fullPath);

        $summary = [
            'rows' => 0,
            'companies' => 0,
            'departments' => 0,
            'sub_departments' => 0,
            'positions' => 0,
        ];

        DB::transaction(function () use ($rows, &$summary): void {
            foreach ($rows as $row) {
                [$companyName, $departmentName, $subDepartmentName, $positionName] = $row;

                $companyName = trim((string) $companyName);
                $departmentName = trim((string) $departmentName);
                $subDepartmentName = trim((string) $subDepartmentName);
                $positionName = trim((string) $positionName);

                if ($companyName === '' || $departmentName === '' || $subDepartmentName === '' || $positionName === '') {
                    continue;
                }

                $summary['rows']++;

                $company = Company::firstOrCreate(
                    ['company_name' => $companyName],
                    ['company_address' => null]
                );

                if ($company->wasRecentlyCreated) {
                    $summary['companies']++;
                }

                $department = Department::firstOrCreate(
                    [
                        'company_id' => $company->id,
                        'department_name' => $departmentName,
                    ]
                );

                if ($department->wasRecentlyCreated) {
                    $summary['departments']++;
                }

                $subDepartment = SubDepartment::firstOrCreate(
                    [
                        'department_id' => $department->id,
                        'sub_department_name' => $subDepartmentName,
                    ]
                );

                if ($subDepartment->wasRecentlyCreated) {
                    $summary['sub_departments']++;
                }

                $position = Position::firstOrCreate(
                    [
                        'sub_department_id' => $subDepartment->id,
                        'position_name' => $positionName,
                    ]
                );

                if ($position->wasRecentlyCreated) {
                    $summary['positions']++;
                }
            }
        });

        return $summary;
    }

    /**
     * @return array<int, array{0:string,1:string,2:string,3:string}>
     */
    private function readRows(string $path): array
    {
        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw new RuntimeException("Unable to open Excel file: {$path}");
        }

        $sharedStrings = $this->readSharedStrings($zip);
        $sheetXml = $this->readSheetXml($zip, 0);

        $rows = [];
        $sheetData = $sheetXml->sheetData->row ?? [];

        foreach ($sheetData as $row) {
            $values = [];

            foreach ($row->c as $cell) {
                $values[] = $this->cellValue($cell, $sharedStrings);
            }

            if (count($values) < 4) {
                $values = array_pad($values, 4, '');
            }

            $rows[] = array_slice($values, 0, 4);
        }

        $zip->close();

        return $rows;
    }

    /**
     * @return array<int, string>
     */
    private function readSharedStrings(ZipArchive $zip): array
    {
        if ($zip->locateName('xl/sharedStrings.xml') === false) {
            return [];
        }

        $xml = new SimpleXMLElement($zip->getFromName('xl/sharedStrings.xml'));
        $xml->registerXPathNamespace('a', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        $strings = [];
        foreach ($xml->xpath('//a:si') ?: [] as $item) {
            $item->registerXPathNamespace('a', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            $nodes = $item->xpath('.//a:t') ?: [];
            $strings[] = implode('', array_map(static fn ($node) => (string) $node, $nodes));
        }

        return $strings;
    }

    private function readSheetXml(ZipArchive $zip, int $index): SimpleXMLElement
    {
        if ($index !== 0) {
            throw new RuntimeException('This importer only supports the first worksheet.');
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');

        if ($sheetXml === false) {
            throw new RuntimeException('Unable to read the first worksheet.');
        }

        return new SimpleXMLElement($sheetXml);
    }

    private function cellValue(SimpleXMLElement $cell, array $sharedStrings): string
    {
        $type = (string) ($cell['t'] ?? '');
        $value = isset($cell->v) ? (string) $cell->v : '';

        if ($type === 's') {
            return $sharedStrings[(int) $value] ?? '';
        }

        if ($type === 'inlineStr') {
            return (string) ($cell->is->t ?? '');
        }

        return $value;
    }

}
