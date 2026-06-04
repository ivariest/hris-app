<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('promotion_number', 50)->nullable();
            $table->string('promotion_type', 30);
            $table->foreignId('old_position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->foreignId('old_level_id')->nullable()->constrained('position_levels')->nullOnDelete();
            $table->foreignId('new_position_id')->constrained('positions')->restrictOnDelete();
            $table->foreignId('new_level_id')->constrained('position_levels')->restrictOnDelete();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('promotion_form_path')->nullable();
            $table->string('appointment_letter_path')->nullable();
            $table->text('notes')->nullable();
            $table->string('record_status', 20)->default('ACTIVE');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_promotions');
    }
};
