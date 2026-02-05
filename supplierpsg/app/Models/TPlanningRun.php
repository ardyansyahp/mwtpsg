<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TPlanningRun extends Model
{
    use HasFactory;

    protected $table = 't_planning_run';

    protected $fillable = [
        'planning_day_id',
        'urutan_run',
        'mold_id',
        'part_id', // untuk ASSY
        'lot_produksi',
        'box_id',
        'qty_box',
        'polybag_id',
        'qty_polybag',
        'start_at',
        'end_at',
        'qty_target_total',
        'qty_actual_total',
        'downtime_menit',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'urutan_run' => 'integer',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'qty_target_total' => 'integer',
            'qty_actual_total' => 'integer',
            'downtime_menit' => 'integer',
            'qty_box' => 'decimal:3',
            'qty_polybag' => 'decimal:3',
        ];
    }

    public function day()
    {
        return $this->belongsTo(\App\Models\TPlanningDay::class, 'planning_day_id');
    }

    public function mold()
    {
        return $this->belongsTo(\App\Models\MMold::class, 'mold_id');
    }

    public function part()
    {
        return $this->belongsTo(\App\Models\SMPart::class, 'part_id');
    }

    public function hourlyTargets()
    {
        return $this->hasMany(\App\Models\TPlanningRunHourlyTarget::class, 'planning_run_id');
    }

    public function hourlyActuals()
    {
        return $this->hasMany(\App\Models\TPlanningRunHourlyActual::class, 'planning_run_id');
    }

    public function kebutuhan()
    {
        return $this->hasOne(\App\Models\TPlanningRunKebutuhan::class, 'planning_run_id');
    }

    public function materials()
    {
        return $this->hasMany(\App\Models\TPlanningRunMaterial::class, 'planning_run_id');
    }

    public function subparts()
    {
        return $this->hasMany(\App\Models\TPlanningRunSubpart::class, 'planning_run_id');
    }

    public function box()
    {
        return $this->belongsTo(\App\Models\MBahanBaku::class, 'box_id');
    }

    public function polybag()
    {
        return $this->belongsTo(\App\Models\MBahanBaku::class, 'polybag_id');
    }
}
