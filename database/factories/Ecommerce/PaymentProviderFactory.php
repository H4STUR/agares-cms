<?php

namespace Database\Factories\Ecommerce;

use App\Models\Ecommerce\PaymentProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentProviderFactory extends Factory
{
    protected $model = PaymentProvider::class;

    public function definition(): array
    {
        return [
            'site_id' => null,
            'driver'  => 'cod',
            'enabled' => true,
            'config'  => [],
        ];
    }

    public function cod(): static
    {
        return $this->state(['driver' => 'cod', 'enabled' => true]);
    }

    public function stripe(): static
    {
        return $this->state(['driver' => 'stripe', 'enabled' => true]);
    }

    public function disabled(): static
    {
        return $this->state(['enabled' => false]);
    }
}
