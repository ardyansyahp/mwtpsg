<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MPerusahaan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'm_perusahaan';

    protected $fillable = [
        'nama_perusahaan',
        'inisial_perusahaan',
        'jenis_perusahaan',
        'customer_type',
        'kode_supplier',
        'alamat',
        'status',
    ];

    /**
     * Get all bahan baku that use this company as supplier
     */
    public function bahanBakus()
    {
        return $this->hasMany(MBahanBaku::class, 'supplier_id');
    }

    /**
     * Get all parts that use this company as customer
     */
    public function partsAsCustomer()
    {
        return $this->hasMany(SMPart::class, 'customer_id');
    }
    /**
     * Scope a query to only include active companies.
     * Use: MPerusahaan::active()->get();
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}

