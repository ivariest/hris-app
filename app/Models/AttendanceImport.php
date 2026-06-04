<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceImport extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_name',
        'file_path',
        'period_start',
        'period_end',
        'imported_by',
        'total_rows',
        'matched_rows',
        'unmatched_rows',
        'imported_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'imported_at' => 'datetime',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function importer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }
}
