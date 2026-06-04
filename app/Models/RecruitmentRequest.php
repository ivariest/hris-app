<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecruitmentRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'request_date' => 'date',
        'needed_date' => 'date',
    ];

    protected $fillable = [
        'request_number',
        'requester_employee_id',
        'requester_department',
        'requester_position',
        'requester_level',
        'company_id',
        'request_date',
        'position_id',
        'requested_position',
        'needed_count',
        'gender',
        'age',
        'request_status',
        'request_reason',
        'replacement_reason',
        'replacement_note',
        'employee_status_agreement',
        'employment_duration_months',
        'needed_date',
        'posting_media',
        'minimum_education',
        'major',
        'special_requirements',
        'general_requirements',
        'job_summary',
        'requirement_detail',
        'status',
        'pending_reason',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'requester_employee_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(RecruitmentCandidate::class);
    }
}
