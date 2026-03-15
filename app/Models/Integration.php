<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Integration extends Model
{
    protected $fillable = [
        'platform',
        'page_id',
        'page_name',
        'access_token',
        'field_mapping',
        'is_active',
        'last_synced_at',
        'last_error',
    ];

    /**
     * The attributes that should be cast.
     * 'access_token' => 'encrypted' is CRITICAL. 
     * It prevents leakage if the DB is compromised.
     */
    protected $casts = [
        'field_mapping' => 'array',
        'access_token' => 'encrypted',
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    /**
     * Helper to get a mapping for a specific Meta field key.
     * Returns the CRM database column name.
     */
    public function getMappedField($metaKey)
    {
        return $this->field_mapping[$metaKey] ?? null;
    }
}