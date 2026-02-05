<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TPlanningRunMaterial extends Model
{
    use HasFactory;

    protected $table = 't_planning_run_material';

    protected $fillable = [
        'planning_run_id',
        'material_id',
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

    public function material()
    {
        return $this->belongsTo(MBahanBaku::class, 'material_id');
    }

    public function shifts()
    {
        return $this->hasMany(\App\Models\TPlanningRunMaterialShift::class, 'planning_run_material_id');
    }

    public function orders()
    {
        return $this->hasMany(\App\Models\TPlanningMaterialOrderProduksi::class, 'planning_run_material_id');
    }
}
