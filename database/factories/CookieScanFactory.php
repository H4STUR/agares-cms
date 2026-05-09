<?php

namespace Database\Factories;

use App\Models\CookieScan;
use Illuminate\Database\Eloquent\Factories\Factory;

class CookieScanFactory extends Factory
{
    protected $model = CookieScan::class;

    public function definition(): array
    {
        return [
            'status'     => 'completed',
            'domain'     => 'demo.agares.co.uk',
            'url'        => 'https://demo.agares.co.uk',
            'scanned_at' => now()->subMinutes(5),

            'total'       => 2,
            'first_party' => 2,
            'third_party' => 0,
            'secure'      => 0,
            'http_only'   => 1,

            'essential'  => 1,
            'functional' => 1,
            'analytics'  => 0,
            'marketing'  => 0,

            'privacy_score' => 80,
            'privacy_grade' => 'B',

            'saas_scan_id' => null,
            'error_message' => null,
            'created_by'   => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => ['status' => 'completed']);
    }

    public function scanning(): static
    {
        return $this->state(fn () => [
            'status'     => 'scanning',
            'scanned_at' => now(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status'     => 'pending',
            'scanned_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'status'        => 'failed',
            'error_message' => 'Scan failed.',
        ]);
    }

    public function forDomain(string $domain): static
    {
        return $this->state(fn () => [
            'domain' => $domain,
            'url'    => 'https://' . $domain,
        ]);
    }
}
