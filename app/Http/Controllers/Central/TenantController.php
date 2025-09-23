<?php

namespace App\Http\Controllers\Central;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
// use Stancl\Tenancy\Database\Models\Tenant;


class TenantController extends Controller
{
    public function index()
    {
        // relationship with domains is provided by HasDomains trait in Tenant model so why eager loading is giving Call to undefined relationship [domains] on model [Stancl\Tenancy\Database\Models\Tenant].? 
        $tenants = Tenant::with('domains')->paginate(10); // Paginate the tenants, 10 per page
        return view('central.tenants.index', compact('tenants'));
    }

    public function create()
    {
        // subscription plans from database table
        $plans = SubscriptionPlan::where('is_active', true)->get();
        $currency = config('app.currency', 'USD');

        return view('central.tenants.create', compact('plans', 'currency'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|alpha_dash|unique:domains,domain',
        ]);

        $tenant = Tenant::create([
            'name' => $request->name,
            'is_active' => true,
            'data' => ['plan' => 'basic'],
        ]);

        $tenant->domains()->create([
            'domain' => $request->domain . '.nexusflow.co.za',
        ]);

        return redirect()->route('central.register')
            ->with('success', "Tenant {$request->name} created successfully! Access: {$request->domain}.nexusflow.co.za");
    }

    public function storeOld(Request $request)
    {
        $validate = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:tenants,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'timezone' => 'required|string|max:100',
            'currency' => 'required|string|max:10',
            'locale' => 'required|string|max:10',
            'plan' => 'nullable|string|max:50',
            'trial_ends_at' => 'nullable|date',
            'is_active' => 'required|boolean',
        ]);

        // validate subscription plan exists in subscription_plans table
        if ($request->plan) {
            $plan = SubscriptionPlan::where('name', $request->plan)->first();
            if (!$plan) {
                return back()->withErrors(['plan' => 'Selected subscription plan does not exist.'])->withInput();
            }
        }

        $tenant = Tenant::create($request->all());

        return redirect()->route('tenants.index')->with('success', 'Tenant created successfully.');
    }

    public function show(Tenant $tenant)
    {
        return view('central.tenants.show', compact('tenant'));
    }

    public function edit(Tenant $tenant)
    {
        return view('central.tenants.edit', compact('tenant'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:tenants,email,' . $tenant->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'timezone' => 'required|string|max:100',
            'currency' => 'required|string|max:10',
            'locale' => 'required|string|max:10',
            'plan' => 'nullable|string|max:50',
            'trial_ends_at' => 'nullable|date',
            'is_active' => 'required|boolean',
        ]);

        $tenant->update($request->all());

        return redirect()->route('tenants.index')->with('success', 'Tenant updated successfully.');
    }

    public function destroy(Tenant $tenant)
    {
        $tenant->delete();
        return redirect()->route('tenants.index')->with('success', 'Tenant deleted successfully.');
    }
}