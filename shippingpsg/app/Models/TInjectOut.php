<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TInjectOut extends Model
{
    use HasFactory;

    protected $table = 't_inject_out';

    protected $fillable = [
        'inject_in_id',
        'lot_number',
        'planning_run_id',
        'waktu_scan',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'inject_in_id' => 'integer',
            'planning_run_id' => 'integer',
            'waktu_scan' => 'datetime',
        ];
    }

    public function injectIn()
    {
        return $this->belongsTo(TInjectIn::class, 'inject_in_id');
    }

    public function planningRun()
    {
        return $this->belongsTo(TPlanningRun::class, 'planning_run_id');
    }

    public function details()
    {
        return $this->hasMany(TInjectOutDetail::class, 'inject_out_id');
    }

    public function wipIn()
    {
        return $this->hasOne(TWipIn::class, 'inject_out_id');
    }
}
