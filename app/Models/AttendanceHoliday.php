<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceHoliday extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'holiday_date',
        'holiday_name',
        'holiday_type',
    ];

    protected $casts = [
        'holiday_date' => 'date',
    ];
}
