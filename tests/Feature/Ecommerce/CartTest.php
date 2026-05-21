<?php

namespace Tests\Feature\Ecommerce;

use App\Models\Ecommerce\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WithEcommerce;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase, WithEcommerce;

    // POST /shop/cart — add

    public function test_add_product_to_cart(): void
    {
        $this->enableEcommerce();
        $product = Product::factory()->create();

        $this->post(route('shop.cart.add'), ['product_id' => $product->id, 'quantity' => 2])
            ->assertRedirect();

        $this->assertEquals([$product->id => 2], session('cart'));
    }

    public function test_add_product_increments_existing_quantity(): void
    {
        $this->enableEcommerce();
        $product = Product::factory()->create();

        $this->withSession(['cart' => [$product->id => 3]])
            ->post(route('shop.cart.add'), ['product_id' => $product->id, 'quantity' => 2])
            ->assertRedirect();

        $this->assertEquals([$product->id => 5], session('cart'));
    }

    public function test_add_defaults_quantity_to_one(): void
    {
        $this->enableEcommerce();
        $product = Product::factory()->create();

        $this->post(route('shop.cart.add'), ['product_id' => $product->id])
            ->assertRedirect();

        $this->assertEquals([$product->id => 1], session('cart'));
    }

    public function test_add_nonexistent_product_returns_validation_error(): void
    {
        $this->enableEcommerce();

        $this->post(route('shop.cart.add'), ['product_id' => 99999])
            ->assertSessionHasErrors('product_id');
    }

    // PATCH /shop/cart/{productId} — update

    public function test_update_cart_quantity(): void
    {
        $this->enableEcommerce();
        $product = Product::factory()->create();

        $this->withSession(['cart' => [$product->id => 2]])
            ->patch(route('shop.cart.update', $product->id), ['quantity' => 5])
            ->assertRedirect();

        $this->assertEquals([$product->id => 5], session('cart'));
    }

    public function test_update_quantity_to_zero_removes_item(): void
    {
        $this->enableEcommerce();
        $product = Product::factory()->create();
        $other   = Product::factory()->create();

        $this->withSession(['cart' => [$product->id => 2, $other->id => 1]])
            ->patch(route('shop.cart.update', $product->id), ['quantity' => 0])
            ->assertRedirect();

        $cart = session('cart');
        $this->assertArrayNotHasKey($product->id, $cart);
        $this->assertArrayHasKey($other->id, $cart);
    }

    // DELETE /shop/cart/{productId} — remove

    public function test_remove_item_from_cart(): void
    {
        $this->enableEcommerce();
        $product = Product::factory()->create();
        $other   = Product::factory()->create();

        $this->withSession(['cart' => [$product->id => 1, $other->id => 3]])
            ->delete(route('shop.cart.remove', $product->id))
            ->assertRedirect();

        $cart = session('cart');
        $this->assertArrayNotHasKey($product->id, $cart);
        $this->assertArrayHasKey($other->id, $cart);
    }

    // DELETE /shop/cart — clear

    public function test_clear_cart_removes_all_items(): void
    {
        $this->enableEcommerce();
        $product = Product::factory()->create();

        $this->withSession(['cart' => [$product->id => 2]])
            ->delete(route('shop.cart.clear'))
            ->assertRedirect();

        $this->assertNull(session('cart'));
    }
}
