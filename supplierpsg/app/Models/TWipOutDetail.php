<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TWipOutDetail extends Model
{
    use HasFactory;

    protected $table = 't_wip_out_detail';

    protected $fillable = [
        'wip_out_id',
        'box_number',
        'waktu_scan',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'wip_out_id' => 'integer',
            'box_number' => 'integer',
            'waktu_scan' => 'datetime',
        ];
    }

    public function wipOut()
    {
        return $this->belongsTo(TWipOut::class, 'wip_out_id');
    }
}
