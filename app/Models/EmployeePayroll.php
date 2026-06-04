<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeePayroll extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'employee_payroll';

    protected $fillable = [
        'employee_id',
        'gaji_pokok',
        'tunjangan',
        'uang_makan',
        'uang_transport',
        'lembur',
        'bpjs_potongan',
        'pajak',
        'total_gaji',
    ];

    protected function casts(): array
    {
        return [
            'gaji_pokok' => 'decimal:2',
            'tunjangan' => 'decimal:2',
            'uang_makan' => 'decimal:2',
            'uang_transport' => 'decimal:2',
            'lembur' => 'decimal:2',
            'bpjs_potongan' => 'decimal:2',
            'pajak' => 'decimal:2',
            'total_gaji' => 'decimal:2',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
