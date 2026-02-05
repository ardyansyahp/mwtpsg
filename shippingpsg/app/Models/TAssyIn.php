<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TAssyIn extends Model
{
    use HasFactory;

    protected $table = 't_assy_in';

    protected $fillable = [
        'supply_detail_id',
        'wip_out_id',
        'lot_number',
        'part_id',
        'manpower',
        'waktu_scan',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'supply_detail_id' => 'integer',
            'wip_out_id' => 'integer',
            'part_id' => 'integer',
            'waktu_scan' => 'datetime',
        ];
    }

    public function supplyDetail()
    {
        return $this->belongsTo(TSupplyDetail::class, 'supply_detail_id');
    }

    public function wipOut()
    {
        return $this->belongsTo(TWipOut::class, 'wip_out_id');
    }

    public function part()
    {
        return $this->belongsTo(SMPart::class, 'part_id');
    }

    public function assyOuts()
    {
        return $this->hasMany(TAssyOut::class, 'assy_in_id');
    }
}
