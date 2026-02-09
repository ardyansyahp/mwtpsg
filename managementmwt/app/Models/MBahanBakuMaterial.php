<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MBahanBakuMaterial extends Model
{
    use HasFactory;

    protected $table = 'm_bahanbaku_material';

    protected $fillable = [
        'bahan_baku_id',
        'nama_bahan_baku',
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
