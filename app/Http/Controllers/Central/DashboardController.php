<?php

namespace App\Http\Controllers\Central;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $tenants = Tenant::all();
        $totalTenants = $tenants->count();
        $activeTenants = $tenants->where('is_active', true)->count();
        // add domains to tenants
        foreach ($tenants as $tenant) {
            $tenant->load('domains');
        }

        $stats = [
			'total_tenants' => $totalTenants,
			'active_tenants' => $activeTenants,
			// Add more stats as needed
		];
        
        return view('central.dashboard', compact('stats', 'tenants'));
    }
}