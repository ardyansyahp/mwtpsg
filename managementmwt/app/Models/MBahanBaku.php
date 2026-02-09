<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MBahanBaku extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'm_bahanbaku';

    // public $timestamps = false; // Migration now has timestamps

    protected $fillable = [
        'kategori',
        'nama_bahan_baku',
        'nomor_bahan_baku',
        'supplier_id',
        'status',
        'qrcode',
        'keterangan',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'status' => 'boolean',
    ];

    public function supplier()
    {
        return $this->belongsTo(MPerusahaan::class, 'supplier_id');
    }

    /**
     * Get material detail (for MATERIAL, MASTERBATCH)
     */
    public function material()
    {
        return $this->hasOne(MBahanBakuMaterial::class, 'bahan_baku_id');
    }

    /**
     * Get subpart detail
     */
    public function subpart()
    {
        return $this->hasOne(MBahanBakuSubpart::class, 'bahan_baku_id');
    }

    /**
     * Get box detail
     */
    public function box()
    {
        return $this->hasOne(MBahanBakuBox::class, 'bahan_baku_id');
    }

    /**
     * Get layer detail
     */
    public function layer()
    {
        return $this->hasOne(MBahanBakuLayer::class, 'bahan_baku_id');
    }

    /**
     * Get polybag detail
     */
    public function polybag()
    {
        return $this->hasOne(MBahanBakuPolybag::class, 'bahan_baku_id');
    }

    /**
     * Get rempart detail
     */
    public function rempart()
    {
        return $this->hasOne(MBahanBakuRempart::class, 'bahan_baku_id');
    }

    /**
     * Get detail based on kategori
     */
    public function getDetailAttribute()
    {
        return match($this->kategori) {
            'material', 'masterbatch' => $this->material,
            'subpart' => $this->subpart,
            'box' => $this->box,
            'layer' => $this->layer,
            'polybag' => $this->polybag,
            'rempart' => $this->rempart,
            default => null,
        };
    }

    /**
     * Get nama bahan baku (dari detail atau fallback)
     */
    public function getNamaBahanBakuAttribute($value)
    {
        // Use details name if available, otherwise use data from m_bahanbaku table
        return $this->detail?->nama_bahan_baku ?? $value;
    }

    /**
     * Get formatted kategori label
     */
    public function getKategoriLabelAttribute()
    {
        $labels = [
            'material' => 'MATERIAL',
            'masterbatch' => 'MASTERBATCH',
            'subpart' => 'SUBPART',
            'box' => 'BOX',
            'layer' => 'LAYER',
            'polybag' => 'POLYBAG',
            'rempart' => 'REMPART',
        ];

        return $labels[$this->kategori] ?? strtoupper($this->kategori);
    }
}

