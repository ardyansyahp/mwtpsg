<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SMPartSubpart extends Model
{
    use HasFactory;

    protected $table = 'sm_part_subpart';

    public $timestamps = false;

    protected $fillable = [
        'part_id',
        'subpart_id',
        'std_using',
        'urutan',
        // Optional fields (only if columns exist)
        'nama',
        'std_packing',
        'uom',
        'tipe', // 'inject', 'assy', 'both'
    ];

    protected $casts = [
        'std_using' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    protected function casts(): array
    {
        return [
            'std_using' => 'decimal:2',
        ];
    }

    /**
     * Get the part
     */
    public function part()
    {
        return $this->belongsTo(SMPart::class, 'part_id');
    }

    /**
     * Get the subpart (material)
     */
    public function subpart()
    {
        return $this->belongsTo(MBahanBaku::class, 'subpart_id');
    }
}

