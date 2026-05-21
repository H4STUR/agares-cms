<?php

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductVariant extends Model
{
    protected $table = 'ecommerce_product_variants';

    protected $fillable = [
        'product_id',
        'signature',
        'image_media_id',

        'is_default',
        'sku',
        'barcode',
        'price',
        'sale_price',
        'sale_from',
        'sale_to',
        'track_inventory',
        'stock_qty',
        'stock_status',
        'weight',
        'width',
        'height',
        'length',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'track_inventory' => 'boolean',
        'sale_from' => 'datetime',
        'sale_to'   => 'datetime',
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'stock_qty' => 'integer',
        'weight' => 'decimal:3',
        'width'  => 'decimal:3',
        'height' => 'decimal:3',
        'length' => 'decimal:3',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(
            AttributeValue::class,
            'ecommerce_variant_attribute_value',
            'variant_id',
            'attribute_value_id'
        );
    }

    // Optional: if you have App\Models\Media
    public function image(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Media::class, 'image_media_id');
    }
}
