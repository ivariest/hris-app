<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeContract;
use App\Models\EmployeePosition;
use App\Models\Location;
use App\Models\Position;
use App\Models\PositionLevel;
use App\Models\SubDepartment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CoreDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $this->deleteOldDummyEmployees();

            $levels = collect([
                'STAFF',
                'COORDINATOR',
                'KEPALA SEKSI',
                'SECT HEAD',
                'SUB DEPT HEAD',
                'DEPT HEAD',
                'DIRECTOR',
            ])->mapWithKeys(fn (string $levelName) => [
                $levelName => PositionLevel::firstOrCreate(['level_name' => $levelName]),
            ]);

            $location = Location::withTrashed()->firstOrCreate(['location_name' => 'Head Office']);

            if ($location->trashed()) {
                $location->restore();
            }

            $employees = [
                ['PT STANDARDPEN INDUSTRIES', 'HR & GA', 'HR & GA', 'HR & GA ADMINISTRATION', 'STAFF', 'EMP-SP-001', 'SP260001', '2001', 'Raka Aditya', 'raka.aditya@standardpen.local', '2021-03-15', 'Jakarta'],
                ['PT STANDARDPEN INDUSTRIES', 'HR & GA', 'GA', 'RECEPTIONIST', 'STAFF', 'EMP-SP-002', 'SP260002', '2002', 'Nadya Paramita', 'nadya.paramita@standardpen.local', '2022-07-01', 'Jakarta'],
                ['PT STANDARDPEN INDUSTRIES', 'IT', 'IT', 'EDP', 'STAFF', 'EMP-SP-003', 'SP260003', '2003', 'Fajar Nugroho', 'fajar.nugroho@standardpen.local', '2023-01-10', 'Jakarta'],
                ['PT STANDARDPEN INDUSTRIES', 'MARKETING', 'MARKETING & PROMOTION', 'MARKETING & PROMOTION OFFICER', 'STAFF', 'EMP-SP-004', 'SP260004', '2004', 'Dinda Larasati', 'dinda.larasati@standardpen.local', '2023-11-20', 'Bandung'],
                ['PT STANDARDPEN INDUSTRIES', 'MARKETING', 'PRODUCT EXECUTIVE', 'PRODUCT EXECUTIVE', 'STAFF', 'EMP-SP-005', 'SP260005', '2005', 'Bagas Prakoso', 'bagas.prakoso@standardpen.local', '2024-05-06', 'Jakarta'],
                ['PT STANDARDPEN INDUSTRIES', 'PURCHASING', 'PURCHASING', 'PURCHASING ADMINISTRATION', 'STAFF', 'EMP-SP-006', 'SP260006', '2006', 'Maya Kartika', 'maya.kartika@standardpen.local', '2024-09-16', 'Tangerang'],
                ['PT STANDARDPEN INDUSTRIES', 'R & D', 'R & D', 'PRODUCT DEVELOPMENT', 'STAFF', 'EMP-SP-007', 'SP260007', '2007', 'Arman Hidayat', 'arman.hidayat@standardpen.local', '2025-02-03', 'Jakarta'],
                ['UD BATAVIA TRINUSA', 'FINANCE', 'FINANCE', 'FINANCE ADMINISTRATION OFFICER', 'STAFF', 'EMP-BT-001', 'BT260001', '3001', 'Salsa Amelia', 'salsa.amelia@batavia.local', '2022-02-14', 'Jakarta'],
                ['UD BATAVIA TRINUSA', 'ACCOUNTING', 'ACCOUNTING', 'ACCOUNT PAYABLE OFFICER', 'STAFF', 'EMP-BT-002', 'BT260002', '3002', 'Yoga Saputra', 'yoga.saputra@batavia.local', '2023-08-01', 'Jakarta'],
                ['UD BATAVIA TRINUSA', 'SALES', 'SALES', 'SALES EXECUTIVE', 'STAFF', 'EMP-BT-003', 'BT260003', '3003', 'Karin Maharani', 'karin.maharani@batavia.local', '2024-01-22', 'Surabaya'],
                ['UD BATAVIA TRINUSA', 'SALES', 'MODERN MARKET', 'KEY ACCOUNT EXECUTIVE', 'COORDINATOR', 'EMP-BT-004', 'BT260004', '3004', 'Reno Wijaya', 'reno.wijaya@batavia.local', '2024-12-02', 'Jakarta'],
                ['UD BATAVIA TRINUSA', 'WAREHOUSE', 'WAREHOUSE', 'ADMINISTRATION WAREHOUSE OFFICER', 'STAFF', 'EMP-BT-005', 'BT260005', '3005', 'Tania Oktaviani', 'tania.oktaviani@batavia.local', '2025-06-09', 'Bekasi'],
                ['UD BATAVIA TRINUSA', 'WAREHOUSE', 'DRIVER', 'DRIVER & DELIVERY', 'STAFF', 'EMP-BT-006', 'BT260006', '3006', 'Dimas Firmansyah', 'dimas.firmansyah@batavia.local', '2025-12-01', 'Bekasi'],
                ['PT STANDARDPEN INDUSTRIES', 'EXIM', 'IMPORT', 'IMPORT OFFICER', 'STAFF', 'EMP-SP-008', 'SP260008', '2008', 'Intan Permata', 'intan.permata@standardpen.local', '2026-01-12', 'Jakarta'],
                ['UD BATAVIA TRINUSA', 'AUDIT', 'AUDIT', 'AUDIT OFFICER', 'STAFF', 'EMP-BT-007', 'BT260007', '3007', 'Bayu Ramadhan', 'bayu.ramadhan@batavia.local', '2026-04-06', 'Jakarta'],
            ];

            foreach ($employees as $data) {
                [$companyName, $departmentName, $subDepartmentName, $positionName, $levelName, $employeeCode, $nik, $attendanceId, $name, $email, $joinDate, $area] = $data;

                $company = Company::firstOrCreate(
                    ['company_name' => $companyName],
                    ['company_address' => 'Jakarta, Indonesia']
                );
                $department = Department::firstOrCreate([
                    'company_id' => $company->id,
                    'department_name' => $departmentName,
                ]);
                $subDepartment = SubDepartment::firstOrCreate([
                    'department_id' => $department->id,
                    'sub_department_name' => $subDepartmentName,
                ]);
                $position = Position::firstOrCreate([
                    'sub_department_id' => $subDepartment->id,
                    'position_name' => $positionName,
                ]);

                $employee = Employee::withTrashed()->updateOrCreate(
                    ['nik_karyawan' => $nik],
                    [
                        'employee_id' => $employeeCode,
                        'attendance_id' => $attendanceId,
                        'company_id' => $company->id,
                        'nama_karyawan' => $name,
                        'email_kantor' => $email,
                        'email_pribadi' => null,
                        'no_hp' => '0812'.substr($attendanceId, -4).'26',
                        'no_hp_darurat' => '0813'.substr($attendanceId, -4).'26',
                        'nama_kontak_darurat' => 'Kontak '.$name,
                        'hubungan_darurat' => 'Keluarga',
                        'status_karyawan' => 'active',
                        'deleted_at' => null,
                    ]
                );

                EmployeePosition::updateOrCreate(
                    ['employee_id' => $employee->id],
                    [
                        'position_id' => $position->id,
                        'level_id' => $levels[$levelName]->id,
                        'area' => $area,
                        'rayon' => null,
                        'location_id' => $location->id,
                        'atasan_langsung' => null,
                    ]
                );

                EmployeeContract::updateOrCreate(
                    ['employee_id' => $employee->id],
                    [
                        'status_kontrak' => 'PKWT',
                        'job_agreement' => $employeeCode.'-AGREEMENT',
                        'start_date' => $joinDate,
                        'end_date' => null,
                        'tanggal_tetap' => null,
                        'masa_kerja' => null,
                        'kontrak_ke' => 1,
                        'file_kontrak' => null,
                        'status' => 'active',
                    ]
                );
            }

            User::firstOrCreate(
                ['email' => 'admin@hris.local'],
                [
                    'name' => 'System Admin',
                    'username' => 'admin',
                    'password' => Hash::make('password'),
                    'role_system' => 'superadmin',
                ]
            );

            User::firstOrCreate(
                ['email' => 'hr@hris.local'],
                [
                    'name' => 'HR Admin',
                    'username' => 'hradmin',
                    'password' => Hash::make('password'),
                    'role_system' => 'admin',
                ]
            );
        });
    }

    private function deleteOldDummyEmployees(): void
    {
        Employee::withTrashed()
            ->where('id', '!=', 1)
            ->where(function ($query) {
                $query->where('nik_karyawan', 'like', 'DMY260%')
                    ->orWhere('nik_karyawan', 'like', 'SP26%')
                    ->orWhere('nik_karyawan', 'like', 'BT26%');
            })
            ->get()
            ->each
            ->forceDelete();
    }
}
