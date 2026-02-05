<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TShippingDeliveryHeader extends Model
{
    use HasFactory;

    protected $table = 't_shipping_delivery_header';

    protected $fillable = [
        'periode',
        'kendaraan_id',
        'driver_id',
        'destination',
        'no_surat_jalan',
        'tanggal_berangkat',
        'waktu_berangkat',
        'waktu_tiba',
        'status',
        'total_trip',
        'total_delivered',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'kendaraan_id' => 'integer',
            'driver_id' => 'integer',
            'total_trip' => 'integer',
            'total_delivered' => 'integer',
            'tanggal_berangkat' => 'date',
            'waktu_berangkat' => 'datetime',
            'waktu_tiba' => 'datetime',
        ];
    }

    public function kendaraan()
    {
        return $this->belongsTo(MKendaraan::class, 'kendaraan_id');
    }

    public function driver()
    {
        return $this->belongsTo(MManpower::class, 'driver_id');
    }

    public function details()
    {
        return $this->hasMany(TShippingDeliveryDetail::class, 'delivery_header_id');
    }

    public function incidents()
    {
        return $this->hasMany(TShippingIncident::class, 'delivery_header_id');
    }
}
