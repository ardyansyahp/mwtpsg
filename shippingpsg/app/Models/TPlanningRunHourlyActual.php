<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TPlanningRunHourlyActual extends Model
{
    use HasFactory;

    protected $table = 't_planning_run_hourly_actual';

    protected $fillable = [
        'planning_run_id',
        'hour_start',
        'hour_end',
        'qty_actual',
    ];

    protected function casts(): array
    {
        return [
            'hour_start' => 'datetime',
            'hour_end' => 'datetime',
            'qty_actual' => 'integer',
        ];
    }

    public function run()
    {
        return $this->belongsTo(TPlanningRun::class, 'planning_run_id');
    }
}
