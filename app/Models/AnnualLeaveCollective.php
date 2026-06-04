<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnnualLeaveCollective extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'date_from',
        'date_to',
        'name',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(AnnualLeaveTransaction::class);
    }
}
