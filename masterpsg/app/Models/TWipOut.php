<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TWipOut extends Model
{
    use HasFactory;

    protected $table = 't_wip_out';

    protected $fillable = [
        'wip_in_id',
        'inject_out_id',
        'lot_number',
        'box_number',
        'planning_run_id',
        'waktu_scan_out',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'wip_in_id' => 'integer',
            'inject_out_id' => 'integer',
            'planning_run_id' => 'integer',
            'box_number' => 'integer',
            'waktu_scan_out' => 'datetime',
        ];
    }

    public function wipIn()
    {
        return $this->belongsTo(TWipIn::class, 'wip_in_id');
    }

    public function injectOut()
    {
        return $this->belongsTo(TInjectOut::class, 'inject_out_id');
    }

    public function planningRun()
    {
        return $this->belongsTo(TPlanningRun::class, 'planning_run_id');
    }

    public function details()
    {
        return $this->hasMany(TWipOutDetail::class, 'wip_out_id');
    }
}

