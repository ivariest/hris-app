<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('general_affair_vehicles', function (Blueprint $table) {
            if (! Schema::hasColumn('general_affair_vehicles', 'location_id')) {
                $table->foreignId('location_id')->nullable()->after('manufacture_year')->constrained('locations')->nullOnDelete();
            }

            if (! Schema::hasColumn('general_affair_vehicles', 'driver_id')) {
                $table->foreignId('driver_id')->nullable()->after('location_id')->constrained('employees')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('general_affair_vehicles', function (Blueprint $table) {
            if (Schema::hasColumn('general_affair_vehicles', 'driver_id')) {
                $table->dropConstrainedForeignId('driver_id');
            }

            if (Schema::hasColumn('general_affair_vehicles', 'location_id')) {
                $table->dropConstrainedForeignId('location_id');
            }
        });
    }
};
