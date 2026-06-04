<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('annual_leave_collectives', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->date('date_from');
            $table->date('date_to');
            $table->string('name', 150);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('annual_leave_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->date('date_from');
            $table->date('date_to');
            $table->decimal('days', 5, 2)->unsigned();
            $table->string('leave_type', 50);
            $table->string('source_type', 30)->default('manual');
            $table->string('balance_effect', 30)->default('annual_leave');
            $table->timestamp('written_off_at')->nullable();
            $table->foreignId('attendance_adjustment_id')->nullable()->constrained('attendance_adjustments')->cascadeOnDelete();
            $table->foreignId('annual_leave_collective_id')->nullable()->constrained('annual_leave_collectives')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['employee_id', 'year']);
            $table->unique('attendance_adjustment_id');
            $table->unique(['employee_id', 'annual_leave_collective_id'], 'leave_employee_collective_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('annual_leave_transactions');
        Schema::dropIfExists('annual_leave_collectives');
    }
};
