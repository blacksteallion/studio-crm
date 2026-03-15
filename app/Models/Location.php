<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'address', 
        'contact_number', 
        'is_active'
    ];

    public function users() {
        return $this->belongsToMany(User::class);
    }

    public function customers() {
        return $this->belongsToMany(Customer::class);
    }

    public function productServices() {
        return $this->belongsToMany(ProductService::class);
    }

    public function bookings() {
        return $this->hasMany(Booking::class);
    }

    public function orders() {
        return $this->hasMany(Order::class);
    }

    public function expenses() {
        return $this->hasMany(Expense::class);
    }

    public function inquiries() {
        return $this->hasMany(Inquiry::class);
    }
}