<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TStockFG extends Model
{
    use HasFactory;

    protected $table = 't_stock_fg';

    protected $fillable = [
        'part_id',
        'qty',
    ];

    public function part()
    {
        return $this->belongsTo(SMPart::class, 'part_id');
    }
}
