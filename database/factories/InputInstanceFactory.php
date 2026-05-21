<?php

namespace Database\Factories;

use App\Models\InputInstance;
use App\Models\InputField;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InputInstanceFactory extends Factory
{
    protected $model = InputInstance::class;

    public function definition(): array
    {
        return [
            'owner_type' => Site::class,
            'owner_id' => Site::factory(),

            'input_field_id' => InputField::factory(),
            'label' => $this->faker->words(2, true),
            'variable' => $this->faker->unique()->slug(2),
            'value' => $this->faker->sentence(),
            'description' => $this->faker->optional()->sentence(),

            'sort_order' => 0,
            'is_default' => false,
            'is_locked' => false,

            'created_by' => User::factory(),
            'updated_by' => User::factory(),

            'gallery_id' => null,
        ];
    }

    public function forSite(Site $site): static
    {
        return $this->state(fn () => [
            'owner_type' => Site::class,
            'owner_id' => $site->id,
        ]);
    }

    public function locked(): static
    {
        return $this->state(fn () => ['is_locked' => true]);
    }

    public function default(): static
    {
        return $this->state(fn () => ['is_default' => true]);
    }
}
