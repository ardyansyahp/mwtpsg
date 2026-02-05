<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TInjectOutDetail extends Model
{
    use HasFactory;

    protected $table = 't_inject_out_detail';

    protected $fillable = [
        'inject_out_id',
        'box_number',
        'waktu_scan',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'inject_out_id' => 'integer',
            'box_number' => 'integer',
            'waktu_scan' => 'datetime',
        ];
    }

    public function injectOut()
    {
        return $this->belongsTo(TInjectOut::class, 'inject_out_id');
    }
}
