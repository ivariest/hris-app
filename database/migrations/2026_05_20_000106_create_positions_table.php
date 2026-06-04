<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_department_id')->constrained('sub_departments')->cascadeOnDelete();
            $table->foreignId('level_id')->constrained('position_levels')->cascadeOnDelete();
            $table->string('position_code', 20)->unique();
            $table->string('position_name', 100);
            $table->string('status', 20)->default('active');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
