<?php

namespace Tests\Concerns;

use App\Models\Ecommerce\PaymentProvider;
use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\Setting as EcommerceSetting;
use App\Models\Setting;
use App\Models\Site;
use App\Models\User;
use Spatie\Permission\Models\Permission;

trait WithEcommerce
{
    protected function setUp(): void
    {
        parent::setUp();
        // Clear ecommerce feature-flag cache so stale values from previous tests don't leak
        cache()->forget('setting.bool.enable_ecommerce');
        cache()->forget('setting.value.enable_ecommerce');
        cache()->forget('settings.all.kv');
    }

    protected function enableEcommerce(): void
    {
        Setting::create([
            'key'   => 'enable_ecommerce',
            'value' => '1',
            'type'  => 'boolean',
        ]);
    }

    protected function setupShopSite(string $slug = 'test-shop'): Site
    {
        EcommerceSetting::create(['key' => 'shop_url', 'value' => $slug]);

        return Site::factory()->create(['slug' => $slug]);
    }

    protected function setupCodProvider(): PaymentProvider
    {
        return PaymentProvider::factory()->cod()->create();
    }

    protected function adminUser(): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $permission = Permission::firstOrCreate(['name' => 'view admin panel', 'guard_name' => 'web']);
        $user->givePermissionTo($permission);

        return $user;
    }

    protected function validCheckoutData(string $driver = 'cod'): array
    {
        return [
            'first_name'     => 'John',
            'last_name'      => 'Doe',
            'email'          => 'john@example.com',
            'phone'          => '+48123456789',
            'address'        => '123 Main Street',
            'city'           => 'Warsaw',
            'postal_code'    => '00-001',
            'country'        => 'Poland',
            'payment_method' => $driver,
        ];
    }
}
