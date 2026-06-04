<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'deactivated_at' => 'datetime',
    ];

    protected $fillable = [
        'employee_id',
        'nik_karyawan',
        'attendance_id',
        'company_id',
        'nama_karyawan',
        'email_kantor',
        'email_pribadi',
        'no_hp',
        'no_hp_darurat',
        'nama_kontak_darurat',
        'hubungan_darurat',
        'foto_karyawan',
        'status_karyawan',
        'deactivation_reason',
        'deactivated_at',
        'deactivation_document_path',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function employeePosition(): HasOne
    {
        return $this->hasOne(EmployeePosition::class)->latestOfMany();
    }

    public function contract(): HasOne
    {
        return $this->hasOne(EmployeeContract::class);
    }

    public function personal(): HasOne
    {
        return $this->hasOne(EmployeePersonal::class);
    }

    public function spouse(): HasOne
    {
        return $this->hasOne(EmployeeSpouse::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(EmployeeChild::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function bpjs(): HasOne
    {
        return $this->hasOne(EmployeeBpjs::class);
    }

    public function tax(): HasOne
    {
        return $this->hasOne(EmployeeTax::class);
    }

    public function bank(): HasOne
    {
        return $this->hasOne(EmployeeBank::class);
    }

    public function payroll(): HasOne
    {
        return $this->hasOne(EmployeePayroll::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'employee_id');
    }

    public function promotions(): HasMany
    {
        return $this->hasMany(EmployeePromotion::class);
    }

    public function mutations(): HasMany
    {
        return $this->hasMany(EmployeeMutation::class);
    }

    public function demotions(): HasMany
    {
        return $this->hasMany(EmployeeDemotion::class);
    }
}
