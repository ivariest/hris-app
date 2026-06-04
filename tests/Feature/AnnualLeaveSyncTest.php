<?php

use App\Models\AnnualLeaveTransaction;
use App\Models\AttendanceAdjustment;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeContract;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('manual annual leave creates attendance recap leave adjustment', function () {
    $company = Company::forceCreate([
        'company_code' => 'CMP002',
        'company_name' => 'PT Manual Leave',
    ]);

    $employee = Employee::create([
        'company_id' => $company->id,
        'nama_karyawan' => 'Siti Aminah',
        'status_karyawan' => 'active',
    ]);

    EmployeeContract::create([
        'employee_id' => $employee->id,
        'start_date' => now()->subYears(2)->toDateString(),
        'status' => 'active',
    ]);

    $user = User::create([
        'name' => 'Admin',
        'email' => 'admin2@example.test',
        'password' => 'password',
        'role_system' => 'admin',
        'status' => 'active',
    ]);

    $this->actingAs($user);

    $this->post(route('annual-leaves.employee.store', [$employee, 'year' => 2026]), [
        'year' => 2026,
        'date_from' => '2026-06-03',
        'date_to' => '2026-06-03',
        'leave_type' => 'CUTI PRIBADI',
        'notes' => 'Keperluan keluarga',
    ])->assertRedirect();

    $transaction = AnnualLeaveTransaction::query()->first();
    expect($transaction)->not->toBeNull();
    expect($transaction->leave_type)->toBe('CUTI PRIBADI');
    expect($transaction->balance_effect)->toBe('annual_leave');
    expect($transaction->source_type)->toBe('manual');

    $adjustment = AttendanceAdjustment::query()->first();
    expect($adjustment)->not->toBeNull();
    expect($adjustment->exception_type)->toBe('CUTI PRIBADI');
    expect($adjustment->attendance_date->toDateString())->toBe('2026-06-03');
    expect($adjustment->exception_note)->toBe('Keperluan keluarga');
});
