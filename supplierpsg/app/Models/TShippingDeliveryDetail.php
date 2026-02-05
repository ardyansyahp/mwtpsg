<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TShippingDeliveryDetail extends Model
{
    use HasFactory;

    protected $table = 't_shipping_delivery_detail';

    protected $fillable = [
        'delivery_header_id',
        'tanggal',
        'jam',
        'status',
        'lokasi_saat_ini',
        'latitude',
        'longitude',
        'waktu_update',
        'keterangan',
        'foto_bukti',
    ];

    protected function casts(): array
    {
        return [
            'delivery_header_id' => 'integer',
            'jam' => 'integer',
            'tanggal' => 'date',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'waktu_update' => 'datetime',
        ];
    }

    public function deliveryHeader()
    {
        return $this->belongsTo(TShippingDeliveryHeader::class, 'delivery_header_id');
    }
}
