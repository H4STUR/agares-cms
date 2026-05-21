<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class InputInstance extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_type',
        'owner_id',
        'input_field_id',
        'label',
        'variable',
        'value',
        'description',
        'sort_order',
        'is_default',
        'is_locked',
        'created_by',
        'updated_by',
        'gallery_id',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_locked'  => 'boolean',
    ];

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(InputField::class, 'input_field_id');
    }

    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class, 'gallery_id');
    }

    /**
     * Media inside the gallery connected to this input instance.
     * NOTE: this uses gallery_id (not the InputInstance id).
     */
    public function galleryMedia(): BelongsToMany
    {
        return $this->belongsToMany(
                Media::class,
                'gallery_media',   // <- pivot table name (adjust if yours differs!)
                'gallery_id',      // pivot column pointing to gallery
                'media_id',        // pivot column pointing to media
                'gallery_id',      // parent key on THIS model
                'id'               // related key on Media
            )
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    /**
     * Files attached directly to this input instance (not gallery).
     */
    public function files(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'input_instance_media', 'input_instance_id', 'media_id')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    public function getFormIdAttribute(): ?int
    {
        $v = $this->value;
        if (!$v) return null;

        $arr = is_array($v) ? $v : (is_string($v) ? json_decode($v, true) : null);
        if (!is_array($arr)) return null;

        return isset($arr['form_id']) ? (int)$arr['form_id'] : null;
    }

    public function faqItems()
    {
        return $this->hasMany(\App\Models\FaqItem::class)->orderBy('sort_order');
    }

    public function getValueAttribute($value)
    {
        // If there's a real value stored in DB and no media loaded, keep it
        // (fallback safety)
        $this->loadMissing(['field', 'files']);

        $type = $this->field?->field_type;

        // Only auto-resolve for IMAGE inputs
        if ($type === 'image') {
            // take first by pivot order if available
            $media = $this->files
                ->sortBy(fn ($m) => $m->pivot->sort_order ?? 999999)
                ->first();

            // If image exists in pivot, expose its path as "value"
            if ($media) {
                return $media->file_path; // relative path (good for asset())
            }

            // otherwise fall back to DB value (could be null)
            return $value;
        }

        // default behavior for all other field types
        return $value;
    }

    // Optional: super handy for blades
    public function getValueUrlAttribute(): ?string
    {
        $path = $this->value;
        return $path ? asset($path) : null;
    }

}
