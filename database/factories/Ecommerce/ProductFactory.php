<?php

namespace Database\Factories\Ecommerce;

use App\Models\Ecommerce\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'status'       => 'published',
            'product_type' => 'simple',
            'name'         => fake()->words(3, true),
            'slug'         => fake()->unique()->slug(),
            'sku'          => fake()->unique()->lexify('SKU-????'),
            'stock'        => 100,
            'manage_stock' => false,
            'is_in_stock'  => true,
            'base_price'   => fake()->randomFloat(2, 10, 200),
            'sale_price'   => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(['status' => 'draft']);
    }

    public function archived(): static
    {
        return $this->state(['status' => 'archived']);
    }

    public function withSalePrice(float $price): static
    {
        return $this->state(['sale_price' => $price]);
    }
}
