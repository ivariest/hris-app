<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class TurnOverReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->date('start_date')?->startOfDay() ?? now()->startOfMonth();
        $endDate = $request->date('end_date')?->endOfDay() ?? now()->endOfMonth();

        if ($startDate->gt($endDate)) {
            [$startDate, $endDate] = [$endDate->copy()->startOfDay(), $startDate->copy()->endOfDay()];
        }

        $relations = ['company', 'employeePosition.position.subDepartment.department.company', 'employeePosition.level'];

        $registeredEmployees = Employee::query()
            ->with($relations)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at')
            ->get();

        $inactiveEmployees = Employee::query()
            ->with($relations)
            ->where('status_karyawan', 'inactive')
            ->whereBetween('deactivated_at', [$startDate, $endDate])
            ->orderBy('deactivated_at')
            ->get();

        return view('pages.employee-management.turn-over-report.index', [
            'title' => 'Turn Over Report',
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
            'registeredEmployees' => $registeredEmployees,
            'inactiveEmployees' => $inactiveEmployees,
        ]);
    }
}
