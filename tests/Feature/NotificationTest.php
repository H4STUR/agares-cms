<?php

namespace Tests\Feature;

use App\Models\AdminNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function adminUser(): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $permission = Permission::firstOrCreate(['name' => 'view admin panel', 'guard_name' => 'web']);
        $user->givePermissionTo($permission);

        return $user;
    }

    private function notification(array $attrs = []): AdminNotification
    {
        return AdminNotification::create(array_merge([
            'type'       => 'new_order',
            'title'      => 'New Order',
            'message'    => 'Order #AG-001',
            'icon'       => 'shopping_cart',
            'icon_color' => 'success',
            'url'        => '/admin/ecommerce/orders/1',
        ], $attrs));
    }

    // -------------------------------------------------------------------------
    // Auth guard
    // -------------------------------------------------------------------------

    public function test_guest_cannot_mark_notification_as_read(): void
    {
        $n = $this->notification();

        $this->patch(route('admin.notifications.read', $n))
            ->assertRedirect('/login');
    }

    public function test_guest_cannot_mark_all_as_read(): void
    {
        $this->patch(route('admin.notifications.readAll'))
            ->assertRedirect('/login');
    }

    public function test_guest_cannot_dismiss_notification(): void
    {
        $n = $this->notification();

        $this->delete(route('admin.notifications.dismiss', $n))
            ->assertRedirect('/login');
    }

    public function test_guest_cannot_dismiss_all(): void
    {
        $this->delete(route('admin.notifications.dismissAll'))
            ->assertRedirect('/login');
    }

    // -------------------------------------------------------------------------
    // Mark single as read
    // -------------------------------------------------------------------------

    public function test_mark_single_notification_as_read(): void
    {
        $admin = $this->adminUser();
        $n = $this->notification();

        $this->actingAs($admin)
            ->patch(route('admin.notifications.read', $n))
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertNotNull($n->fresh()->read_at);
    }

    public function test_marking_already_read_notification_returns_ok(): void
    {
        $admin = $this->adminUser();
        $n = $this->notification(['read_at' => now()->subMinute()]);
        $originalReadAt = $n->read_at->toDateTimeString();

        $this->actingAs($admin)
            ->patch(route('admin.notifications.read', $n))
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertEquals($originalReadAt, $n->fresh()->read_at->toDateTimeString());
    }

    // -------------------------------------------------------------------------
    // Mark all as read
    // -------------------------------------------------------------------------

    public function test_mark_all_as_read_updates_all_unread(): void
    {
        $admin = $this->adminUser();
        $this->notification();
        $this->notification();
        $this->notification(['read_at' => now()->subHour()]);

        $this->actingAs($admin)
            ->patch(route('admin.notifications.readAll'))
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertCount(0, AdminNotification::unread()->get());
        $this->assertCount(3, AdminNotification::all());
    }

    public function test_mark_all_as_read_with_no_notifications_returns_ok(): void
    {
        $this->actingAs($this->adminUser())
            ->patch(route('admin.notifications.readAll'))
            ->assertOk()
            ->assertJson(['ok' => true]);
    }

    // -------------------------------------------------------------------------
    // Dismiss single
    // -------------------------------------------------------------------------

    public function test_dismiss_single_notification_deletes_it(): void
    {
        $admin = $this->adminUser();
        $n = $this->notification();

        $this->actingAs($admin)
            ->delete(route('admin.notifications.dismiss', $n))
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertDatabaseMissing('admin_notifications', ['id' => $n->id]);
    }

    public function test_dismiss_one_does_not_affect_others(): void
    {
        $admin = $this->adminUser();
        $keep  = $this->notification();
        $gone  = $this->notification();

        $this->actingAs($admin)
            ->delete(route('admin.notifications.dismiss', $gone))
            ->assertOk();

        $this->assertDatabaseHas('admin_notifications', ['id' => $keep->id]);
        $this->assertDatabaseMissing('admin_notifications', ['id' => $gone->id]);
    }

    // -------------------------------------------------------------------------
    // Dismiss all
    // -------------------------------------------------------------------------

    public function test_dismiss_all_deletes_every_notification(): void
    {
        $admin = $this->adminUser();
        $this->notification();
        $this->notification();
        $this->notification(['read_at' => now()]);

        $this->actingAs($admin)
            ->delete(route('admin.notifications.dismissAll'))
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertCount(0, AdminNotification::all());
    }

    public function test_dismiss_all_with_no_notifications_returns_ok(): void
    {
        $this->actingAs($this->adminUser())
            ->delete(route('admin.notifications.dismissAll'))
            ->assertOk()
            ->assertJson(['ok' => true]);
    }
}
