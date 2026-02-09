<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TSpk extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 't_spk';

    protected $fillable = [
        'nomor_spk',
        'parent_spk_id',
        'cycle_number',
        'manpower_pembuat',
        'customer_id',
        'plantgate_id',
        'tanggal',
        'jam_berangkat_plan',
        'jam_datang_plan',
        'cycle',
        'no_surat_jalan',
        'nomor_plat',
        'driver_id',
        'model_part',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'datetime',
            'driver_id' => 'integer',
            'parent_spk_id' => 'integer',
            'cycle_number' => 'integer',
        ];
    }

    public function driver()
    {
        return $this->belongsTo(MManpower::class, 'driver_id');
    }

    public function customer()
    {
        return $this->belongsTo(MPerusahaan::class, 'customer_id');
    }
    
    public function plantgate()
    {
        return $this->belongsTo(MPlantGate::class, 'plantgate_id');
    }

    public function details()
    {
        return $this->hasMany(TSpkDetail::class, 'spk_id');
    }

    public function finishGoodOuts()
    {
        return $this->hasMany(TFinishGoodOut::class, 'spk_id');
    }

    public function parentSpk()
    {
        return $this->belongsTo(TSpk::class, 'parent_spk_id');
    }

    public function childSpk()
    {
        return $this->hasOne(TSpk::class, 'parent_spk_id');
    }

    public function deliveryHeader()
    {
        return $this->hasOne(TShippingDeliveryHeader::class, 'no_surat_jalan', 'no_surat_jalan');
    }
}
