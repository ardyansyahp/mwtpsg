<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SMPartRempart extends Model
{
    use HasFactory;

    protected $table = 'sm_part_rempart';

    protected $fillable = [
        'part_id',
        'tipe',
        'R_Karton_Box_id',
        'R_Polybag_id',
        'R_Gasket_Duplex_id',
        'R_Foam_Sheet_id',
        'R_Hologram_id',
        'R_LabelA_id',
        'R_LabelB_id',
        'R_Qty_Pcs',
        'urutan'
    ];

    protected $casts = [
        'R_Qty_Pcs' => 'integer',
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
     * Get the rempart karton box
     */
    public function rempartKartonBox()
    {
        return $this->belongsTo(MBahanBaku::class, 'R_Karton_Box_id');
    }

    /**
     * Get the rempart polybag
     */
    public function rempartPolybag()
    {
        return $this->belongsTo(MBahanBaku::class, 'R_Polybag_id');
    }

    /**
     * Get the rempart gasket duplex
     */
    public function rempartGasketDuplex()
    {
        return $this->belongsTo(MBahanBaku::class, 'R_Gasket_Duplex_id');
    }

    /**
     * Get the rempart foam sheet
     */
    public function rempartFoamSheet()
    {
        return $this->belongsTo(MBahanBaku::class, 'R_Foam_Sheet_id');
    }

    /**
     * Get the rempart hologram
     */
    public function rempartHologram()
    {
        return $this->belongsTo(MBahanBaku::class, 'R_Hologram_id');
    }

    /**
     * Get the rempart label A
     */
    public function rempartLabelA()
    {
        return $this->belongsTo(MBahanBaku::class, 'R_LabelA_id');
    }

    /**
     * Get the rempart label B
     */
    public function rempartLabelB()
    {
        return $this->belongsTo(MBahanBaku::class, 'R_LabelB_id');
    }
}
