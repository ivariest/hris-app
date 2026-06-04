<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('position_id')->constrained('positions')->restrictOnDelete();
            $table->string('area', 100)->nullable();
            $table->string('rayon', 100)->nullable();
            $table->foreignId('location_id')->constrained('locations')->restrictOnDelete();
            $table->foreignId('atasan_langsung')->nullable()->constrained('employees')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_positions');
    }
};
