<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeAgreement extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'date_of_birth' => 'date',
        'contract_start_date' => 'date',
        'contract_end_date' => 'date',
        'base_salary' => 'decimal:2',
        'allowance' => 'decimal:2',
        'meal_allowance' => 'decimal:2',
    ];

    protected $fillable = [
        'agreement_number',
        'recruitment_request_id',
        'agreement_status',
        'employee_name',
        'department',
        'place_of_birth',
        'date_of_birth',
        'gender',
        'religion',
        'id_card_number',
        'phone_number',
        'email',
        'address',
        'contract_start_date',
        'contract_end_date',
        'base_salary',
        'allowance',
        'meal_allowance',
        'notes',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(RecruitmentRequest::class, 'recruitment_request_id');
    }
}
