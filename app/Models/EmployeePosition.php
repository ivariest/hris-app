<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeePosition extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'position_id',
        'level_id',
        'area',
        'rayon',
        'location_id',
        'atasan_langsung',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(PositionLevel::class, 'level_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'atasan_langsung');
    }
}
