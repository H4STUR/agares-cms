<?php

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Model;

class TaxRule extends Model
{
    protected $table = 'ecommerce_tax_rules';

    protected $guarded = [];

    protected $casts = [
        'rate' => 'decimal:2',
        'enabled' => 'boolean',
        'prices_include_tax' => 'boolean',
    ];
}
