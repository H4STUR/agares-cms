<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function markRead(AdminNotification $notification): JsonResponse
    {
        $notification->markRead();

        return response()->json(['ok' => true]);
    }

    public function markAllRead(): JsonResponse
    {
        AdminNotification::unread()->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }

    public function dismiss(AdminNotification $notification): JsonResponse
    {
        $notification->delete();

        return response()->json(['ok' => true]);
    }

    public function dismissAll(): JsonResponse
    {
        AdminNotification::query()->delete();

        return response()->json(['ok' => true]);
    }
}
