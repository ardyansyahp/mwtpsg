<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TSpkDetail extends Model
{
    use HasFactory;

    protected $table = 't_spk_detail';

    protected $fillable = [
        'spk_id',
        'part_id',
        'qty_packing_box',
        'jadwal_delivery_pcs',
        'original_jadwal_delivery_pcs',
        'jumlah_pulling_box',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'qty_packing_box' => 'integer',
            'jadwal_delivery_pcs' => 'integer',
            'original_jadwal_delivery_pcs' => 'integer',
            'jumlah_pulling_box' => 'integer',
        ];
    }

    public function spk()
    {
        return $this->belongsTo(TSpk::class, 'spk_id');
    }

    public function part()
    {
        return $this->belongsTo(SMPart::class, 'part_id');
    }
}
