<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\TenantUserNotification;
use Illuminate\Http\Request;

class TenantUserNotificationController extends Controller
{
    /**
     * Display a listing of the user's notifications.
     */
    public function index(Request $request)
    {
        $query = TenantUserNotification::with('user')
            ->where('tenant_user_id', auth()->id());

        // Filter by type if provided
        if ($request->has('type')) {
            $query->ofType($request->type);
        }

        // Filter by read status if provided
        if ($request->has('is_read')) {
            $query->where('is_read', $request->boolean('is_read'));
        }

        // Show scheduled notifications
        if ($request->boolean('scheduled')) {
            $query->scheduled();
        }

        $notifications = $query->latest()->paginate(15);

        return view('tenant.notifications.index', compact('notifications'));
    }

    /**
     * Display the specified notification.
     */
    public function show(TenantUserNotification $notification)
    {
        $this->authorize('view', $notification);

        if (!$notification->is_read) {
            $notification->markAsRead();
        }

        if ($notification->link) {
            return redirect($notification->link);
        }

        return view('tenant.notifications.show', compact('notification'));
    }

    /**
     * Mark multiple notifications as read.
     */
    public function markAsRead(Request $request)
    {
        $validated = $request->validate([
            'notification_ids' => 'required|array',
            'notification_ids.*' => 'exists:tenant_user_notifications,id'
        ]);

        TenantUserNotification::whereIn('id', $validated['notification_ids'])
            ->where('tenant_user_id', auth()->id())
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return response()->json(['message' => 'Notifications marked as read successfully']);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        TenantUserNotification::where('tenant_user_id', auth()->id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return redirect()->back()->with('success', 'All notifications marked as read');
    }

    /**
     * Clear all notifications for the current user.
     */
    public function clearAll()
    {
        TenantUserNotification::where('tenant_user_id', auth()->id())->delete();

        return redirect()->route('tenant.notifications.index')
            ->with('success', 'All notifications have been cleared');
    }
}
