<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TFinishGoodIn extends Model
{
    use HasFactory;

    protected $table = 't_finishgood_in';

    protected $fillable = [
        'assy_out_id',
        'lot_number',
        'no_planning',
        'mesin_id', // Changed from no_mesin
        'tanggal_produksi',
        'shift',
        'part_id',
        'qty',
        'customer',
        'manpower_id', // Changed from manpower
        'waktu_scan',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'assy_out_id' => 'integer',
            'part_id' => 'integer',
            'mesin_id' => 'integer',
            'manpower_id' => 'integer',
            'waktu_scan' => 'datetime',
        ];
    }

    public function assyOut()
    {
        return $this->belongsTo(TAssyOut::class, 'assy_out_id');
    }

    public function part()
    {
        return $this->belongsTo(SMPart::class, 'part_id');
    }

    public function mesin()
    {
        return $this->belongsTo(MMesin::class, 'mesin_id');
    }

    public function manpower()
    {
        return $this->belongsTo(MManpower::class, 'manpower_id');
    }


    public function finishGoodOuts()
    {
        return $this->hasMany(TFinishGoodOut::class, 'finish_good_in_id');
    }
}

