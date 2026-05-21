<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Gallery extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'owner_type',
        'owner_id',
        'name',
        'variable',
        'sort_order',
    ];
    
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public function items()
    {
        return $this->hasMany(\App\Models\GalleryItem::class)->orderBy('sort_order');
    }

    public function media()
    {
        return $this->belongsToMany(\App\Models\Media::class, 'gallery_media')
                ->withPivot('sort_order')
                ->orderBy('gallery_media.sort_order');
    }
}
