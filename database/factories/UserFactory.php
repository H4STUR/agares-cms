<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password;

    public function definition(): array
    {
        return [
            'username' => fake()->unique()->userName(),
            'name' => fake()->firstName(),
            'surname' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'remember_token' => \Illuminate\Support\Str::random(10),

            // REMOVE THIS if column doesn't exist:
            // 'role_id' => null,

            'description' => fake()->sentence(),
            'avatar' => 'https://testingbot.com/free-online-tools/random-avatar/250?img=' . fake()->numberBetween(1, 10),
            'background_image' => null,
        ];
    }


    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }

    /**
     * Convenient helper for tests that need roles/permissions.
     */
    public function withRole(string $roleName = 'admin'): static
    {
        return $this->afterCreating(function (User $user) use ($roleName) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $user->role_id = $role->id;
            $user->save(); // triggers your saved() hook and syncRoles()
        });
    }
}
