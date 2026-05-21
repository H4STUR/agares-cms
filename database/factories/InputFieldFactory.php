<?php

namespace Database\Factories;

use App\Models\InputField;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InputFieldFactory extends Factory
{
    protected $model = InputField::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement([
            'short_text', 'number', 'text_editor', 'textarea', 'code', 'file', 'gallery', 'image', 'contact_form'
        ]);

        return [
            'name' => ucfirst($this->faker->unique()->words(2, true)),
            'field_type' => $type,
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }

    public function type(string $fieldType): static
    {
        return $this->state(fn () => ['field_type' => $fieldType]);
    }
}
