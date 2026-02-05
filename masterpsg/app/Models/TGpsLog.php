<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TGpsLog extends Model
{
    use HasFactory;

    protected $table = 't_gps_logs';

    protected $fillable = [
        'delivery_header_id',
        'latitude',
        'longitude',
        'recorded_at',
        'device_info',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'recorded_at' => 'datetime',
    ];

    public function deliveryHeader()
    {
        return $this->belongsTo(TShippingDeliveryHeader::class, 'delivery_header_id');
    }
}
