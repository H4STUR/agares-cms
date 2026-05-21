<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'title' => ucfirst($this->faker->sentence(5)),
            'meta_title' => $this->faker->optional()->text(60),
            'meta_description' => $this->faker->optional()->sentence(14),
            'meta_keywords' => $this->faker->optional()->words(8, true),
            'description' => $this->faker->optional()->paragraph(),
            'template' => $this->faker->optional()->randomElement(['default', 'article']),
            'content' => $this->faker->paragraphs(4, true),
            'status' => Article::STATUS_PUBLISHED,
            'published_at' => now(),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => Article::STATUS_DRAFT,
            'published_at' => null,
        ]);
    }

    public function scheduled(\DateTimeInterface $when = null): static
    {
        return $this->state(fn () => [
            'status' => Article::STATUS_SCHEDULED,
            'published_at' => $when ? $when : now()->addDays(2),
        ]);
    }

    public function forSite(Site $site): static
    {
        return $this->state(fn () => ['site_id' => $site->id]);
    }
}
