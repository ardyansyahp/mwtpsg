<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TWipIn extends Model
{
    use HasFactory;

    protected $table = 't_wip_in';

    protected $fillable = [
        'inject_out_id',
        'lot_number',
        'box_number',
        'planning_run_id',
        'waktu_scan_in',
        'is_confirmed',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'inject_out_id' => 'integer',
            'planning_run_id' => 'integer',
            'box_number' => 'integer',
            'waktu_scan_in' => 'datetime',
            'is_confirmed' => 'boolean',
        ];
    }

    public function injectOut()
    {
        return $this->belongsTo(TInjectOut::class, 'inject_out_id');
    }

    public function planningRun()
    {
        return $this->belongsTo(TPlanningRun::class, 'planning_run_id');
    }

    public function wipOut()
    {
        return $this->hasOne(TWipOut::class, 'wip_in_id');
    }

    public function wipOuts()
    {
        return $this->hasMany(TWipOut::class, 'wip_in_id');
    }
}

