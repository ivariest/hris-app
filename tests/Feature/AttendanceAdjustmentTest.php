<?php

use App\Models\AnnualLeaveTransaction;
use App\Models\AttendanceAdjustment;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeContract;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('attendance recap cuti khusus creates annual leave detail without reducing balance', function () {
    $company = Company::forceCreate([
        'company_code' => 'CMP001',
        'company_name' => 'PT Example',
    ]);

    $employee = Employee::create([
        'company_id' => $company->id,
        'nama_karyawan' => 'Budi Santoso',
        'status_karyawan' => 'active',
    ]);

    EmployeeContract::create([
        'employee_id' => $employee->id,
        'start_date' => now()->subYears(2)->toDateString(),
        'status' => 'active',
    ]);

    $user = User::create([
        'name' => 'Admin',
        'email' => 'admin@example.test',
        'password' => 'password',
        'role_system' => 'admin',
        'status' => 'active',
    ]);

    $this->actingAs($user);

    $this->post(route('attendance-adjustments.store'), [
        'employee_id' => $employee->id,
        'attendance_date' => '2026-06-03',
        'action' => 'exception',
        'exception_type' => 'CUTI KHUSUS',
        'exception_note' => 'Izin keluarga',
    ])->assertRedirect();

    $adjustment = AttendanceAdjustment::query()->first();
    expect($adjustment)->not->toBeNull();
    expect($adjustment->exception_type)->toBe('CUTI KHUSUS');

    $transaction = AnnualLeaveTransaction::query()->first();
    expect($transaction)->not->toBeNull();
    expect($transaction->leave_type)->toBe('CUTI KHUSUS');
    expect($transaction->balance_effect)->toBe('no_balance');
    expect($transaction->source_type)->toBe('attendance');
    expect($transaction->days)->toBe('1.00');
    expect(AnnualLeaveTransaction::query()->where('balance_effect', 'annual_leave')->count())->toBe(0);
    expect(AnnualLeaveTransaction::query()->where('balance_effect', 'no_balance')->count())->toBe(1);
});
