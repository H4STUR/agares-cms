<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EcommerceShippingMethodsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $rows = [
            [
                'site_id' => null,
                'name' => 'Courier',
                'pricing_type' => 'flat',
                'price' => 15.00,
                'enabled' => 1,
                'config' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_id' => null,
                'name' => 'Local pickup',
                'pricing_type' => 'flat',
                'price' => 0.00,
                'enabled' => 1,
                'config' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($rows as $row) {
            DB::table('ecommerce_shipping_methods')->updateOrInsert(
                ['site_id' => $row['site_id'], 'name' => $row['name']],
                $row
            );
        }
    }
}
