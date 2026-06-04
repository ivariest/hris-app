<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeePromotion extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected $fillable = [
        'employee_id',
        'promotion_number',
        'promotion_type',
        'old_position_id',
        'old_level_id',
        'new_position_id',
        'new_level_id',
        'start_date',
        'end_date',
        'promotion_form_path',
        'appointment_letter_path',
        'notes',
        'record_status',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function oldPosition(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'old_position_id');
    }

    public function oldLevel(): BelongsTo
    {
        return $this->belongsTo(PositionLevel::class, 'old_level_id');
    }

    public function newPosition(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'new_position_id');
    }

    public function newLevel(): BelongsTo
    {
        return $this->belongsTo(PositionLevel::class, 'new_level_id');
    }
}
