<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceShift extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'shift_name',
        'check_in_time',
        'check_out_time',
        'late_tolerance_minutes',
        'early_leave_tolerance_minutes',
        'is_default',
        'status',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function employeeAssignments(): HasMany
    {
        return $this->hasMany(EmployeeAttendanceShift::class);
    }
}
