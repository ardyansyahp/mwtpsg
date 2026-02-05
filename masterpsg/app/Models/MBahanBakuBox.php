<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MBahanBakuBox extends Model
{
    use HasFactory;

    protected $table = 'm_bahanbaku_box';

    protected $fillable = [
        'bahan_baku_id',
        'jenis',
        'kode_box',
        'panjang',
        'lebar',
        'tinggi',
    ];

    protected $casts = [
        'panjang' => 'decimal:2',
        'lebar' => 'decimal:2',
        'tinggi' => 'decimal:2',
    ];

    /**
     * Get the bahan baku
     */
    public function bahanBaku()
    {
        return $this->belongsTo(MBahanBaku::class, 'bahan_baku_id');
    }
}
