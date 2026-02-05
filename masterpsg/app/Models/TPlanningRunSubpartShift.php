<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TPlanningRunSubpartShift extends Model
{
    use HasFactory;

    protected $table = 't_planning_run_subpart_shift';

    protected $fillable = [
        'planning_run_subpart_id',
        'shift_no',
        'qty',
    ];

    protected function casts(): array
    {
        return [
            'shift_no' => 'integer',
            'qty' => 'integer',
        ];
    }

    public function planningRunSubpart()
    {
        return $this->belongsTo(TPlanningRunSubpart::class, 'planning_run_subpart_id');
    }
}
