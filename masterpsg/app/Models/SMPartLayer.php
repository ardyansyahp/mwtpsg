<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SMPartLayer extends Model
{
    use HasFactory;

    protected $table = 'sm_part_layer';

    public $timestamps = false;

    protected $fillable = [
        'part_id',
        'bahan_baku_id',
        'layer_number',
        'std_using',
        'tipe', // 'inject', 'assy', 'both'
        'jenis_layer',
        'panjang',
        'lebar',
        'tinggi',
    ];

    protected $casts = [
        'std_using' => 'decimal:2',
        'panjang' => 'decimal:2',
        'lebar' => 'decimal:2',
        'tinggi' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    /**
     * Get the part
     */
    public function part()
    {
        return $this->belongsTo(SMPart::class, 'part_id');
    }

    /**
     * Get the bahan baku
     */
    public function bahanBaku()
    {
        return $this->belongsTo(MBahanBaku::class, 'bahan_baku_id');
    }
}

