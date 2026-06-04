<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeBpjs extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'employee_bpjs';

    protected $fillable = [
        'employee_id',
        'nomor_ketenagakerjaan',
        'nomor_kesehatan',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
