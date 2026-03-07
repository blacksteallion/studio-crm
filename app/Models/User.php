<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles; // <--- 1. SPATIE IMPORT ADDED

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles; // <--- 2. HASROLES TRAIT ADDED

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'mobile',
        'photo',
        'role',
        'status', 
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'status' => 'boolean', 
    ];

    /**
     * Multi-Location Relationship
     */
    public function locations()
    {
        return $this->belongsToMany(Location::class);
    }
}