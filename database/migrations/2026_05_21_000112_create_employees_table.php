<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id', 30)->nullable();
            $table->string('nik_karyawan', 50)->nullable();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->string('nama_karyawan', 150);
            $table->string('email_kantor', 100)->nullable();
            $table->string('email_pribadi', 100)->nullable();
            $table->string('no_hp', 30)->nullable();
            $table->string('no_hp_darurat', 30)->nullable();
            $table->string('nama_kontak_darurat', 100)->nullable();
            $table->string('hubungan_darurat', 50)->nullable();
            $table->string('foto_karyawan', 255)->nullable();
            $table->string('status_karyawan', 30)->default('active');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
