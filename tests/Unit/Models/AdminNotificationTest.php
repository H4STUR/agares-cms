<?php

namespace Tests\Unit\Models;

use App\Models\AdminNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function make(array $attrs = []): AdminNotification
    {
        return AdminNotification::create(array_merge([
            'type'       => 'test',
            'title'      => 'Test Notification',
            'message'    => 'Something happened.',
            'icon'       => 'info',
            'icon_color' => 'primary',
        ], $attrs));
    }

    // -------------------------------------------------------------------------
    // isRead / markRead
    // -------------------------------------------------------------------------

    public function test_is_read_returns_false_when_unread(): void
    {
        $notification = $this->make();

        $this->assertFalse($notification->isRead());
    }

    public function test_is_read_returns_true_when_read(): void
    {
        $notification = $this->make(['read_at' => now()]);

        $this->assertTrue($notification->isRead());
    }

    public function test_mark_read_sets_read_at(): void
    {
        $notification = $this->make();
        $this->assertNull($notification->read_at);

        $notification->markRead();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_mark_read_is_idempotent(): void
    {
        $original = now()->subMinute();
        $notification = $this->make(['read_at' => $original]);

        $notification->markRead();

        $this->assertEquals(
            $original->toDateTimeString(),
            $notification->fresh()->read_at->toDateTimeString()
        );
    }

    // -------------------------------------------------------------------------
    // scopeUnread
    // -------------------------------------------------------------------------

    public function test_unread_scope_returns_only_unread(): void
    {
        $this->make();
        $this->make(['read_at' => now()]);

        $this->assertCount(1, AdminNotification::unread()->get());
    }

    public function test_unread_scope_returns_empty_when_all_read(): void
    {
        $this->make(['read_at' => now()]);
        $this->make(['read_at' => now()]);

        $this->assertCount(0, AdminNotification::unread()->get());
    }

    // -------------------------------------------------------------------------
    // Casts & accessors
    // -------------------------------------------------------------------------

    public function test_data_is_cast_to_array(): void
    {
        $notification = $this->make(['data' => ['order_id' => 42]]);

        $this->assertIsArray($notification->fresh()->data);
        $this->assertEquals(42, $notification->fresh()->data['order_id']);
    }

    public function test_null_data_stays_null(): void
    {
        $notification = $this->make();

        $this->assertNull($notification->data);
    }

    public function test_created_at_human_returns_string(): void
    {
        $notification = $this->make();

        $this->assertIsString($notification->created_at_human);
        $this->assertNotEmpty($notification->created_at_human);
    }

    public function test_created_at_human_is_included_in_json(): void
    {
        $notification = $this->make();
        $json = $notification->toArray();

        $this->assertArrayHasKey('created_at_human', $json);
    }
}
