<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Site extends Model
{

    use HasFactory, SoftDeletes;

    public const STATUS_DRAFT     = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_PRIVATE   = 'private';

    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'menu_order',
        'title',
        'description',
        'keywords',
        'template',

        'is_redirect',
        'redirect_url',
        'redirect_type',
        'redirect_new_tab',

        'privileges',
        'status',
        'published_at',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'privileges'       => 'array',
        'published_at'     => 'datetime',
        'deleted_at'       => 'datetime',

        // Redirect / Forward page
        'is_redirect'      => 'boolean',
        'redirect_new_tab' => 'boolean',
        'redirect_type'    => 'integer',
    ];


    /** Frontend visibility rule */
    public function scopePublic(Builder $q): Builder
    {
        return $q
            ->whereNull('deleted_at')
            ->whereIn('status', [self::STATUS_PUBLISHED, self::STATUS_SCHEDULED])
            ->where(function ($q) {
                $q->where('status', self::STATUS_PUBLISHED)
                  ->orWhere('published_at', '<=', now());
            });
    }

    /** Make {site:slug} resolve ONLY public pages (draft/trashed => 404) */
    public function resolveRouteBinding($value, $field = null)
    {
        $field = $field ?: $this->getRouteKeyName();

        if (auth()->check() && auth()->user()->can('view unpublished content')) {
            return $this->newQuery()
                ->withTrashed()
                ->where($field, $value)
                ->firstOrFail();
        }

        return $this->newQuery()
            ->whereNull('deleted_at')
            ->where('status', 'published')
            ->where($field, $value)
            ->firstOrFail();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Site::class, 'parent_id');
    }

    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class, 'menu_site')
            ->withPivot('menu_order');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // NEW: one site owns many categories
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    // NEW: one site owns many articles
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    // Input system
    public function inputTemplates(): MorphMany
    {
        return $this->morphMany(InputTemplate::class, 'scope');
    }

    public function inputInstances(): MorphMany
    {
        return $this->morphMany(InputInstance::class, 'owner')->orderBy('sort_order');
    }

    public function galleries(): MorphMany
    {
        return $this->morphMany(Gallery::class, 'owner')->orderBy('sort_order');
    }

    public function isRedirect(): bool
    {
        return (bool) $this->is_redirect && !empty($this->redirect_url);
    }

    public function redirectStatusCode(): int
    {
        $code = (int) ($this->redirect_type ?: 302);
        return in_array($code, [301, 302], true) ? $code : 302;
    }

}
