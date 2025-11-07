<?php

namespace App\Http\Controllers\Tenant\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create()
    {
        return view('tenant.auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate('tenant');

        $request->session()->regenerate();

        $user = auth()->guard('tenant')->user();

        // Check if user must change password
        if ($user->must_change_password) {
            return redirect()
                ->route('tenant.password.change')
                ->with('warning', 'You must change your password before continuing.');
        }

        return redirect()->intended(RouteServiceProvider::TENANT_HOME);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
    {
        Auth::guard('tenant')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('tenant.login');
    }
}