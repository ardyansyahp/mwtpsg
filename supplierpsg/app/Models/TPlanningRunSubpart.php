<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TPlanningRunSubpart extends Model
{
    use HasFactory;

    protected $table = 't_planning_run_subpart';

    protected $fillable = [
        'planning_run_id',
        'partsubpart_id',
        'qty_total',
        'uom',
    ];

    protected function casts(): array
    {
        return [
            'qty_total' => 'decimal:3',
        ];
    }

    public function run()
    {
        return $this->belongsTo(\App\Models\TPlanningRun::class, 'planning_run_id');
    }

    public function partsubpart()
    {
        return $this->belongsTo(SMPartSubpart::class, 'partsubpart_id');
    }

    public function shifts()
    {
        return $this->hasMany(\App\Models\TPlanningRunSubpartShift::class, 'planning_run_subpart_id');
    }
}
