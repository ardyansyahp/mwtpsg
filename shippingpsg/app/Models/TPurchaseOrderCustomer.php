<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TPurchaseOrderCustomer extends Model
{
    use HasFactory;

    protected $table = 't_purchase_order_customer';

    protected $fillable = [
        'part_id',
        'po_number',
        'qty',
        'delivery_frequency',
        'month',
        'year',
    ];

    public function part()
    {
        return $this->belongsTo(SMPart::class, 'part_id');
    }
}
