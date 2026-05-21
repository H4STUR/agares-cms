<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'site_id', // add if you will mass-assign, or omit if always created via $site->categories()->create()
        'name',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'description',
        'template',
        'default_article_template',
        'created_by',
        'updated_by'
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'category_article');
    }

    public function getSlugAttribute()
    {
        return Str::slug($this->name);
    }

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
}
