<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TShippingIncident extends Model
{
    use HasFactory;

    protected $table = 't_shipping_incidents';

    protected $fillable = [
        'delivery_header_id',
        'keterangan',
        'foto',
        'latitude',
        'longitude',
        'status',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function deliveryHeader()
    {
        return $this->belongsTo(TShippingDeliveryHeader::class, 'delivery_header_id');
    }
}
