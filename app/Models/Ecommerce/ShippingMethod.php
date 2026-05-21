<?php

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
{
    protected $table = 'ecommerce_shipping_methods';

    protected $guarded = [];

    protected $casts = [
        'enabled' => 'boolean',
        'price' => 'decimal:2',
        'config' => 'array',
    ];
}
