<?php
namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant\DigitalKey;

class DigitalKeyController extends Controller
{
    // Show digital keys for guest
    public function index(Request $request)
    {
        $keys = DigitalKey::where('guest_id', $request->user()?->id)->get();
        return view('tenant.guest-portal.digital-keys', compact('keys'));
    }

    // Optionally: Activate/deactivate key
    public function deactivate(Request $request, $keyId)
    {
        $key = DigitalKey::findOrFail($keyId);
        $key->active = false;
        $key->save();
        return redirect()->back()->with('success', 'Key deactivated.');
    }
}
