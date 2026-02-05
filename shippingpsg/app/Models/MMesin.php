<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class MMesin extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'm_mesin';

    protected $fillable = [
        'mesin_id',
        'no_mesin',
        'merk_mesin',
        'tonase',
        'qrcode',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tonase' => 'integer',
            'status' => 'boolean',
        ];
    }

    /**
     * Scope a query to only include active machines.
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function planningDays()
    {
        return $this->hasMany(\App\Models\TPlanningDay::class, 'mesin_id');
    }
}
