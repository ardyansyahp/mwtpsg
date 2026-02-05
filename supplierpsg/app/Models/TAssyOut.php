<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TAssyOut extends Model
{
    use HasFactory;

    protected $table = 't_assy_out';

    protected $fillable = [
        'assy_in_id',
        'lot_number',
        'part_id',
        'waktu_scan',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'assy_in_id' => 'integer',
            'part_id' => 'integer',
            'waktu_scan' => 'datetime',
        ];
    }

    public function assyIn()
    {
        return $this->belongsTo(TAssyIn::class, 'assy_in_id');
    }

    public function part()
    {
        return $this->belongsTo(SMPart::class, 'part_id');
    }

    public function finishGoodIns()
    {
        return $this->hasMany(TFinishGoodIn::class, 'assy_out_id');
    }
}
