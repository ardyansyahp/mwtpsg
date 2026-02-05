<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TPlanningMaterialOrderProduksi extends Model
{
    use HasFactory;

    protected $table = 't_planning_material_order_produksi';

    protected $fillable = [
        'planning_run_material_id',
        'qty_prd_order',
        'qty_return',
    ];

    protected function casts(): array
    {
        return [
            'qty_prd_order' => 'decimal:3',
            'qty_return' => 'decimal:3',
        ];
    }

    public function planningRunMaterial()
    {
        return $this->belongsTo(TPlanningRunMaterial::class, 'planning_run_material_id');
    }
}
