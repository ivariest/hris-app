<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeePersonal extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'employee_personal';

    protected $fillable = [
        'employee_id',
        'no_ktp',
        'no_kk',
        'jenis_kelamin',
        'agama',
        'tempat_lahir',
        'tgl_lahir',
        'status_pernikahan',
        'golongan_darah',
        'pendidikan',
        'jurusan',
        'alamat',
        'kelurahan',
        'kecamatan',
        'kota',
        'kode_pos',
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
