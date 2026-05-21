<?php

namespace Database\Factories\Ecommerce;

use App\Models\Ecommerce\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'site_id'         => null,
            'user_id'         => null,
            'order_number'    => 'AG-' . date('Ymd') . str_pad(fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'status'          => 'processing',
            'payment_status'  => 'unpaid',
            'currency'        => 'PLN',
            'billing_address' => [
                'name'     => fake()->name(),
                'address1' => fake()->streetAddress(),
                'city'     => fake()->city(),
                'postcode' => fake()->postcode(),
                'country'  => 'PL',
                'email'    => fake()->safeEmail(),
                'phone'    => fake()->phoneNumber(),
            ],
            'subtotal'        => 100.00,
            'tax_total'       => 0,
            'shipping_total'  => 0,
            'discount_total'  => 0,
            'grand_total'     => 100.00,
            'placed_at'       => now(),
        ];
    }

    public function pendingPayment(): static
    {
        return $this->state(['status' => 'pending_payment', 'payment_status' => 'unpaid']);
    }

    public function completed(): static
    {
        return $this->state(['status' => 'completed', 'payment_status' => 'paid']);
    }

    public function cancelled(): static
    {
        return $this->state(['status' => 'cancelled']);
    }

    public function forUser(int $userId): static
    {
        return $this->state(['user_id' => $userId]);
    }
}
