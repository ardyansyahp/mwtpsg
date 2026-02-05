<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TInjectIn extends Model
{
    use HasFactory;

    protected $table = 't_inject_in';

    protected $fillable = [
        'supply_detail_id',
        'lot_number',
        'planning_run_id',
        'mesin_id',
        'manpower',
        'waktu_scan',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'supply_detail_id' => 'integer',
            'planning_run_id' => 'integer',
            'mesin_id' => 'integer',
            'waktu_scan' => 'datetime',
        ];
    }

    public function supplyDetail()
    {
        return $this->belongsTo(TSupplyDetail::class, 'supply_detail_id');
    }

    public function planningRun()
    {
        return $this->belongsTo(TPlanningRun::class, 'planning_run_id');
    }

    public function mesin()
    {
        return $this->belongsTo(MMesin::class, 'mesin_id');
    }

    public function injectOuts()
    {
        return $this->hasMany(TInjectOut::class, 'inject_in_id');
    }
}

