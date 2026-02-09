<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TScheduleHeader extends Model
{
    protected $table = 't_schedule_header';
    
    protected $fillable = [
        'periode',
        'supplier_id',
        'bahan_baku_id',
        'po_number',
        'total_plan_auto',
        'total_plan_manual',
        'total_plan',
        'total_act',
        'total_blc',
        'total_status',
        'total_ar',
        'total_sr',
        'keterangan',
    ];
    
    protected $casts = [
        'total_plan_auto' => 'decimal:2',
        'total_plan_manual' => 'decimal:2',
        'total_plan' => 'decimal:2',
        'total_act' => 'decimal:2',
        'total_blc' => 'decimal:2',
        'total_ar' => 'decimal:2',
        'total_sr' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(MPerusahaan::class, 'supplier_id');
    }
    
    public function bahanBaku(): BelongsTo
    {
        return $this->belongsTo(MBahanBaku::class, 'bahan_baku_id');
    }
    
    public function details(): HasMany
    {
        return $this->hasMany(TScheduleDetail::class, 'schedule_header_id');
    }
    
    /**
     * Recalculate totals from details
     */
    public function recalculateTotals(): void
    {
        $details = $this->details;
        
        $this->total_plan = $details->sum('pc_plan');
        $this->total_act = $details->sum('pc_act');
        $this->total_blc = $this->total_plan - $this->total_act;
        
        // Calculate AR (Actual Rate)
        if ($this->total_plan > 0) {
            $this->total_ar = ($this->total_act / $this->total_plan) * 100;
        } else {
            $this->total_ar = 0;
        }
        
        // Calculate SR (Service Rate) - based on progress
        $countPlan = $details->where('pc_plan', '>', 0)->count();
        if ($countPlan > 0) {
            $totalProgress = 0;
            foreach ($details->where('pc_plan', '>', 0) as $detail) {
                $progress = min($detail->pc_act / $detail->pc_plan, 1.0);
                $totalProgress += $progress;
            }
            $this->total_sr = ($totalProgress / $countPlan) * 100;
        } else {
            $this->total_sr = 0;
        }
        
        // Update status
        if ($this->total_act == 0) {
            $this->total_status = 'OPEN';
        } elseif ($this->total_act >= $this->total_plan) {
            $this->total_status = 'CLOSE';
        } else {
            $this->total_status = 'PENDING';
        }
        
        $this->save();
    }
}
