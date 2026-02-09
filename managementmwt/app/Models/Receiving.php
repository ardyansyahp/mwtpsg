<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Receiving extends Model
{
    use HasFactory;

    protected $table = 'bb_receiving';

    protected $fillable = [
        'tanggal_receiving',
        'supplier_id',
        'no_surat_jalan',
        'no_purchase_order',
        'manpower',
        'shift',
    ];

    protected $casts = [
        'tanggal_receiving' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(MPerusahaan::class, 'supplier_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(ReceivingDetail::class, 'receiving_id');
    }
}
