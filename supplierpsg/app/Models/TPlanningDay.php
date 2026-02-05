<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TPlanningDay extends Model
{
    use HasFactory;

    protected $table = 't_planning_day';

    protected $fillable = [
        'tanggal',
        'tipe',
        'mesin_id',
        'meja',
        'status',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function mesin()
    {
        return $this->belongsTo(\App\Models\MMesin::class, 'mesin_id');
    }

    public function runs()
    {
        return $this->hasMany(\App\Models\TPlanningRun::class, 'planning_day_id');
    }
}
