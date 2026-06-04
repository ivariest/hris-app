<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeTax extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'employee_tax';

    protected $fillable = [
        'employee_id',
        'no_npwp',
        'ptkp_status',
        'npwp_status',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
