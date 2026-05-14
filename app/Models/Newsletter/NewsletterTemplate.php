<?php

namespace App\Models\Newsletter;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class NewsletterTemplate extends Model
{
    protected $table = 'newsletter_templates';

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $template) {
            if (empty($template->created_by)) {
                $template->created_by = Auth::id();
            }
            $template->updated_by = Auth::id();
        });

        static::updating(function (self $template) {
            $template->updated_by = Auth::id();
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(NewsletterCampaign::class, 'template_id');
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }
}
