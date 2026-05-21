<?php
// app/Models/CookieScanCookie.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CookieScanCookie extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'cookie_scan_id',
        'name','value','domain','path',
        'expires','expires_timestamp','size',
        'http_only','secure','same_site','session',
        'type','is_first_party','description',
    ];

    protected $casts = [
        'http_only' => 'boolean',
        'secure' => 'boolean',
        'session' => 'boolean',
        'is_first_party' => 'boolean',

        // IMPORTANT:
        // This encrypts value at rest in DB, and decrypts when you read it.
        // If you decide later you don’t want values stored, remove this and stop saving value.
        'value' => 'encrypted',
    ];

    public function scan(): BelongsTo
    {
        return $this->belongsTo(CookieScan::class, 'cookie_scan_id');
    }
}
