<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeMutation extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'effective_date' => 'date',
    ];

    protected $fillable = [
        'employee_id',
        'mutation_number',
        'old_position_id',
        'new_position_id',
        'effective_date',
        'mutation_letter_path',
        'notes',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function oldPosition(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'old_position_id');
    }

    public function newPosition(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'new_position_id');
    }
}
