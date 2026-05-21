<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EcommerceSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $rows = [
            // General
            [
                'site_id' => null,
                'key' => 'currency',
                'value' => 'PLN',
                'category' => 'general',
                'type' => 'string',
                'description' => 'Default shop currency (ISO 4217)',
                'created_by' => null,
                'updated_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_id' => null,
                'key' => 'prices_include_tax',
                'value' => '1',
                'category' => 'tax',
                'type' => 'boolean',
                'description' => 'Whether product prices include tax',
                'created_by' => null,
                'updated_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_id' => null,
                'key' => 'checkout_require_phone',
                'value' => '1',
                'category' => 'checkout',
                'type' => 'boolean',
                'description' => 'Require phone number during checkout',
                'created_by' => null,
                'updated_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_id' => null,
                'key' => 'order_number_prefix',
                'value' => 'AG-',
                'category' => 'orders',
                'type' => 'string',
                'description' => 'Prefix for generated order numbers',
                'created_by' => null,
                'updated_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // URLs / pages (keep consistent with your idea, these can later become site_id dropdowns)
            [
                'site_id' => null,
                'key' => 'shop_url',
                'value' => 'shop',
                'category' => 'routing',
                'type' => 'string',
                'description' => 'URL of the shop page (path/slug)',
                'created_by' => null,
                'updated_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_id' => null,
                'key' => 'cart_url',
                'value' => 'cart',
                'category' => 'routing',
                'type' => 'string',
                'description' => 'URL of the cart page (path/slug)',
                'created_by' => null,
                'updated_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_id' => null,
                'key' => 'checkout_url',
                'value' => 'checkout',
                'category' => 'routing',
                'type' => 'string',
                'description' => 'URL of the checkout page (path/slug)',
                'created_by' => null,
                'updated_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_id' => null,
                'key' => 'order_tracking_url',
                'value' => 'order-tracking',
                'category' => 'routing',
                'type' => 'string',
                'description' => 'URL of the order tracking page (path/slug)',
                'created_by' => null,
                'updated_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Currency (multi-currency groundwork — no conversion yet)
            [
                'site_id' => null,
                'key' => 'default_currency',
                'value' => 'PLN',
                'category' => 'general',
                'type' => 'string',
                'description' => 'Base currency shown to customers (ISO 4217)',
                'created_by' => null,
                'updated_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_id' => null,
                'key' => 'available_currencies',
                'value' => 'PLN,EUR,USD',
                'category' => 'general',
                'type' => 'string',
                'description' => 'Comma-separated list of currencies customers can select',
                'created_by' => null,
                'updated_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Checkout
            [
                'site_id' => null,
                'key' => 'guest_checkout',
                'value' => '1',
                'category' => 'checkout',
                'type' => 'boolean',
                'description' => 'Allow checkout without an account',
                'created_by' => null,
                'updated_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_id' => null,
                'key' => 'allow_register_at_checkout',
                'value' => '1',
                'category' => 'checkout',
                'type' => 'boolean',
                'description' => 'Show "create account" option during checkout',
                'created_by' => null,
                'updated_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Notifications
            [
                'site_id' => null,
                'key' => 'admin_email',
                'value' => '',
                'category' => 'notifications',
                'type' => 'string',
                'description' => 'Admin email address for new order notifications',
                'created_by' => null,
                'updated_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($rows as $row) {
            DB::table('ecommerce_settings')->updateOrInsert(
                ['site_id' => $row['site_id'], 'key' => $row['key']],
                $row
            );
        }
    }
}
