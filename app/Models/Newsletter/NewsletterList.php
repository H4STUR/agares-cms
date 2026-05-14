<?php

namespace App\Models\Newsletter;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class NewsletterList extends Model
{
    protected $table = 'newsletter_lists';

    protected $guarded = [];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $list) {
            if (empty($list->slug) && !empty($list->name)) {
                $list->slug = Str::slug($list->name);
            }
        });
    }

    public function subscribers(): BelongsToMany
    {
        return $this->belongsToMany(
            NewsletterSubscriber::class,
            'newsletter_list_subscriber',
            'newsletter_list_id',
            'newsletter_subscriber_id'
        )->withTimestamps();
    }

    public function activeSubscribers(): BelongsToMany
    {
        return $this->subscribers()->where('status', NewsletterSubscriber::STATUS_ACTIVE);
    }

    /* ---------- Scopes ---------- */

    public function scopeDefault(Builder $q): Builder
    {
        return $q->where('is_default', true);
    }

    public static function defaultList(): ?self
    {
        return static::query()->where('is_default', true)->first();
    }
}
