<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeBank extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'employee_bank';

    protected $fillable = [
        'employee_id',
        'no_rekening',
        'nama_bank',
        'nama_didalam_rek',
        'cabang_bank',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
