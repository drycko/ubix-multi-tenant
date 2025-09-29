<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\TenantUserActivity;
use Illuminate\Http\Request;

class TenantUserActivityController extends Controller
{
    /**
     * Display a listing of user activities.
     */
    public function index(Request $request)
    {
        $query = TenantUserActivity::with(['user', 'subject'])
            ->where('tenant_user_id', auth()->id());

        // Filter by activity type if provided
        if ($request->has('type')) {
            $query->ofType($request->type);
        }

        // Filter by read status if provided
        if ($request->has('is_read')) {
            $query->where('is_read', $request->boolean('is_read'));
        }

        // Date range filter
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $activities = $query->latest()->paginate(15);

        return view('tenant.activities.index', compact('activities'));
    }

    /**
     * Display the specified activity.
     */
    public function show(TenantUserActivity $activity)
    {
        $this->authorize('view', $activity);

        if (!$activity->is_read) {
            $activity->markAsRead();
        }

        return view('tenant.activities.show', compact('activity'));
    }

    /**
     * Mark multiple activities as read.
     */
    public function markAsRead(Request $request)
    {
        $validated = $request->validate([
            'activity_ids' => 'required|array',
            'activity_ids.*' => 'exists:tenant_user_activities,id'
        ]);

        TenantUserActivity::whereIn('id', $validated['activity_ids'])
            ->where('tenant_user_id', auth()->id())
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return response()->json(['message' => 'Activities marked as read successfully']);
    }

    /**
     * Mark all activities as read.
     */
    public function markAllAsRead()
    {
        TenantUserActivity::where('tenant_user_id', auth()->id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return redirect()->back()->with('success', 'All activities marked as read');
    }

    /**
     * Clear all activities for the current user.
     */
    public function clearAll()
    {
        TenantUserActivity::where('tenant_user_id', auth()->id())->delete();

        return redirect()->route('tenant.activities.index')
            ->with('success', 'All activities have been cleared');
    }
}
