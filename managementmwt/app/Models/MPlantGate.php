<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class MPlantGate extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'm_plantgate';

    protected $fillable = [
        'customer_id',
        'nama_plantgate',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(MPerusahaan::class, 'customer_id');
    }

    public function parts()
    {
        return $this->belongsToMany(SMPart::class, 'SM_PlantGate_Part', 'plantgate_id', 'part_id')
            ->withTimestamps();
    }
}
