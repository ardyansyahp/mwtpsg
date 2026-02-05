<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TSupply extends Model
{
    use HasFactory;

    protected $table = 't_supply';

    protected $fillable = [
        'planning_run_id', // nullable untuk ASSY
        'part_id', // nullable, untuk ASSY (input manual part)
        'meja', // nullable, untuk ASSY (input meja)
        'tujuan',
        'tanggal_supply',
        'shift_no',
        'status',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'planning_run_id' => 'integer', // nullable
            'part_id' => 'integer', // nullable
            'tanggal_supply' => 'date',
            'shift_no' => 'integer',
        ];
    }

    public function planningRun()
    {
        return $this->belongsTo(TPlanningRun::class, 'planning_run_id');
    }

    public function part()
    {
        return $this->belongsTo(SMPart::class, 'part_id');
    }

    public function details()
    {
        return $this->hasMany(TSupplyDetail::class, 'supply_id');
    }
}
