<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecruitmentCandidate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'recruitment_request_id',
        'candidate_name',
        'candidate_address',
        'candidate_phone',
        'candidate_email',
        'psychotest_result',
        'id_card_number',
        'comment',
        'category',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(RecruitmentRequest::class, 'recruitment_request_id');
    }
}
