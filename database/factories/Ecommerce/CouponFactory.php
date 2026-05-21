<?php

namespace Database\Factories\Ecommerce;

use App\Models\Ecommerce\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition(): array
    {
        return [
            'site_id'               => null,
            'code'                  => fake()->unique()->regexify('[A-Z]{8}'),
            'type'                  => 'percent',
            'value'                 => 10,
            'min_order_value'       => null,
            'max_uses'              => null,
            'max_uses_per_customer' => null,
            'starts_at'             => null,
            'ends_at'               => null,
            'enabled'               => true,
        ];
    }

    public function disabled(): static
    {
        return $this->state(['enabled' => false]);
    }

    public function fixed(float $amount): static
    {
        return $this->state(['type' => 'fixed', 'value' => $amount]);
    }

    public function percent(float $pct): static
    {
        return $this->state(['type' => 'percent', 'value' => $pct]);
    }

    public function freeShipping(): static
    {
        return $this->state(['type' => 'free_shipping', 'value' => 0]);
    }

    public function expired(): static
    {
        return $this->state(['ends_at' => now()->subDay()]);
    }

    public function notStarted(): static
    {
        return $this->state(['starts_at' => now()->addDay()]);
    }
}
