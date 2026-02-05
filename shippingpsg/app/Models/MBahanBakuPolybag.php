<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MBahanBakuPolybag extends Model
{
    use HasFactory;

    protected $table = 'm_bahanbaku_polybag';

    protected $fillable = [
        'bahan_baku_id',
        'jenis',
        'panjang',
        'lebar',
        'tinggi',
        'std_packing',
        'uom',
        'jenis_packing'
    ];

    protected $casts = [
        'panjang' => 'decimal:2',
        'lebar' => 'decimal:2',
        'tinggi' => 'decimal:2',
        'std_packing' => 'decimal:2',
    ];

    /**
     * Get the bahan baku
     */
    public function bahanBaku()
    {
        return $this->belongsTo(MBahanBaku::class, 'bahan_baku_id');
    }
}
