<?php

namespace Tests\Unit\Ecommerce;

use App\Models\Ecommerce\Coupon;
use Database\Factories\Ecommerce\CouponFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // validate()
    // -------------------------------------------------------------------------

    public function test_disabled_coupon_fails_validation(): void
    {
        $coupon = Coupon::factory()->disabled()->create();

        $this->assertNotNull($coupon->validate(100));
    }

    public function test_coupon_not_yet_active_fails_validation(): void
    {
        $coupon = Coupon::factory()->notStarted()->create();

        $this->assertNotNull($coupon->validate(100));
    }

    public function test_expired_coupon_fails_validation(): void
    {
        $coupon = Coupon::factory()->expired()->create();

        $this->assertNotNull($coupon->validate(100));
    }

    public function test_order_below_minimum_value_fails_validation(): void
    {
        $coupon = Coupon::factory()->create(['min_order_value' => 200]);

        $this->assertNotNull($coupon->validate(150));
    }

    public function test_order_at_minimum_value_passes_validation(): void
    {
        $coupon = Coupon::factory()->create(['min_order_value' => 100]);

        $this->assertNull($coupon->validate(100));
    }

    public function test_coupon_at_max_uses_fails_validation(): void
    {
        // max_uses = 0 means 0 allowed uses; count() (0) >= max_uses (0) → error
        $coupon = Coupon::factory()->create(['max_uses' => 0]);

        $this->assertNotNull($coupon->validate(100));
    }

    public function test_per_customer_limit_fails_validation(): void
    {
        // max_uses_per_customer = 0 means user has already exhausted their allowance
        $coupon = Coupon::factory()->create(['max_uses_per_customer' => 0]);

        $this->assertNotNull($coupon->validate(100, userId: 42));
    }

    public function test_per_customer_limit_not_checked_for_guests(): void
    {
        $coupon = Coupon::factory()->create(['max_uses_per_customer' => 0]);

        // No userId passed → skip per-customer check
        $this->assertNull($coupon->validate(100, userId: null));
    }

    public function test_fully_valid_coupon_returns_null(): void
    {
        $coupon = Coupon::factory()->create([
            'min_order_value' => 50,
            'max_uses'        => 10,
        ]);

        $this->assertNull($coupon->validate(100, userId: 1));
    }

    // -------------------------------------------------------------------------
    // discountAmount()
    // -------------------------------------------------------------------------

    public function test_percent_discount_calculation(): void
    {
        $coupon = Coupon::factory()->percent(20)->create();

        $this->assertEquals(20.00, $coupon->discountAmount(100));
    }

    public function test_percent_discount_rounds_to_two_decimals(): void
    {
        $coupon = Coupon::factory()->percent(10)->create();

        $this->assertEquals(3.33, $coupon->discountAmount(33.33));
    }

    public function test_fixed_discount_within_order_total(): void
    {
        $coupon = Coupon::factory()->fixed(15)->create();

        $this->assertEquals(15.00, $coupon->discountAmount(100));
    }

    public function test_fixed_discount_capped_at_order_total(): void
    {
        $coupon = Coupon::factory()->fixed(150)->create();

        $this->assertEquals(50.00, $coupon->discountAmount(50));
    }

    public function test_free_shipping_discount_is_always_zero(): void
    {
        $coupon = Coupon::factory()->freeShipping()->create();

        $this->assertEquals(0.0, $coupon->discountAmount(200));
    }
}
