<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InputTemplate extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'applies_to'];

    public function scope(): MorphTo
    {
        return $this->morphTo();
    }

    public function items(): HasMany
    {
        return $this->hasMany(InputTemplateItem::class)->orderBy('sort_order');
    }
}
