<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TShippingLoading extends Model
{
    use HasFactory;

    protected $table = 't_shipping_loading';

    protected $fillable = [
        'finish_good_out_id',
        'waktu_loading',
        'status',
        'kendaraan_id',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'finish_good_out_id' => 'integer',
            'kendaraan_id' => 'integer',
            'waktu_loading' => 'datetime',
        ];
    }

    public function finishGoodOut()
    {
        return $this->belongsTo(TFinishGoodOut::class, 'finish_good_out_id');
    }

    public function kendaraan()
    {
        return $this->belongsTo(MKendaraan::class, 'kendaraan_id');
    }
}
