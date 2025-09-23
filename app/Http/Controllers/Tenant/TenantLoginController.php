<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stancl\Tenancy\Database\Models\Domain;

class TenantLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('tenant.auth.login');
    }

	public function login(Request $request)
	{
		$request->validate([
			'email' => 'required|email',
			'password' => 'required|string',
		]);

		if ($this->attemptLogin($request)) {
			$request->session()->regenerate();
			return redirect()->intended(route('tenant.dashboard'));
		}

		return back()->withErrors([
			'email' => 'Invalid credentials or account not found.',
		])->withInput($request->only('email'));
	}

	protected function guard()
	{
		return Auth::guard('tenant');
	}

	protected function attemptLogin(Request $request)
    {
        $credentials = $this->credentials($request);
        
        // Add tenant scope for tenant domain logins
        $domain = request()->getHost();
        if (!in_array($domain, config('tenancy.central_domains'))) {
            $tenantDomain = Domain::where('domain', $domain)->first();
            if ($tenantDomain) {
                $credentials['tenant_id'] = $tenantDomain->tenant_id;
            }
        }
        
        return $this->guard()->attempt(
            $credentials, $request->filled('remember')
        );
    }

	/**
     * Get the needed authentication credentials from the request.
     */
    protected function credentials(Request $request)
    {
        return $request->only('email', 'password');
    }

    public function logout(Request $request)
    {
        Auth::guard('tenant')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('tenant.login');
    }
}