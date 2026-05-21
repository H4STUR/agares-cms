<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Support\Carbon;

class ApiKey extends Model
{
    protected $fillable = [
        'name',
        'key_hash',
        'site_id',
        'abilities',
        'expires_at',
        'revoked_at',
        'created_by',
        'last_used_at',
    ];

    protected $casts = [
        'abilities' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function isRevoked(): bool
    {
        return !is_null($this->revoked_at);
    }

    public function isExpired(): bool
    {
        return $this->expires_at instanceof Carbon && $this->expires_at->isPast();
    }

    public function can(string $ability): bool
    {
        $abilities = $this->abilities ?? [];
        return in_array('*', $abilities, true) || in_array($ability, $abilities, true);
    }
}
