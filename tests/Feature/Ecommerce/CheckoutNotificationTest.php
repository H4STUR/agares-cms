<?php

namespace Tests\Feature\Ecommerce;

use App\Models\AdminNotification;
use App\Models\Ecommerce\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\WithEcommerce;
use Tests\TestCase;

class CheckoutNotificationTest extends TestCase
{
    use RefreshDatabase, WithEcommerce;

    private function placeOrder(array $cartItems, array $overrides = []): void
    {
        Mail::fake();
        $this->enableEcommerce();
        $this->setupShopSite();
        $this->setupCodProvider();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['cart' => $cartItems])
            ->post(route('shop.checkout.store'), array_merge($this->validCheckoutData('cod'), $overrides))
            ->assertRedirect();
    }

    // -------------------------------------------------------------------------

    public function test_cod_checkout_creates_new_order_notification(): void
    {
        $product = Product::factory()->create(['base_price' => 49.99]);

        $this->placeOrder([$product->id => 2]);

        $this->assertDatabaseHas('admin_notifications', [
            'type' => 'new_order',
            'icon' => 'shopping_cart',
        ]);
    }

    public function test_notification_message_contains_order_number_and_customer_name(): void
    {
        $product = Product::factory()->create(['base_price' => 100.00]);

        $this->placeOrder([$product->id => 1]);

        $notification = AdminNotification::where('type', 'new_order')->firstOrFail();

        $this->assertStringContainsString('John Doe', $notification->message);
        $this->assertStringContainsString('#', $notification->message);
    }

    public function test_notification_url_points_to_admin_order_page(): void
    {
        $product = Product::factory()->create(['base_price' => 50.00]);

        $this->placeOrder([$product->id => 1]);

        $notification = AdminNotification::where('type', 'new_order')->firstOrFail();

        $this->assertNotNull($notification->url);
        $this->assertStringContainsString('/admin/ecommerce/orders/', $notification->url);
    }

    public function test_notification_is_unread_when_created(): void
    {
        $product = Product::factory()->create(['base_price' => 25.00]);

        $this->placeOrder([$product->id => 1]);

        $notification = AdminNotification::where('type', 'new_order')->firstOrFail();

        $this->assertNull($notification->read_at);
    }

    public function test_each_order_creates_a_separate_notification(): void
    {
        Mail::fake();
        $this->enableEcommerce();
        $this->setupShopSite();
        $this->setupCodProvider();
        $product = Product::factory()->create(['base_price' => 10.00]);
        $user    = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 1]])
            ->post(route('shop.checkout.store'), $this->validCheckoutData('cod'));

        $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 1]])
            ->post(route('shop.checkout.store'), array_merge(
                $this->validCheckoutData('cod'),
                ['email' => 'another@example.com']
            ));

        $this->assertCount(2, AdminNotification::where('type', 'new_order')->get());
    }
}
