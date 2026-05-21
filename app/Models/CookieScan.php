<?php
// app/Models/CookieScan.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CookieScan extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'status', 'saas_scan_id', 'error_message',
        'domain','url','scanned_at',
        'total','first_party','third_party','secure','http_only',
        'essential','functional','analytics','marketing',
        'privacy_score','privacy_grade',
        'requested_domains','third_party_domains','ga_detected','raw_payload',
        'created_by',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
        'requested_domains' => 'array',
        'third_party_domains' => 'array',
        'ga_detected' => 'array',
        'raw_payload' => 'array',
    ];

    public function isPending(): bool
    {
        return in_array($this->status, ['pending', 'scanning']);
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, ['pending', 'scanning']);
    }

    public function cookies(): HasMany
    {
        return $this->hasMany(CookieScanCookie::class);
    }
}
