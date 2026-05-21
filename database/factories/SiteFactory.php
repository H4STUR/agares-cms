<?php

namespace Database\Factories;

use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SiteFactory extends Factory
{
    protected $model = Site::class;

    public function definition(): array
    {
        $name = $this->faker->words(2, true);
        $slug = $this->faker->unique()->slug();

        return [
            'name' => $name,
            'slug' => $slug,
            'parent_id' => null,
            'menu_order' => 0,
            'title' => $this->faker->optional()->sentence(6),
            'description' => $this->faker->optional()->paragraph(),
            'keywords' => $this->faker->optional()->words(6, true),
            'template' => $this->faker->optional()->randomElement(['default', 'page', 'landing']),
            'privileges' => null, // casts to array; null is fine unless DB requires JSON
            'status' => Site::STATUS_PUBLISHED,
            'published_at' => now(),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => Site::STATUS_DRAFT,
            'published_at' => null,
        ]);
    }

    public function scheduled(\DateTimeInterface $when = null): static
    {
        return $this->state(fn () => [
            'status' => Site::STATUS_SCHEDULED,
            'published_at' => $when ? $when : now()->addDays(3),
        ]);
    }

    public function childOf(Site $parent): static
    {
        return $this->state(fn () => [
            'parent_id' => $parent->id,
        ]);
    }
}
