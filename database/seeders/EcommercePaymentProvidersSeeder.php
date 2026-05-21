<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EcommercePaymentProvidersSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $providers = [
            ['driver' => 'stripe', 'enabled' => 0],
            ['driver' => 'payu', 'enabled' => 0],
            ['driver' => 'p24', 'enabled' => 0],
            ['driver' => 'paypal', 'enabled' => 0],
            ['driver' => 'manual', 'enabled' => 1], // optional: allow manual/bank transfer from day 1
        ];

        foreach ($providers as $p) {
            DB::table('ecommerce_payment_providers')->updateOrInsert(
                ['site_id' => null, 'driver' => $p['driver']],
                [
                    'site_id' => null,
                    'driver' => $p['driver'],
                    'enabled' => $p['enabled'],
                    'config' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
