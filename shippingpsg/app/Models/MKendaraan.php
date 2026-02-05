<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MKendaraan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'm_kendaraan';

    protected $fillable = [
        'nopol_kendaraan',
        'jenis_kendaraan',
        'merk_kendaraan',
        'tahun_kendaraan',
        'status',
        'qrcode',
    ];

    protected $casts = [
        'tahun_kendaraan' => 'integer',
        'status' => 'boolean',
    ];

    /**
     * Scope a query to only include active records.
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
