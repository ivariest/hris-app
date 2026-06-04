<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('general_affair_vehicles', function (Blueprint $table) {
            if (! Schema::hasColumn('general_affair_vehicles', 'tax_valid_until')) {
                $table->date('tax_valid_until')->nullable()->after('ownership_status');
            }

            if (! Schema::hasColumn('general_affair_vehicles', 'plate_valid_until')) {
                $table->date('plate_valid_until')->nullable()->after('tax_valid_until');
            }
        });

        if (
            Schema::hasColumn('general_affair_vehicles', 'stnk_valid_until')
            && Schema::hasColumn('general_affair_vehicles', 'tax_valid_until')
        ) {
            DB::table('general_affair_vehicles')
                ->whereNull('tax_valid_until')
                ->update(['tax_valid_until' => DB::raw('stnk_valid_until')]);
        }

        Schema::table('general_affair_vehicles', function (Blueprint $table) {
            $columns = collect([
                'vehicle_code',
                'vehicle_name',
                'model',
                'color',
                'status',
                'stnk_number',
                'stnk_valid_until',
                'kir_number',
            ])
                ->filter(fn (string $column) => Schema::hasColumn('general_affair_vehicles', $column))
                ->values()
                ->all();

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }

    public function down(): void
    {
        Schema::table('general_affair_vehicles', function (Blueprint $table) {
            if (! Schema::hasColumn('general_affair_vehicles', 'vehicle_code')) {
                $table->string('vehicle_code', 50)->nullable()->unique()->after('id');
            }

            if (! Schema::hasColumn('general_affair_vehicles', 'vehicle_name')) {
                $table->string('vehicle_name', 150)->nullable()->after('plate_number');
            }

            if (! Schema::hasColumn('general_affair_vehicles', 'model')) {
                $table->string('model', 100)->nullable()->after('brand');
            }

            if (! Schema::hasColumn('general_affair_vehicles', 'color')) {
                $table->string('color', 50)->nullable()->after('manufacture_year');
            }

            if (! Schema::hasColumn('general_affair_vehicles', 'status')) {
                $table->string('status', 30)->default('active')->after('ownership_status');
            }

            if (! Schema::hasColumn('general_affair_vehicles', 'stnk_number')) {
                $table->string('stnk_number', 100)->nullable()->after('status');
            }

            if (! Schema::hasColumn('general_affair_vehicles', 'stnk_valid_until')) {
                $table->date('stnk_valid_until')->nullable()->after('stnk_number');
            }

            if (! Schema::hasColumn('general_affair_vehicles', 'kir_number')) {
                $table->string('kir_number', 100)->nullable()->after('stnk_document_path');
            }
        });

        Schema::table('general_affair_vehicles', function (Blueprint $table) {
            $columns = collect(['tax_valid_until', 'plate_valid_until'])
                ->filter(fn (string $column) => Schema::hasColumn('general_affair_vehicles', $column))
                ->values()
                ->all();

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
