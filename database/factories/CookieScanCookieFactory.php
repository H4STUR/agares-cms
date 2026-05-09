<?php

namespace Database\Factories;

use App\Models\CookieScan;
use App\Models\CookieScanCookie;
use Illuminate\Database\Eloquent\Factories\Factory;

class CookieScanCookieFactory extends Factory
{
    protected $model = CookieScanCookie::class;

    public function definition(): array
    {
        return [
            'cookie_scan_id' => CookieScan::factory(),
            'name'           => $this->faker->slug(2),
            'value'          => null,
            'domain'         => 'demo.agares.co.uk',
            'path'           => '/',
            'expires'        => null,
            'expires_timestamp' => null,
            'size'           => 0,
            'http_only'      => true,
            'secure'         => false,
            'same_site'      => 'Lax',
            'session'        => false,
            'type'           => 'functional',
            'is_first_party' => true,
            'description'    => $this->faker->sentence(),
        ];
    }

    public function essential(): static
    {
        return $this->state(fn () => ['type' => 'essential']);
    }

    public function functional(): static
    {
        return $this->state(fn () => ['type' => 'functional']);
    }

    public function analytics(): static
    {
        return $this->state(fn () => ['type' => 'analytics']);
    }

    public function marketing(): static
    {
        return $this->state(fn () => ['type' => 'marketing']);
    }

    public function thirdParty(): static
    {
        return $this->state(fn () => ['is_first_party' => false]);
    }
}
