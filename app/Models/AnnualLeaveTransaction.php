<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnnualLeaveTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'year',
        'date_from',
        'date_to',
        'days',
        'leave_type',
        'source_type',
        'balance_effect',
        'written_off_at',
        'attendance_adjustment_id',
        'annual_leave_collective_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
        'days' => 'decimal:2',
        'written_off_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function adjustment(): BelongsTo
    {
        return $this->belongsTo(AttendanceAdjustment::class, 'attendance_adjustment_id');
    }

    public function collective(): BelongsTo
    {
        return $this->belongsTo(AnnualLeaveCollective::class, 'annual_leave_collective_id');
    }
}
