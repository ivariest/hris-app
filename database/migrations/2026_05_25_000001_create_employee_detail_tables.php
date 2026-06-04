<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('status_kontrak', 30)->nullable();
            $table->string('job_agreement', 100)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('tanggal_tetap')->nullable();
            $table->string('masa_kerja', 50)->nullable();
            $table->unsignedInteger('kontrak_ke')->nullable();
            $table->string('file_kontrak', 255)->nullable();
            $table->string('status', 20)->default('active');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('employee_personal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('no_ktp', 30)->nullable();
            $table->string('no_kk', 30)->nullable();
            $table->string('jenis_kelamin', 10)->nullable();
            $table->string('agama', 30)->nullable();
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tgl_lahir')->nullable();
            $table->string('status_pernikahan', 30)->nullable();
            $table->string('golongan_darah', 5)->nullable();
            $table->string('pendidikan', 50)->nullable();
            $table->string('jurusan', 100)->nullable();
            $table->text('alamat')->nullable();
            $table->string('kelurahan', 100)->nullable();
            $table->string('kecamatan', 100)->nullable();
            $table->string('kota', 100)->nullable();
            $table->string('kode_pos', 10)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('employee_spouse', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('nik', 30)->nullable();
            $table->string('nama', 100)->nullable();
            $table->string('jenis_kelamin', 20)->nullable();
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tgl_lahir')->nullable();
            $table->string('pendidikan', 50)->nullable();
            $table->string('pekerjaan', 100)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('employee_bpjs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('nomor_ketenagakerjaan', 50)->nullable();
            $table->string('nomor_kesehatan', 50)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('employee_tax', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('no_npwp', 50)->nullable();
            $table->string('ptkp_status', 20)->nullable();
            $table->string('npwp_status', 20)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('employee_bank', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('no_rekening', 50)->nullable();
            $table->string('nama_bank', 100)->nullable();
            $table->string('nama_didalam_rek', 100)->nullable();
            $table->string('cabang_bank', 100)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('employee_payroll', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->decimal('gaji_pokok', 15, 2)->nullable();
            $table->decimal('tunjangan', 15, 2)->nullable();
            $table->decimal('uang_makan', 15, 2)->nullable();
            $table->decimal('uang_transport', 15, 2)->nullable();
            $table->decimal('lembur', 15, 2)->nullable();
            $table->decimal('bpjs_potongan', 15, 2)->nullable();
            $table->decimal('pajak', 15, 2)->nullable();
            $table->decimal('total_gaji', 15, 2)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_payroll');
        Schema::dropIfExists('employee_bank');
        Schema::dropIfExists('employee_tax');
        Schema::dropIfExists('employee_bpjs');
        Schema::dropIfExists('employee_spouse');
        Schema::dropIfExists('employee_personal');
        Schema::dropIfExists('employee_contracts');
    }
};
