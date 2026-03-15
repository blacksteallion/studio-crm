<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'mobile',
        'business_name',
        'website',        // New
        'gst_number',     // New
        'address_line1',  // New
        'address_line2',  // New
        'city',           // New
        'state',          // New
        'pincode',        // New
        'country',        // New
        'remarks',
        'photo',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function inquiries()
    {
        return $this->hasMany(Inquiry::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}