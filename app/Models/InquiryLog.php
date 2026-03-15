<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InquiryLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'inquiry_id',
        'user_id',   // <--- IMPORTANT: This must be here
        'type',
        'message',
        'log_date',
        'log_time'
    ];

    protected $casts = [
        'log_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function inquiry()
    {
        return $this->belongsTo(Inquiry::class);
    }
}