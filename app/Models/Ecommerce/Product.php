<?php

namespace App\Models\Ecommerce;

use Database\Factories\Ecommerce\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use SoftDeletes, HasFactory;

    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }

    protected $table = 'ecommerce_products';

    // ✅ safer than guarded=[]
    protected $fillable = [
        'status',
        'product_type',
        'name',
        'slug',

        'sku',
        'stock',
        'manage_stock',
        'is_in_stock',

        'short_description',
        'description',

        'base_price',
        'sale_price',
        'sale_from',
        'sale_to',

        'meta_title',
        'meta_description',
        'meta_keywords',
        'canonical_url',

        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'sale_from' => 'datetime',
        'sale_to'   => 'datetime',

        'base_price' => 'decimal:2',
        'sale_price' => 'decimal:2',

        'stock' => 'integer',
        'manage_stock' => 'boolean',
        'is_in_stock' => 'boolean',
    ];

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }

    public function defaultVariant(): HasOne
    {
        return $this->hasOne(ProductVariant::class, 'product_id')->where('is_default', true);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'ecommerce_product_category', 'product_id', 'category_id')
            ->withPivot(['sort_order']);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'ecommerce_product_tag', 'product_id', 'tag_id');
    }
}
