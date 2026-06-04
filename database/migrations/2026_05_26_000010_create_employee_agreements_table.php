<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_agreements', function (Blueprint $table) {
            $table->id();
            $table->string('agreement_number', 50)->unique();
            $table->foreignId('recruitment_request_id')->nullable()->constrained('recruitment_requests')->nullOnDelete();
            $table->string('agreement_status', 30);
            $table->string('employee_name', 150);
            $table->string('place_of_birth', 100)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('religion', 30)->nullable();
            $table->string('id_card_number', 50)->nullable();
            $table->string('phone_number', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('address')->nullable();
            $table->date('contract_start_date')->nullable();
            $table->date('contract_end_date')->nullable();
            $table->decimal('base_salary', 15, 2)->nullable();
            $table->decimal('allowance', 15, 2)->nullable();
            $table->decimal('meal_allowance', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_agreements');
    }
};
