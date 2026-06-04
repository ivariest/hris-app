<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recruitment_candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recruitment_request_id')->constrained('recruitment_requests')->cascadeOnDelete();
            $table->string('candidate_name', 150);
            $table->text('candidate_address')->nullable();
            $table->string('candidate_phone', 30)->nullable();
            $table->string('candidate_email', 100)->nullable();
            $table->text('psychotest_result')->nullable();
            $table->string('id_card_number', 50)->nullable();
            $table->text('comment')->nullable();
            $table->string('category', 20)->default('shortlist');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recruitment_candidates');
    }
};
