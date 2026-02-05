<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class MManpower extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'm_manpower';
    
    protected $fillable = [
        'mp_id',
        'nik',
        'nama',
        'departemen',
        'bagian',
        'qrcode',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    /**
     * Scope a query to only include active records.
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
