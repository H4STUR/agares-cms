<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            OwnerUserSeeder::class,

            SettingsTableSeeder::class,

            InputFieldSeeder::class,
            InputTemplateSeeder::class,

            MenusTableSeeder::class,
            SitesTableSeeder::class,
            CustomCodeSeeder::class,
            CookieConsentSettingsSeeder::class,

            EcommerceSettingsSeeder::class,
            EcommercePaymentProvidersSeeder::class,
            EcommerceShippingMethodsSeeder::class,
        ]);

    }
}
