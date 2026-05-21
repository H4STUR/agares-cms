<?php

namespace App\Models\Ecommerce;

use Database\Factories\Ecommerce\PaymentProviderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentProvider extends Model
{
    use HasFactory;

    protected static function newFactory(): PaymentProviderFactory
    {
        return PaymentProviderFactory::new();
    }
    protected $table = 'ecommerce_payment_providers';

    protected $guarded = [];

    protected $casts = [
        'enabled' => 'boolean',
        'config'  => 'array',
    ];

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'provider_id');
    }
}
