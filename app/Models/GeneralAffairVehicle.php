<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GeneralAffairVehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'general_affair_vehicles';

    protected $fillable = [
        'plate_number',
        'vehicle_type',
        'brand',
        'manufacture_year',
        'location_id',
        'driver_id',
        'ownership_status',
        'tax_valid_until',
        'plate_valid_until',
        'stnk_document_path',
        'kir_valid_until',
        'kir_document_path',
        'notes',
    ];

    protected $casts = [
        'manufacture_year' => 'integer',
        'tax_valid_until' => 'date',
        'plate_valid_until' => 'date',
        'kir_valid_until' => 'date',
    ];

    public function services(): HasMany
    {
        return $this->hasMany(GeneralAffairVehicleService::class, 'vehicle_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'driver_id');
    }
}
