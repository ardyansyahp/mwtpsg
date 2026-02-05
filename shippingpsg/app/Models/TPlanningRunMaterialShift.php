<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TPlanningRunMaterialShift extends Model
{
    use HasFactory;

    protected $table = 't_planning_run_material_shift';

    protected $fillable = [
        'planning_run_material_id',
        'shift_no',
        'qty',
    ];

    protected function casts(): array
    {
        return [
            'shift_no' => 'integer',
            'qty' => 'decimal:3',
        ];
    }

    public function planningRunMaterial()
    {
        return $this->belongsTo(TPlanningRunMaterial::class, 'planning_run_material_id');
    }
}
