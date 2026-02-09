<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TStockOpname extends Model
{
    use HasFactory;

    protected $table = 't_stock_opname';

    protected $fillable = [
        'part_id',
        'qty_system',
        'qty_actual',
        'diff',
        'manpower_id',
        'keterangan',
    ];

    public function part()
    {
        return $this->belongsTo(SMPart::class, 'part_id');
    }

    public function manpower()
    {
        return $this->belongsTo(MManpower::class, 'manpower_id');
    }
}
