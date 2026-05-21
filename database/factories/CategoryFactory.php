<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'name' => ucfirst($this->faker->unique()->words(2, true)),
            'meta_title' => $this->faker->optional()->sentence(6),
            'meta_description' => $this->faker->optional()->sentence(12),
            'meta_keywords' => $this->faker->optional()->words(6, true),
            'description' => $this->faker->optional()->paragraph(),
            'template' => $this->faker->optional()->randomElement(['default', 'category']),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }

    public function forSite(Site $site): static
    {
        return $this->state(fn () => ['site_id' => $site->id]);
    }
}
