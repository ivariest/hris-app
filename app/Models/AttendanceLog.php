<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_import_id',
        'employee_id',
        'attendance_id',
        'fingerprint_name',
        'scan_date',
        'check_in',
        'check_out',
        'status',
        'late_minutes',
        'early_leave_minutes',
        'notes',
    ];

    protected $casts = [
        'scan_date' => 'date',
    ];

    public function import(): BelongsTo
    {
        return $this->belongsTo(AttendanceImport::class, 'attendance_import_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
