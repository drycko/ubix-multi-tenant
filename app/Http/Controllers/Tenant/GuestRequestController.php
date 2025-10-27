<?php
namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant\GuestRequest;
use App\Models\Tenant\GuestFeedback;

class GuestRequestController extends Controller
{
    // Show guest requests page
    public function index(Request $request)
    {
        $requests = GuestRequest::where('guest_id', $request->user()?->id)->get();
        $feedbacks = GuestFeedback::where('guest_id', $request->user()?->id)->get();
        return view('tenant.guest-portal.requests', compact('requests', 'feedbacks'));
    }

    // Submit a new room service or request
    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|string',
            'request' => 'required|string',
        ]);
        $data['guest_id'] = $request->user()?->id;
        GuestRequest::create($data);
        return redirect()->back()->with('success', 'Request submitted!');
    }

    // Submit guest feedback
    public function storeFeedback(Request $request)
    {
        $data = $request->validate([
            'feedback' => 'required|string',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);
        $data['guest_id'] = $request->user()?->id;
        GuestFeedback::create($data);
        return redirect()->back()->with('success', 'Feedback submitted!');
    }
}
