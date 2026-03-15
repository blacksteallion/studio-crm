<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappNumber extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone_number_id',
        'phone_number_name',
        'access_token',
        'welcome_template_name',
        'assigned_staff_id',
        'is_active'
    ];

    // Relationship: A WhatsApp Number belongs to a Staff Member
    public function staff()
    {
        return $this->belongsTo(User::class, 'assigned_staff_id');
    }
}