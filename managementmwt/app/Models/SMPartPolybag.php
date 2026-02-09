<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SMPartPolybag extends Model
{
    use HasFactory;

    protected $table = 'sm_part_polybag';
    
    public $timestamps = false;
    
    /**
     * Prevent timestamps from being set
     */
    public function setUpdatedAt($value)
    {
        return $this;
    }
    
    public function setCreatedAt($value)
    {
        return $this;
    }
    
    /**
     * Get the attributes that should be converted to dates.
     */
    public function getDates()
    {
        return [];
    }
    
    /**
     * Perform a model insert operation.
     */
    protected function performInsert(\Illuminate\Database\Eloquent\Builder $query)
    {
        // Ensure timestamps are not used
        $this->timestamps = false;
        return parent::performInsert($query);
    }
    
    /**
     * Perform a model update operation.
     */
    protected function performUpdate(\Illuminate\Database\Eloquent\Builder $query)
    {
        // Ensure timestamps are not used
        $this->timestamps = false;
        return parent::performUpdate($query);
    }

    protected $fillable = [
        'part_id',
        'bahan_baku_id', // Changed from polybag_id to match Layer structure
        'tipe',
        'jenis_polybag',
        'polybag_number', // Similar to layer_number
        'panjang',
        'lebar', // Added to match Layer structure
        'tinggi', // Added to match Layer structure
        'std_using',
        'urutan'
    ];

    protected $casts = [
        'panjang' => 'decimal:2',
        'qty_pcs' => 'decimal:2',
        'std_using' => 'decimal:2',
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
     * Get the polybag material (using bahan_baku_id to match Layer structure)
     */
    public function bahanBaku()
    {
        return $this->belongsTo(MBahanBaku::class, 'bahan_baku_id');
    }

    /**
     * Alias for bahanBaku (for backward compatibility)
     */
    public function polybag()
    {
        return $this->bahanBaku();
    }
}
