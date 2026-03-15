<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id', // Links inquiry to a specific branch
        'customer_id',
        'business_name',
        'lead_source_id',
        'primary_date',
        'alternate_date',
        'from_time',
        'to_time',
        'total_hours',
        'budget',
        'assigned_staff_id',
        'status',
        'follow_up_date'
    ];

    protected $casts = [
        'primary_date' => 'date',
        'alternate_date' => 'date',
        'follow_up_date' => 'date',
        'total_hours' => 'decimal:2',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignedStaff()
    {
        return $this->belongsTo(User::class, 'assigned_staff_id');
    }

    public function logs()
    {
        return $this->hasMany(InquiryLog::class)->latest();
    }
    
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function leadSource()
    {
        return $this->belongsTo(LeadSource::class, 'lead_source_id');
    }

    public function items()
    {
        return $this->hasMany(InquiryItem::class);
    }
}