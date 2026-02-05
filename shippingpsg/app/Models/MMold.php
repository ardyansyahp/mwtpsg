<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MMold extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'm_mold';

    protected $fillable = [
        'mold_id',
        'perusahaan_id',
        'part_id',
        'kode_mold',
        'nomor_mold',
        'cavity',
        'cycle_time',
        'capacity',
        'lokasi_mold',
        'tipe_mold',
        'material_resin',
        'warna_produk',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'cavity' => 'integer',
            'cycle_time' => 'decimal:2',
            'capacity' => 'integer',
            'status' => 'boolean',
        ];
    }

    public function perusahaan()
    {
        return $this->belongsTo(MPerusahaan::class, 'perusahaan_id');
    }

    public function part()
    {
        return $this->belongsTo(SMPart::class, 'part_id');
    }

    public function planningRuns()
    {
        return $this->hasMany(\App\Models\TPlanningRun::class, 'mold_id');
    }
}
