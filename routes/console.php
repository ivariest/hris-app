<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Support\OrganizationExcelImporter;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('organization:import-excel {path}', function (string $path) {
    $summary = app(OrganizationExcelImporter::class)->import($path);

    $this->info(sprintf(
        'Imported %d rows: %d companies, %d departments, %d sub departments, %d positions.',
        $summary['rows'],
        $summary['companies'],
        $summary['departments'],
        $summary['sub_departments'],
        $summary['positions'],
    ));
})->purpose('Import organization hierarchy from an Excel file');
