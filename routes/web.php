<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\AttendanceAdjustmentController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\AttendanceHolidayController;
use App\Http\Controllers\AttendanceImportController;
use App\Http\Controllers\AttendanceRecapController;
use App\Http\Controllers\AttendanceReportController;
use App\Http\Controllers\AttendanceShiftController;
use App\Http\Controllers\AllowanceReportController;
use App\Http\Controllers\AnnualLeaveController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Organization\HierarchyLookupController;
use App\Http\Controllers\Organization\CompanyController;
use App\Http\Controllers\Organization\DepartmentController;
use App\Http\Controllers\Organization\LocationController;
use App\Http\Controllers\Organization\PositionController;
use App\Http\Controllers\Organization\PositionLevelController;
use App\Http\Controllers\Organization\SubDepartmentController;
use App\Http\Controllers\TurnOverReportController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeAgreementController;
use App\Http\Controllers\EmployeeDeactivationController;
use App\Http\Controllers\EmployeeDemotionController;
use App\Http\Controllers\EmployeeMutationController;
use App\Http\Controllers\EmployeePromotionController;
use App\Http\Controllers\GeneralAffair\VehicleController;
use App\Http\Controllers\GeneralAffair\VehicleServiceController;
use App\Http\Controllers\RecruitmentCandidateController;
use App\Http\Controllers\RecruitmentReportController;
use App\Http\Controllers\RecruitmentRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('signin');
});

