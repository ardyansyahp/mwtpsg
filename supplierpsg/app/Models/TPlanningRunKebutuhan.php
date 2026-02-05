<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TPlanningRunKebutuhan extends Model
{
    use HasFactory;

    protected $table = 't_planning_run_kebutuhan';

    protected $fillable = [
        'planning_run_id',
        'qty_polybox',
        'qty_partisi',
        'qty_imfrabolt',
        'qty_karton',
        'qty_troly',
    ];

    protected function casts(): array
    {
        return [
            'qty_polybox' => 'integer',
            'qty_partisi' => 'integer',
            'qty_imfrabolt' => 'integer',
            'qty_karton' => 'integer',
            'qty_troly' => 'integer',
        ];
    }

    public function run()
    {
        return $this->belongsTo(TPlanningRun::class, 'planning_run_id');
    }
}
