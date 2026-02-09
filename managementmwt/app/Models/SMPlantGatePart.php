<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SMPlantGatePart extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sm_plantgate_part';

    protected $fillable = [
        'plantgate_id',
        'part_id',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    // Relationships
    public function plantgate()
    {
        return $this->belongsTo(MPlantGate::class, 'plantgate_id');
    }

    public function part()
    {
        return $this->belongsTo(SMPart::class, 'part_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', false);
    }
}
