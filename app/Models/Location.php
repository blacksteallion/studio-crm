<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Add these missing relationships so the Controller can check them!
    
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function inquiries()
    {
        return $this->hasMany(Inquiry::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // You likely already have users() or other methods below here...
}