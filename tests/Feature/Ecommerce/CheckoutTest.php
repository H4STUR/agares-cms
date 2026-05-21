<?php

namespace Tests\Feature\Ecommerce;

use App\Mail\Ecommerce\OrderConfirmed;
use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\OrderItem;
use App\Models\Ecommerce\Payment;
use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\Setting as EcommerceSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\WithEcommerce;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase, WithEcommerce;

    // -------------------------------------------------------------------------
    // GET /shop/checkout
    // -------------------------------------------------------------------------

    public function test_guest_is_redirected_to_login_when_guest_checkout_disabled(): void
    {
        $this->enableEcommerce();
        EcommerceSetting::create(['key' => 'guest_checkout', 'value' => '0']);

        $this->get(route('shop.checkout'))
            ->assertRedirect(route('login'));
    }

    public function test_empty_cart_redirects_to_cart_page(): void
    {
        $this->enableEcommerce();
        $this->setupShopSite();
        EcommerceSetting::create(['key' => 'guest_checkout', 'value' => '1']);

        $this->get(route('shop.checkout'))
            ->assertRedirect(route('shop.cart'));
    }

    // -------------------------------------------------------------------------
    // POST /shop/checkout — COD order creation
    // -------------------------------------------------------------------------

    public function test_cod_checkout_creates_order_with_correct_totals(): void
    {
        Mail::fake();
        $this->enableEcommerce();
        $site     = $this->setupShopSite();
        $provider = $this->setupCodProvider();
        $product  = Product::factory()->create(['base_price' => 50.00]);
        $user     = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 2]])
            ->post(route('shop.checkout.store'), $this->validCheckoutData())
            ->assertRedirect();

        $order = Order::latest()->first();

        $this->assertNotNull($order);
        $this->assertEquals('processing', $order->status);
        $this->assertEquals(100.00, (float) $order->grand_total);
        $this->assertEquals(100.00, (float) $order->subtotal);
        $this->assertEquals($site->id, $order->site_id);
        $this->assertEquals($user->id, $order->user_id);
    }

    public function test_cod_checkout_creates_order_items(): void
    {
        Mail::fake();
        $this->enableEcommerce();
        $this->setupShopSite();
        $this->setupCodProvider();
        $product = Product::factory()->create(['base_price' => 30.00, 'name' => 'Test Widget']);
        $user    = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 3]])
            ->post(route('shop.checkout.store'), $this->validCheckoutData());

        $order = Order::latest()->first();
        $item  = $order->items()->first();

        $this->assertNotNull($item);
        $this->assertEquals($product->id, $item->product_id);
        $this->assertEquals(3, $item->qty);
        $this->assertEquals(30.00, (float) $item->unit_price);
        $this->assertEquals(90.00, (float) $item->total);
    }

    public function test_cod_checkout_creates_status_history_entry(): void
    {
        Mail::fake();
        $this->enableEcommerce();
        $this->setupShopSite();
        $this->setupCodProvider();
        $product = Product::factory()->create(['base_price' => 20.00]);
        $user    = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 1]])
            ->post(route('shop.checkout.store'), $this->validCheckoutData());

        $order   = Order::latest()->first();
        $history = $order->statusHistory()->first();

        $this->assertNotNull($history);
        $this->assertNull($history->from_status);
        $this->assertEquals('processing', $history->to_status);
    }

    public function test_cod_checkout_creates_payment_record(): void
    {
        Mail::fake();
        $this->enableEcommerce();
        $this->setupShopSite();
        $provider = $this->setupCodProvider();
        $product  = Product::factory()->create(['base_price' => 40.00]);
        $user     = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 1]])
            ->post(route('shop.checkout.store'), $this->validCheckoutData());

        $order   = Order::latest()->first();
        $payment = $order->payments()->first();

        $this->assertNotNull($payment);
        $this->assertEquals('pending', $payment->status);
        $this->assertEquals($provider->id, $payment->provider_id);
        $this->assertEquals(40.00, (float) $payment->amount);
    }

    public function test_checkout_clears_cart_after_order(): void
    {
        Mail::fake();
        $this->enableEcommerce();
        $this->setupShopSite();
        $this->setupCodProvider();
        $product = Product::factory()->create(['base_price' => 25.00]);
        $user    = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 2]])
            ->post(route('shop.checkout.store'), $this->validCheckoutData());

        $this->assertNull(session('cart'));
    }

    public function test_checkout_redirects_to_confirmation_after_cod(): void
    {
        Mail::fake();
        $this->enableEcommerce();
        $this->setupShopSite();
        $this->setupCodProvider();
        $product = Product::factory()->create(['base_price' => 10.00]);
        $user    = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 1]])
            ->post(route('shop.checkout.store'), $this->validCheckoutData())
            ->assertRedirect();

        $order = Order::latest()->first();
        $this->assertStringContainsString($order->order_number, session()->all()['_previous']['url'] ?? '');
    }

    public function test_checkout_sends_confirmation_email_to_customer(): void
    {
        Mail::fake();
        $this->enableEcommerce();
        $this->setupShopSite();
        $this->setupCodProvider();
        $product = Product::factory()->create(['base_price' => 15.00]);
        $user    = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 1]])
            ->post(route('shop.checkout.store'), $this->validCheckoutData());

        Mail::assertSent(OrderConfirmed::class, fn ($mail) => $mail->hasTo('john@example.com'));
    }

    public function test_checkout_uses_sale_price_when_available(): void
    {
        Mail::fake();
        $this->enableEcommerce();
        $this->setupShopSite();
        $this->setupCodProvider();
        $product = Product::factory()->withSalePrice(20.00)->create(['base_price' => 50.00]);
        $user    = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 2]])
            ->post(route('shop.checkout.store'), $this->validCheckoutData());

        $order = Order::latest()->first();
        $this->assertEquals(40.00, (float) $order->grand_total);
    }

    public function test_unavailable_payment_method_returns_error(): void
    {
        $this->enableEcommerce();
        $this->setupShopSite();
        $product = Product::factory()->create(['base_price' => 10.00]);
        $user    = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 1]])
            ->post(route('shop.checkout.store'), $this->validCheckoutData('nonexistent'))
            ->assertSessionHasErrors('payment_method');
    }

    public function test_empty_cart_on_post_redirects_to_cart(): void
    {
        $this->enableEcommerce();
        $this->setupShopSite();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('shop.checkout.store'), $this->validCheckoutData())
            ->assertRedirect(route('shop.cart'));
    }

    // -------------------------------------------------------------------------
    // Guest checkout
    // -------------------------------------------------------------------------

    public function test_guest_can_checkout_when_guest_checkout_enabled(): void
    {
        Mail::fake();
        $this->enableEcommerce();
        $this->setupShopSite();
        $this->setupCodProvider();
        EcommerceSetting::create(['key' => 'guest_checkout', 'value' => '1']);
        $product = Product::factory()->create(['base_price' => 10.00]);

        $this->withSession(['cart' => [$product->id => 1]])
            ->post(route('shop.checkout.store'), $this->validCheckoutData())
            ->assertRedirect();

        $this->assertDatabaseCount('ecommerce_orders', 1);
    }

    // -------------------------------------------------------------------------
    // Registration at checkout
    // -------------------------------------------------------------------------

    public function test_checkout_with_registration_creates_user(): void
    {
        Mail::fake();
        $this->enableEcommerce();
        $this->setupShopSite();
        $this->setupCodProvider();
        EcommerceSetting::create(['key' => 'allow_register_at_checkout', 'value' => '1']);
        $product = Product::factory()->create(['base_price' => 10.00]);

        $data = array_merge($this->validCheckoutData(), [
            'create_account'        => '1',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $this->withSession(['cart' => [$product->id => 1]])
            ->post(route('shop.checkout.store'), $data)
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);

        $order = Order::latest()->first();
        $user  = \App\Models\User::where('email', 'john@example.com')->first();
        $this->assertEquals($user->id, $order->user_id);
    }

    // -------------------------------------------------------------------------
    // GET /shop/order/{orderNumber}/confirmation
    // -------------------------------------------------------------------------

    public function test_confirmation_accessible_via_session_flag(): void
    {
        $this->enableEcommerce();
        $order = Order::factory()->create();

        $this->withSession(['confirmed_order_' . $order->order_number => true])
            ->get(route('shop.order.confirmation', $order->order_number))
            ->assertOk();
    }

    public function test_confirmation_accessible_to_order_owner(): void
    {
        $this->enableEcommerce();
        $user  = User::factory()->create();
        $order = Order::factory()->forUser($user->id)->create();

        $this->actingAs($user)
            ->get(route('shop.order.confirmation', $order->order_number))
            ->assertOk();
    }

    public function test_confirmation_forbidden_for_unauthorized_user(): void
    {
        $this->enableEcommerce();
        $order = Order::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($other)
            ->get(route('shop.order.confirmation', $order->order_number))
            ->assertForbidden();
    }

    public function test_confirmation_returns_404_for_unknown_order(): void
    {
        $this->enableEcommerce();

        $this->withSession(['confirmed_order_NONEXISTENT' => true])
            ->get(route('shop.order.confirmation', 'NONEXISTENT'))
            ->assertNotFound();
    }
}
