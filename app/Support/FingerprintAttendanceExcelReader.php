<?php

namespace App\Support;

use Carbon\Carbon;
use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

class FingerprintAttendanceExcelReader
{
    /**
     * @return array<int, array{attendance_id:string,fingerprint_name:?string,raw_scan_date:string,scan_date:?string,check_in:?string,check_out:?string,row_number:int}>
     */
    public function read(string $path, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $fullPath = realpath($path);
        $rangeStart = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : null;
        $rangeEnd = $dateTo ? Carbon::parse($dateTo)->endOfDay() : null;

        if (! $fullPath || ! is_file($fullPath)) {
            throw new RuntimeException("Excel file not found: {$path}");
        }

        $zip = new ZipArchive();

        if ($zip->open($fullPath) !== true) {
            throw new RuntimeException("Unable to open Excel file: {$path}");
        }

        $sharedStrings = $this->readSharedStrings($zip);
        $sheetXml = $this->readSheetXml($zip);
        $rows = [];

        foreach ($sheetXml->sheetData->row ?? [] as $row) {
            $rowNumber = (int) ($row['r'] ?? 0);
            $cells = [];

            foreach ($row->c as $cell) {
                $reference = (string) ($cell['r'] ?? '');
                $column = preg_replace('/\d+/', '', $reference);
                $cells[$column] = $this->cellValue($cell, $sharedStrings);
            }

            $attendanceId = trim((string) ($cells['A'] ?? ''));
            $fingerprintName = trim((string) ($cells['B'] ?? ''));
            $scanDate = trim((string) ($cells['C'] ?? ''));
            $checkIn = trim((string) ($cells['D'] ?? ''));
            $checkOut = trim((string) ($cells['E'] ?? ''));

            if ($attendanceId === '' && $fingerprintName === '' && $scanDate === '' && $checkIn === '' && $checkOut === '') {
                continue;
            }

            if ($this->looksLikeHeader($attendanceId, $scanDate)) {
                continue;
            }

            $rows[] = [
                'attendance_id' => $attendanceId,
                'fingerprint_name' => $fingerprintName !== '' ? $fingerprintName : null,
                'raw_scan_date' => $scanDate,
                'scan_date' => $this->normalizeDate($scanDate, $rangeStart, $rangeEnd),
                'check_in' => $this->normalizeTime($checkIn),
                'check_out' => $this->normalizeTime($checkOut),
                'row_number' => $rowNumber,
            ];
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

    private function readSheetXml(ZipArchive $zip): SimpleXMLElement
    {
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

    private function looksLikeHeader(string $attendanceId, string $scanDate): bool
    {
        $combined = strtoupper($attendanceId.' '.$scanDate);

        return str_contains($combined, 'ID ABSEN')
            || str_contains($combined, 'TANGGAL');
    }

    private function normalizeDate(string $value, ?Carbon $rangeStart = null, ?Carbon $rangeEnd = null): ?string
    {
        if ($value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return $this->dateFromExcelSerial((float) $value, $rangeStart, $rangeEnd);
        }

        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})$/', $value, $matches)) {
            return $this->dateFromNumericParts(
                (int) $matches[1],
                (int) $matches[2],
                (int) $matches[3],
                $rangeStart,
                $rangeEnd
            );
        }

        foreach (['Y-m-d', 'n/j/Y', 'm/d/Y', 'd/m/Y', 'd-m-Y', 'd M Y', 'd F Y'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);
                if ($date !== false && $this->dateIsInRange($date, $rangeStart, $rangeEnd)) {
                    return $date->toDateString();
                }
            } catch (\Throwable) {
                continue;
            }
        }

        try {
            $date = Carbon::parse($value);

            return $this->dateIsInRange($date, $rangeStart, $rangeEnd) ? $date->toDateString() : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function dateFromNumericParts(int $first, int $second, int $year, ?Carbon $rangeStart, ?Carbon $rangeEnd): ?string
    {
        $year = $year < 100 ? 2000 + $year : $year;
        $candidates = [];

        if (checkdate($first, $second, $year)) {
            $candidates[] = Carbon::create($year, $first, $second)->startOfDay();
        }

        if (checkdate($second, $first, $year)) {
            $candidates[] = Carbon::create($year, $second, $first)->startOfDay();
        }

        $candidates = collect($candidates)
            ->unique(fn (Carbon $date) => $date->toDateString())
            ->values();

        if ($rangeStart && $rangeEnd) {
            $inRange = $candidates->first(fn (Carbon $date) => $this->dateIsInRange($date, $rangeStart, $rangeEnd));

            return $inRange?->toDateString();
        }

        return $candidates->first()?->toDateString();
    }

    private function dateFromExcelSerial(float $value, ?Carbon $rangeStart, ?Carbon $rangeEnd): ?string
    {
        $date = Carbon::create(1899, 12, 30)->addDays((int) floor($value))->startOfDay();
        $candidates = collect([$date]);

        // Some fingerprint exports contain Excel serials created after Excel interpreted
        // d/m input as m/d. If a period is supplied, try the swapped day/month as well.
        if ($date->day <= 12 && checkdate($date->day, $date->month, $date->year)) {
            $candidates->push(Carbon::create($date->year, $date->day, $date->month)->startOfDay());
        }

        $candidates = $candidates
            ->unique(fn (Carbon $candidate) => $candidate->toDateString())
            ->values();

        if ($rangeStart && $rangeEnd) {
            $inRange = $candidates->first(fn (Carbon $candidate) => $this->dateIsInRange($candidate, $rangeStart, $rangeEnd));

            return $inRange?->toDateString();
        }

        return $date->toDateString();
    }

    private function dateIsInRange(Carbon $date, ?Carbon $rangeStart, ?Carbon $rangeEnd): bool
    {
        if (! $rangeStart || ! $rangeEnd) {
            return true;
        }

        return $date->betweenIncluded($rangeStart, $rangeEnd);
    }

    private function normalizeTime(string $value): ?string
    {
        if ($value === '') {
            return null;
        }

        if (preg_match('/^(\d{1,2})\.(\d{1,2})(?:\.(\d{1,2}))?$/', $value, $matches)) {
            $hour = (int) $matches[1];
            $minute = (int) $matches[2];
            $second = isset($matches[3]) ? (int) $matches[3] : 0;

            if ($hour <= 23 && $minute <= 59 && $second <= 59) {
                return sprintf('%02d:%02d:%02d', $hour, $minute, $second);
            }
        }

        if (is_numeric($value)) {
            $seconds = (int) round(((float) $value - floor((float) $value)) * 86400);

            return sprintf('%02d:%02d:%02d', intdiv($seconds, 3600), intdiv($seconds % 3600, 60), $seconds % 60);
        }

        foreach (['H:i:s', 'H:i', 'g:i A', 'g:i:s A'] as $format) {
            try {
                $time = Carbon::createFromFormat($format, strtoupper($value));
                if ($time !== false) {
                    return $time->format('H:i:s');
                }
            } catch (\Throwable) {
                continue;
            }
        }

        try {
            return Carbon::parse($value)->format('H:i:s');
        } catch (\Throwable) {
            return null;
        }
    }
}
