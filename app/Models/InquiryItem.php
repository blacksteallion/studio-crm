<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InquiryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'inquiry_id',
        'product_service_id',
        'item_name',
        'unit_price',
        'gst_rate',   // <--- ADDED THIS
        'quantity',
        'gst_amount', // <--- ADDED THIS
        'total'
    ];

    public function inquiry()
    {
        return $this->belongsTo(Inquiry::class);
    }

    public function productService()
    {
        return $this->belongsTo(ProductService::class);
    }
}