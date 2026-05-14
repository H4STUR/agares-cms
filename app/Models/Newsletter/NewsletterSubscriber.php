<?php

namespace App\Models\Newsletter;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class NewsletterSubscriber extends Model
{
    protected $table = 'newsletter_subscribers';

    protected $guarded = [];

    public const STATUS_PENDING      = 'pending';
    public const STATUS_ACTIVE       = 'active';
    public const STATUS_UNSUBSCRIBED = 'unsubscribed';
    public const STATUS_BOUNCED      = 'bounced';
    public const STATUS_COMPLAINED   = 'complained';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_ACTIVE,
        self::STATUS_UNSUBSCRIBED,
        self::STATUS_BOUNCED,
        self::STATUS_COMPLAINED,
    ];

    public const SOURCE_WEBSITE = 'website_form';
    public const SOURCE_ADMIN   = 'admin';
    public const SOURCE_IMPORT  = 'import';
    public const SOURCE_API     = 'api';

    public const SOURCES = [
        self::SOURCE_WEBSITE,
        self::SOURCE_ADMIN,
        self::SOURCE_IMPORT,
        self::SOURCE_API,
    ];

    protected $casts = [
        'subscribed_at'   => 'datetime',
        'confirmed_at'    => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $sub) {
            if (empty($sub->unsubscribe_token)) {
                $sub->unsubscribe_token = self::generateToken();
            }
            if (is_string($sub->email)) {
                $sub->email = strtolower(trim($sub->email));
            }
        });
    }

    public static function generateToken(): string
    {
        return Str::random(48);
    }

    public function lists(): BelongsToMany
    {
        return $this->belongsToMany(
            NewsletterList::class,
            'newsletter_list_subscriber',
            'newsletter_subscriber_id',
            'newsletter_list_id'
        )->withTimestamps();
    }

    /* ---------- Scopes ---------- */

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_ACTIVE);
    }

    public function scopePending(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_PENDING);
    }

    public function scopeUnsubscribed(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_UNSUBSCRIBED);
    }

    public function scopeStatus(Builder $q, ?string $status): Builder
    {
        return $status ? $q->where('status', $status) : $q;
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $q;
        }
        $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $term) . '%';
        return $q->where(function (Builder $q) use ($like) {
            $q->where('email', 'like', $like)
              ->orWhere('name', 'like', $like);
        });
    }
}
