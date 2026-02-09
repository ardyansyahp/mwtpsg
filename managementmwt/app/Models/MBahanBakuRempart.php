<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MBahanBakuRempart extends Model
{
    use HasFactory;

    protected $table = 'm_bahanbaku_rempart';

    protected $fillable = [
        'bahan_baku_id',
        'jenis',
        'std_packing',
        'uom',
        'jenis_packing'
    ];

    protected $casts = [
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
