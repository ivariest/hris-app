<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('general_affair_vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('plate_number', 30)->unique();
            $table->string('vehicle_type', 100)->nullable();
            $table->string('brand', 100)->nullable();
            $table->year('manufacture_year')->nullable();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('ownership_status', 30)->default('asset');
            $table->date('tax_valid_until')->nullable();
            $table->date('plate_valid_until')->nullable();
            $table->string('stnk_document_path')->nullable();
            $table->date('kir_valid_until')->nullable();
            $table->string('kir_document_path')->nullable();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('general_affair_vehicle_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('general_affair_vehicles')->cascadeOnDelete();
            $table->date('service_date');
            $table->string('service_type', 100);
            $table->string('workshop_name', 150)->nullable();
            $table->unsignedInteger('odometer')->nullable();
            $table->decimal('cost', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->date('next_service_date')->nullable();
            $table->unsignedInteger('next_service_odometer')->nullable();
            $table->string('document_path')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['vehicle_id', 'service_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('general_affair_vehicle_services');
        Schema::dropIfExists('general_affair_vehicles');
    }
};
