<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeSpouse extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'employee_spouse';

    protected $fillable = [
        'employee_id',
        'nik',
        'nama',
        'jenis_kelamin',
        'tempat_lahir',
        'tgl_lahir',
        'pendidikan',
        'pekerjaan',
    ];

    protected function casts(): array
    {
        return [
            'tgl_lahir' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
