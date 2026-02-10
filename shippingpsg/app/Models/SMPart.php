<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class SMPart extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sm_part';

    protected $fillable = [
        'nomor_part',
        'nama_part',
        'min_stock',
        'max_stock',
        'customer_id',
        'tipe_id',
        'model_part',
        'proses',
        'parent_part_id',
        'relation_type',
        'CT_Inject',
        'CT_Assy',
        'Warna_Label_Packing',
        'QTY_Packing_Box',
        'R_Karton_Box_id',
        'N_Cav1', // Used for netto
        'Runner',
        'Avg_Brutto', // Used for brutto
    ];

    protected function casts(): array
    {
        return [
            'CT_Inject' => 'decimal:2',
            'CT_Assy' => 'decimal:2',
            'N_Cav1' => 'decimal:3', // Used for netto
            'Runner' => 'decimal:3',
            'Avg_Brutto' => 'decimal:3', // Used for brutto
            'min_stock' => 'integer',
            'max_stock' => 'integer',
        ];
    }

    /**
     * Get the customer company
     */
    public function customer()
    {
        return $this->belongsTo(MPerusahaan::class, 'customer_id');
    }

    /**
     * Get the parent part (source part)
     */
    public function parentPart()
    {
        return $this->belongsTo(SMPart::class, 'parent_part_id');
    }

    /**
     * Get child parts (parts that use this as parent)
     */
    public function childParts()
    {
        return $this->hasMany(SMPart::class, 'parent_part_id');
    }

    /**
     * Get next process parts (e.g., ASSY parts from INJECT part)
     */
    public function getNextProcessParts($relationType = 'inject_to_assy')
    {
        return $this->childParts()->where('relation_type', $relationType)->get();
    }


    /**
     * Get all part-subpart relationships
     */
    public function partSubparts()
    {
        return $this->hasMany(SMPartSubpart::class, 'part_id');
    }

    /**
     * Get all part-layer relationships
     */
    public function partLayers()
    {
        return $this->hasMany(SMPartLayer::class, 'part_id');
    }

    /**
     * Get all part-box relationships
     */
    public function partBoxes()
    {
        return $this->hasMany(SMPartBox::class, 'part_id')->orderBy('urutan');
    }

    /**
     * Get all part-polybag relationships
     */
    public function partPolybags()
    {
        return $this->hasMany(SMPartPolybag::class, 'part_id')->orderBy('urutan');
    }

    /**
     * Get all part-rempart relationships
     */
    public function partRemparts()
    {
        return $this->hasMany(SMPartRempart::class, 'part_id')->orderBy('urutan');
    }

    /**
     * Get all part-material relationships
     */
    public function partMaterials()
    {
        return $this->hasMany(SMPartMaterial::class, 'part_id')->orderBy('material_type')->orderBy('urutan');
    }

    /**
     * Get the current stock record
     */
    public function stockFg()
    {
        return $this->hasOne(TStockFG::class, 'part_id');
    }

    /**
     * Get the latest stock opname record
     */
    public function latestStockOpname()
    {
        return $this->hasOne(\App\Models\TStockOpname::class, 'part_id')->latestOfMany();
    }
}

