<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MaterialBalance extends Model
{
    use HasFactory;

    protected $table = 'sm_balance_bb';

    protected $fillable = [
        'nomor_bahan_baku',
        'kategori',
        'lot_number',
        'qty_available',
        'qty_reserved',
        'uom',
        'tanggal',
        'qrcode',
    ];

    protected $casts = [
        'qty_available' => 'decimal:3',
        'qty_reserved' => 'decimal:3',
        'tanggal' => 'date',
    ];

    /**
     * Get the bahan baku
     */
    public function bahanBaku()
    {
        return $this->belongsTo(MBahanBaku::class, 'nomor_bahan_baku', 'nomor_bahan_baku');
    }

    /**
     * Get available quantity for a material
     */
    public static function getAvailable($nomorBahanBaku, $kategori, $lotNumber = null)
    {
        $query = self::where('nomor_bahan_baku', $nomorBahanBaku)
            ->where('kategori', $kategori)
            ->where('qty_available', '>', 0);
        
        if ($lotNumber) {
            $query->where('lot_number', $lotNumber);
        }
        
        return $query->sum('qty_available');
    }

    /**
     * Update balance after supply
     */
    public static function updateBalance($nomorBahanBaku, $kategori, $suppliedQty, $usedQty, $uom, $lotNumber = null, $qrcode = null, $tanggal = null)
    {
        $remaining = $suppliedQty - $usedQty;
        
        if ($remaining > 0) {
            self::updateOrCreate(
                [
                    'nomor_bahan_baku' => $nomorBahanBaku,
                    'kategori' => $kategori,
                    'lot_number' => $lotNumber,
                    'qrcode' => $qrcode,
                ],
                [
                    'qty_available' => DB::raw("COALESCE(qty_available, 0) + {$remaining}"),
                    'uom' => $uom,
                    'tanggal' => $tanggal ?? now(),
                ]
            );
        }
    }

    /**
     * Reserve quantity for planning
     */
    public static function reserve($nomorBahanBaku, $kategori, $qty, $lotNumber = null)
    {
        // Find available balance
        $balance = self::where('nomor_bahan_baku', $nomorBahanBaku)
            ->where('kategori', $kategori)
            ->where('qty_available', '>', 0)
            ->orderBy('tanggal', 'asc') // FIFO: First In First Out
            ->first();
        
        if ($balance && $balance->qty_available >= $qty) {
            $balance->qty_available -= $qty;
            $balance->qty_reserved += $qty;
            $balance->save();
            return true;
        }
        
        return false;
    }
}
