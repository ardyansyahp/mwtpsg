<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TSupplyDetail extends Model
{
    use HasFactory;

    protected $table = 't_supply_detail';

    public $timestamps = false;

    protected $fillable = [
        'supply_id',
        'receiving_detail_id',
        'nomor_bahan_baku',
        'lot_number',
        'qty',
    ];

    protected function casts(): array
    {
        return [
            'supply_id' => 'integer',
            'receiving_detail_id' => 'integer',
            'qty' => 'decimal:3',
        ];
    }

    public function supply()
    {
        return $this->belongsTo(TSupply::class, 'supply_id');
    }

    public function receivingDetail()
    {
        return $this->belongsTo(ReceivingDetail::class, 'receiving_detail_id');
    }
}
