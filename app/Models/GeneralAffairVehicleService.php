<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GeneralAffairVehicleService extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'general_affair_vehicle_services';

    protected $fillable = [
        'vehicle_id',
        'service_date',
        'service_type',
        'workshop_name',
        'odometer',
        'cost',
        'description',
        'next_service_date',
        'next_service_odometer',
        'document_path',
    ];

    protected $casts = [
        'service_date' => 'date',
        'next_service_date' => 'date',
        'odometer' => 'integer',
        'next_service_odometer' => 'integer',
        'cost' => 'decimal:2',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(GeneralAffairVehicle::class, 'vehicle_id');
    }
}