Route::middleware('guest')->group(function () {
    Route::get('/signin', [AuthenticatedSessionController::class, 'create'])->name('signin');
    Route::post('/signin', [AuthenticatedSessionController::class, 'store'])->name('signin.store');

    Route::get('/signup', [RegisteredUserController::class, 'create'])->name('signup');
    Route::post('/signup', [RegisteredUserController::class, 'store'])->name('signup.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'internal'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/lookups/companies/{company}/departments', [HierarchyLookupController::class, 'departmentsByCompany'])->name('lookups.departments_by_company');
    Route::get('/lookups/departments/{department}/sub-departments', [HierarchyLookupController::class, 'subDepartmentsByDepartment'])->name('lookups.sub_departments_by_department');

    Route::resource('companies', CompanyController::class);
    Route::resource('departments', DepartmentController::class);
    Route::resource('sub-departments', SubDepartmentController::class)->parameters([
        'sub-departments' => 'subDepartment',
    ]);
    Route::resource('position-levels', PositionLevelController::class)->parameters([
        'position-levels' => 'positionLevel',
    ]);
    Route::resource('positions', PositionController::class);
    Route::resource('locations', LocationController::class);
    Route::resource('employees', EmployeeController::class);
    Route::get('/employee-agreements/{employee_agreement}/print', [EmployeeAgreementController::class, 'print'])
        ->name('employee-agreements.print');
    Route::resource('employee-agreements', EmployeeAgreementController::class);
    Route::resource('employee-promotions', EmployeePromotionController::class)->only([
        'index',
        'create',
        'store',
        'edit',
        'update',
        'show',
        'destroy',
    ]);
    Route::get('/employee-promotions/{employee_promotion}/update-action', [EmployeePromotionController::class, 'manage'])
        ->name('employee-promotions.manage');
    Route::redirect('/employee-management/promotion', '/employee-promotions')->name('employee_management.promotion');
    Route::resource('employee-mutations', EmployeeMutationController::class)->only([
        'index',
        'create',
        'store',
        'show',
    ]);
    Route::redirect('/employee-management/mutation', '/employee-mutations')->name('employee_management.mutation');
    Route::resource('employee-demotions', EmployeeDemotionController::class)->only([
        'index',
        'create',
        'store',
        'show',
    ]);
    Route::redirect('/employee-management/demotion', '/employee-demotions')->name('employee_management.demotion');
    Route::get('/employee-deactivations', [EmployeeDeactivationController::class, 'index'])
        ->name('employee-deactivations.index');
    Route::get('/employee-deactivations/{employee}/edit', [EmployeeDeactivationController::class, 'edit'])
        ->name('employee-deactivations.edit');
    Route::put('/employee-deactivations/{employee}', [EmployeeDeactivationController::class, 'update'])
        ->name('employee-deactivations.update');
    Route::redirect('/employee-management/deactivate-employees', '/employee-deactivations')->name('employee_management.deactivate_employees');
    Route::get('/turn-over-report', [TurnOverReportController::class, 'index'])
        ->name('turn-over-report.index');
    Route::redirect('/employee-management/turn-over-report', '/turn-over-report')->name('employee_management.turn_over_report');
    Route::resource('attendance-shifts', AttendanceShiftController::class)->except(['show']);
    Route::resource('attendance-holidays', AttendanceHolidayController::class)->except(['show']);
    Route::post('/attendance-imports/preview', [AttendanceImportController::class, 'preview'])->name('attendance-imports.preview');
    Route::resource('attendance-imports', AttendanceImportController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('/attendance-adjustments', [AttendanceAdjustmentController::class, 'store'])->name('attendance-adjustments.store');
    Route::get('/attendance-recap/print', [AttendanceRecapController::class, 'print'])->name('attendance-recap.print');
    Route::get('/attendance-recap', [AttendanceRecapController::class, 'index'])->name('attendance-recap.index');
    Route::get('/attendance-report', [AttendanceReportController::class, 'index'])->name('attendance-report.index');
    Route::get('/annual-leaves', [AnnualLeaveController::class, 'index'])->name('annual-leaves.index');
    Route::post('/annual-leaves', [AnnualLeaveController::class, 'storeLeave'])->name('annual-leaves.store');
    Route::get('/annual-leaves/collectives', [AnnualLeaveController::class, 'collectives'])->name('annual-leaves.collectives.index');
    Route::get('/annual-leaves/collectives/create', [AnnualLeaveController::class, 'createCollective'])->name('annual-leaves.collectives.create');
    Route::post('/annual-leaves/collectives', [AnnualLeaveController::class, 'storeCollective'])->name('annual-leaves.collectives.store');
    Route::get('/annual-leaves/collectives/{collective}/edit', [AnnualLeaveController::class, 'editCollective'])->name('annual-leaves.collectives.edit');
    Route::put('/annual-leaves/collectives/{collective}', [AnnualLeaveController::class, 'updateCollective'])->name('annual-leaves.collectives.update');
    Route::get('/annual-leaves/transactions/{transaction}/edit', [AnnualLeaveController::class, 'editTransaction'])->name('annual-leaves.transactions.edit');
    Route::put('/annual-leaves/transactions/{transaction}', [AnnualLeaveController::class, 'updateTransaction'])->name('annual-leaves.transactions.update');
    Route::get('/annual-leaves/{employee}', [AnnualLeaveController::class, 'show'])->name('annual-leaves.show');
    Route::post('/annual-leaves/{employee}', [AnnualLeaveController::class, 'storeEmployeeLeave'])->name('annual-leaves.employee.store');
    Route::get('/allowance-report/print', [AllowanceReportController::class, 'print'])->name('allowance-report.print');
    Route::get('/allowance-report', [AllowanceReportController::class, 'index'])->name('allowance-report.index');
    Route::resource('recruitment-requests', RecruitmentRequestController::class);
    Route::resource('recruitment-candidates', RecruitmentCandidateController::class);
    Route::post('/recruitment-candidates/{recruitment_candidate}/move-category', [RecruitmentCandidateController::class, 'moveCategory'])
        ->name('recruitment-candidates.move-category');
    Route::get('/recruitment-report', [RecruitmentReportController::class, 'index'])->name('recruitment-report.index');
    Route::patch('/general-affair/vehicles/{vehicle}/stnk', [VehicleController::class, 'updateStnk'])
        ->name('general-affair.vehicles.update-stnk');
    Route::patch('/general-affair/vehicles/{vehicle}/kir', [VehicleController::class, 'updateKir'])
        ->name('general-affair.vehicles.update-kir');
    Route::resource('/general-affair/vehicles', VehicleController::class)
        ->names('general-affair.vehicles');
    Route::resource('/general-affair/vehicle-services', VehicleServiceController::class)
        ->except(['show'])
        ->names('general-affair.vehicle-services');
    Route::view('/general-affair/kendaraan-rental', 'pages.placeholders.module', [
        'title' => 'Kendaraan Rental',
        'description' => 'Prepare rental vehicle tracking workflows.',
    ])->name('general-affair.vehicle-rentals.index');
    Route::view('/users', 'pages.blank', ['title' => 'Users'])->name('users');
    Route::view('/audit-logs', 'pages.blank', ['title' => 'Audit Logs'])->name('audit_logs');

    Route::get('/calendar', function () {
        return view('pages.calender', ['title' => 'Calendar']);
    })->name('calendar');

    Route::get('/profile', function () {
        return view('pages.profile', ['title' => 'Profile']);
    })->name('profile');

    Route::get('/form-elements', function () {
        return view('pages.form.form-elements', ['title' => 'Form Elements']);
    })->name('form-elements');

    Route::get('/basic-tables', function () {
        return view('pages.tables.basic-tables', ['title' => 'Basic Tables']);
    })->name('basic-tables');

    Route::get('/blank', function () {
        return view('pages.blank', ['title' => 'Blank']);
    })->name('blank');

    Route::get('/error-404', function () {
        return view('pages.errors.error-404', ['title' => 'Error 404']);
    })->name('error-404');

    Route::get('/line-chart', function () {
        return view('pages.chart.line-chart', ['title' => 'Line Chart']);
    })->name('line-chart');

    Route::get('/bar-chart', function () {
        return view('pages.chart.bar-chart', ['title' => 'Bar Chart']);
    })->name('bar-chart');

    Route::get('/alerts', function () {
        return view('pages.ui-elements.alerts', ['title' => 'Alerts']);
    })->name('alerts');

    Route::get('/avatars', function () {
        return view('pages.ui-elements.avatars', ['title' => 'Avatars']);
    })->name('avatars');

    Route::get('/badge', function () {
        return view('pages.ui-elements.badges', ['title' => 'Badges']);
    })->name('badges');

    Route::get('/buttons', function () {
        return view('pages.ui-elements.buttons', ['title' => 'Buttons']);
    })->name('buttons');

    Route::get('/image', function () {
        return view('pages.ui-elements.images', ['title' => 'Images']);
    })->name('images');

    Route::get('/videos', function () {
        return view('pages.ui-elements.videos', ['title' => 'Videos']);
    })->name('videos');
});
