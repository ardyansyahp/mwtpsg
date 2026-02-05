<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SMPartMaterial extends Model
{
    use HasFactory;

    protected $table = 'sm_part_material';

    protected $fillable = [
        'part_id',
        'material_id',
        'material_type',
        'tipe',
        'std_using',
        'urutan'
    ];

    protected $casts = [
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
     * Get the material
     */
    public function material()
    {
        return $this->belongsTo(MBahanBaku::class, 'material_id');
    }

    /**
     * Alias for material (for consistency)
     */
    public function bahanBaku()
    {
        return $this->material();
    }
}
