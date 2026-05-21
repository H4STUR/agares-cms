<?php

namespace Tests\Feature\Ecommerce;

use App\Mail\Ecommerce\OrderStatusChanged;
use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\OrderStatusHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\WithEcommerce;
use Tests\TestCase;

class AdminOrderTest extends TestCase
{
    use RefreshDatabase, WithEcommerce;

    // -------------------------------------------------------------------------
    // GET /admin/ecommerce/orders
    // -------------------------------------------------------------------------

    public function test_admin_can_list_orders(): void
    {
        $this->enableEcommerce();
        Order::factory()->count(3)->create();

        $this->actingAs($this->adminUser())
            ->get(route('admin.ecommerce.orders.index'))
            ->assertOk();
    }

    public function test_order_list_is_paginated_to_25(): void
    {
        $this->enableEcommerce();
        Order::factory()->count(30)->create();

        $response = $this->actingAs($this->adminUser())
            ->get(route('admin.ecommerce.orders.index'));

        $response->assertOk();
        $orders = $response->viewData('orders');
        $this->assertCount(25, $orders);
    }

    public function test_order_list_can_be_filtered_by_status(): void
    {
        $this->enableEcommerce();
        Order::factory()->count(2)->pendingPayment()->create();
        Order::factory()->count(3)->completed()->create();

        $response = $this->actingAs($this->adminUser())
            ->get(route('admin.ecommerce.orders.index', ['status' => 'completed']));

        $response->assertOk();
        $orders = $response->viewData('orders');
        $this->assertCount(3, $orders);
    }

    public function test_unauthenticated_user_cannot_access_order_list(): void
    {
        $this->enableEcommerce();

        $this->get(route('admin.ecommerce.orders.index'))
            ->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // GET /admin/ecommerce/orders/{order}
    // -------------------------------------------------------------------------

    public function test_admin_can_view_order_detail(): void
    {
        $this->enableEcommerce();
        $order = Order::factory()->create();

        $this->actingAs($this->adminUser())
            ->get(route('admin.ecommerce.orders.show', $order))
            ->assertOk()
            ->assertViewHas('order');
    }

    public function test_order_detail_loads_related_data(): void
    {
        $this->enableEcommerce();
        $order = Order::factory()->create();

        $response = $this->actingAs($this->adminUser())
            ->get(route('admin.ecommerce.orders.show', $order));

        $response->assertOk();
        $viewOrder = $response->viewData('order');
        $this->assertTrue($viewOrder->relationLoaded('items'));
        $this->assertTrue($viewOrder->relationLoaded('statusHistory'));
        $this->assertTrue($viewOrder->relationLoaded('payments'));
    }

    // -------------------------------------------------------------------------
    // PATCH /admin/ecommerce/orders/{order}/status
    // -------------------------------------------------------------------------

    public function test_admin_can_update_order_status(): void
    {
        Mail::fake();
        $this->enableEcommerce();
        $order = Order::factory()->pendingPayment()->create();

        $this->actingAs($this->adminUser())
            ->patch(route('admin.ecommerce.orders.updateStatus', $order), [
                'status'  => 'processing',
                'comment' => 'Payment confirmed manually.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('ecommerce_orders', [
            'id'     => $order->id,
            'status' => 'processing',
        ]);
    }

    public function test_status_update_creates_history_entry(): void
    {
        Mail::fake();
        $this->enableEcommerce();
        $order = Order::factory()->pendingPayment()->create();
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->patch(route('admin.ecommerce.orders.updateStatus', $order), [
                'status'  => 'completed',
                'comment' => 'All done.',
            ]);

        $history = OrderStatusHistory::where('order_id', $order->id)->latest()->first();

        $this->assertNotNull($history);
        $this->assertEquals('pending_payment', $history->from_status);
        $this->assertEquals('completed', $history->to_status);
        $this->assertEquals('All done.', $history->comment);
        $this->assertEquals($admin->id, $history->changed_by);
    }

    public function test_status_update_sends_email_to_customer(): void
    {
        Mail::fake();
        $this->enableEcommerce();
        $order = Order::factory()->create([
            'billing_address' => [
                'name'    => 'Jane Doe',
                'email'   => 'customer@example.com',
                'address1'=> '1 Test St',
                'city'    => 'Warsaw',
                'postcode'=> '00-001',
                'country' => 'PL',
            ],
        ]);

        $this->actingAs($this->adminUser())
            ->patch(route('admin.ecommerce.orders.updateStatus', $order), [
                'status' => 'completed',
            ]);

        Mail::assertSent(OrderStatusChanged::class, fn ($mail) => $mail->hasTo('customer@example.com'));
    }

    public function test_status_update_rejects_invalid_status(): void
    {
        $this->enableEcommerce();
        $order = Order::factory()->create();

        $this->actingAs($this->adminUser())
            ->patch(route('admin.ecommerce.orders.updateStatus', $order), [
                'status' => 'invalid_status',
            ])
            ->assertSessionHasErrors('status');
    }

    public function test_all_allowed_statuses_are_accepted(): void
    {
        Mail::fake();
        $this->enableEcommerce();
        $admin = $this->adminUser();

        $statuses = ['pending_payment', 'processing', 'on_hold', 'completed', 'cancelled', 'refunded', 'failed'];

        foreach ($statuses as $status) {
            $order = Order::factory()->create();

            $this->actingAs($admin)
                ->patch(route('admin.ecommerce.orders.updateStatus', $order), compact('status'))
                ->assertSessionHasNoErrors();
        }
    }
}
