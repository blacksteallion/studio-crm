<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'product_service_id',
        'item_name',
        'unit_price',
        'gst_rate',   // <--- ADDED THIS
        'quantity',
        'gst_amount', // <--- ADDED THIS
        'total'
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function productService()
    {
        return $this->belongsTo(ProductService::class);
    }
}