<?php

namespace App\Models\Ecommerce;

use Database\Factories\Ecommerce\CouponFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Coupon extends Model
{
    use HasFactory;

    protected static function newFactory(): CouponFactory
    {
        return CouponFactory::new();
    }
    protected $table = 'ecommerce_coupons';

    protected $guarded = [];

    protected $casts = [
        'value'                 => 'decimal:2',
        'min_order_value'       => 'decimal:2',
        'enabled'               => 'boolean',
        'starts_at'             => 'datetime',
        'ends_at'               => 'datetime',
    ];

    public function redemptions(): HasMany
    {
        return $this->hasMany(CouponRedemption::class, 'coupon_id');
    }

    /**
     * Check if this coupon is currently usable for a given order total and user.
     * Returns null on success, or a string error message.
     */
    public function validate(float $orderTotal, ?int $userId = null): ?string
    {
        if (! $this->enabled) {
            return 'This coupon is disabled.';
        }

        $now = Carbon::now();

        if ($this->starts_at && $now->lt($this->starts_at)) {
            return 'This coupon is not active yet.';
        }

        if ($this->ends_at && $now->gt($this->ends_at)) {
            return 'This coupon has expired.';
        }

        if ($this->min_order_value && $orderTotal < $this->min_order_value) {
            return "Minimum order value of {$this->min_order_value} required.";
        }

        if ($this->max_uses !== null) {
            $used = $this->redemptions()->count();
            if ($used >= $this->max_uses) {
                return 'This coupon has reached its usage limit.';
            }
        }

        if ($this->max_uses_per_customer !== null && $userId) {
            $used = $this->redemptions()->where('user_id', $userId)->count();
            if ($used >= $this->max_uses_per_customer) {
                return 'You have already used this coupon the maximum number of times.';
            }
        }

        return null;
    }

    /**
     * Calculate the discount amount for a given order total.
     */
    public function discountAmount(float $orderTotal): float
    {
        return match ($this->type) {
            'percent'      => round($orderTotal * ((float) $this->value / 100), 2),
            'fixed'        => min((float) $this->value, $orderTotal),
            'free_shipping' => 0.0,
            default        => 0.0,
        };
    }
}
