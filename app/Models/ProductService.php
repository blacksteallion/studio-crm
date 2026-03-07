<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductService extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type', 
        'pricing_model', 
        'price',
        'gst_rate', // <--- ADDED THIS
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'gst_rate' => 'decimal:2' // <--- ADDED THIS
    ];
}