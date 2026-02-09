<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SMPartBox extends Model
{
    use HasFactory;

    protected $table = 'sm_part_box';

    protected $fillable = [
        'part_id',
        'box_id',
        'urutan',
        // Optional fields (only if columns exist)
        'tipe',
        'jenis_box',
        'kode_box',
        'panjang',
        'lebar',
        'tinggi',
    ];

    protected $casts = [
        'panjang' => 'decimal:2',
        'lebar' => 'decimal:2',
        'tinggi' => 'decimal:2',
        'qty_pcs' => 'integer',
        'urutan' => 'integer',
    ];

    /**
     * Get the part
     */
    public function part()
    {
        return $this->belongsTo(SMPart::class, 'part_id');
    }

    /**
     * Get the box material
     */
    public function box()
    {
        return $this->belongsTo(MBahanBaku::class, 'box_id');
    }

    /**
     * Alias for box (for consistency)
     */
    public function bahanBaku()
    {
        return $this->box();
    }
}
