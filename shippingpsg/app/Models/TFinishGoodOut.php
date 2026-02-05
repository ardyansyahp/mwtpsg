<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TFinishGoodOut extends Model
{
    use HasFactory;

    protected $table = 't_finishgood_out';

    protected $fillable = [
        'finish_good_in_id',
        'lot_number',
        'spk_id',
        'part_id',
        'waktu_scan_out',
        'catatan',
        'cycle',
        'qty',
        'no_surat_jalan',
    ];

    protected function casts(): array
    {
        return [
            'finish_good_in_id' => 'integer',
            'spk_id' => 'integer',
            'part_id' => 'integer',
            'waktu_scan_out' => 'datetime',
            'cycle' => 'integer',
            'qty' => 'integer',
        ];
    }

    public function finishGoodIn()
    {
        return $this->belongsTo(TFinishGoodIn::class, 'finish_good_in_id');
    }

    public function spk()
    {
        return $this->belongsTo(TSpk::class, 'spk_id');
    }

    public function part()
    {
        return $this->belongsTo(SMPart::class, 'part_id');
    }
}
