<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Article extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_DRAFT     = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_PRIVATE   = 'private';

    protected $fillable = [
        'site_id',
        'title',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'description',
        'template',
        'content',

        'status',
        'published_at',

        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'deleted_at'   => 'datetime',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_article');
    }

    public function inputInstances(): MorphMany
    {
        return $this->morphMany(InputInstance::class, 'owner')->orderBy('sort_order');
    }

    public function galleries(): MorphMany
    {
        return $this->morphMany(Gallery::class, 'owner')->orderBy('sort_order');
    }

    public function scopePublic(Builder $q): Builder
    {
        return $q
            ->whereNull('deleted_at')
            ->whereIn('status', ['published', 'scheduled'])
            ->where(function ($q) {
                $q->where('status', 'published')
                ->orWhere(function ($q) {
                    $q->where('status', 'scheduled')
                        ->where('published_at', '<=', now());
                });
            });
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        // If relation is already eager-loaded → use it
        if ($this->relationLoaded('inputInstances')) {
            $inst = $this->inputInstances->firstWhere('variable', 'thumbnail');
        } else {
            // Fallback: load ONLY what we need (safe)
            $inst = $this->inputInstances()
                ->with(['files', 'field'])
                ->where('variable', 'thumbnail')
                ->first();
        }

        return $inst?->value ? asset($inst->value) : null;
    }
}
