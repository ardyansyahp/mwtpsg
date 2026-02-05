<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class ReceivingDetail extends Model
{
    use HasFactory;

    protected $table = 'bb_receiving_detail';

    protected $fillable = [
        'receiving_id',
        'schedule_detail_id',
        'nomor_bahan_baku',
        'lot_number',
        'internal_lot_number',
        'qty',
        'uom',
        'qrcode',
    ];

    protected $casts = [
        'qty' => 'decimal:3',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function receiving(): BelongsTo
    {
        return $this->belongsTo(Receiving::class, 'receiving_id');
    }

    public function bahanBaku(): BelongsTo
    {
        return $this->belongsTo(MBahanBaku::class, 'nomor_bahan_baku', 'nomor_bahan_baku');
    }

    public function scheduleDetail(): BelongsTo
    {
        return $this->belongsTo(TScheduleDetail::class, 'schedule_detail_id');
    }
}
