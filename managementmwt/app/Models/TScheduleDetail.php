<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TScheduleDetail extends Model
{
    protected $table = 't_schedule_detail';
    
    protected $fillable = [
        'schedule_header_id',
        'tanggal',
        'po_number',
        'pc_plan',
        'pc_act',
        'pc_blc',
        'pc_status',
        'pc_ar',
        'pc_sr',
    ];
    
    protected $casts = [
        'tanggal' => 'date',
        'pc_plan' => 'decimal:2',
        'pc_act' => 'decimal:2',
        'pc_blc' => 'decimal:2',
        'pc_ar' => 'decimal:2',
        'pc_sr' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    public function header(): BelongsTo
    {
        return $this->belongsTo(TScheduleHeader::class, 'schedule_header_id');
    }
    
    /**
     * Recalculate BLC, AR, SR after update
     */
    public function recalculate(): void
    {
        // Get all details for cumulative calculation
        $allDetails = $this->header->details()
            ->where('tanggal', '<=', $this->tanggal)
            ->orderBy('tanggal')
            ->get();
        
        $cumulativePlan = $allDetails->sum('pc_plan');
        $cumulativeAct = $allDetails->sum('pc_act');
        
        $this->pc_blc = $cumulativePlan - $cumulativeAct;
        
        // AR
        if ($cumulativePlan > 0) {
            $this->pc_ar = ($cumulativeAct / $cumulativePlan) * 100;
        } else {
            $this->pc_ar = 0;
        }
        
        // SR
        if ($this->pc_plan > 0) {
            $progress = min($cumulativeAct / $this->pc_plan, 1.0);
            $this->pc_sr = $progress * 100;
        } else {
            $this->pc_sr = 0;
        }
        
        // Status
        if ($cumulativeAct == 0) {
            $this->pc_status = 'PENDING';
        } elseif ($cumulativeAct >= $cumulativePlan && $cumulativePlan > 0) {
            $this->pc_status = 'CLOSE';
        } else {
            $this->pc_status = 'PENDING';
        }
        
        $this->save();
        
        // Trigger header recalculation
        $this->header->recalculateTotals();
    }
}
