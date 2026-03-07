<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'item_name',
        'quantity',
        'unit_price',
        'gst_rate',   // <--- Added
        'gst_amount', // <--- Added
        'amount'      // This is the Total (Base + Tax)
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}