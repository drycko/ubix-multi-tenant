<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Property $property)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Property $property)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Property $property)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Property $property)
    {
        //
    }
    public function updateSettings(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'currency' => 'required|string|size:3',
        ]);

        $property = auth()->user()->property;
        $property->update($request->only(['name', 'address', 'phone', 'email', 'currency']));

        return redirect()->route('tenant.admin.property.settings')
            ->with('success', 'property settings updated successfully!');
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|exists:roles,name'
        ]);

        $property = auth()->user()->property;

        $user = $property->users()->create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $user->assignRole($request->role);

        return redirect()->route('tenant.admin.property.users')
            ->with('success', 'User created successfully!');
    }

    public function select()
    {
        $companies = property::active()->get();
        return view('property.select', compact('companies'));
    }

    public function storeSelection(Request $request)
    {
        $request->validate([
            'property_id' => 'required|exists:companies,id'
        ]);

        // Store property selection in session
        session(['current_property_id' => $request->property_id]);

        return redirect()->route('tenant.admin.dashboard')
            ->with('success', 'property selected successfully!');
    }

    public function settings()
    {
        $users = auth()->user()->property->users;
        $roles = \Spatie\Permission\Models\Role::all();
        $propertyApis = auth()->user()->property->propertyApis;
        $activeSubscription = auth()->user()->property->activeSubscription() ?? auth()->user()->property->trialSubscription();

        // show available subscription plan if no active subscription
        // if (!$activeSubscription) {
        $availablePlans = \App\Models\SubscriptionTier::all();
        // }
        // default app currency
        $currency = config('app.defaults.currency');

        return view('tenant.settings.index', compact('users', 'roles', 'propertyApis', 'activeSubscription', 'availablePlans', 'currency'));
    }
    

    public function storeApiKey(Request $request)
    {
        $validated = $request->validate([
            'api_name' => 'nullable|string|max:255',
            'api_key' => 'required|string|max:255|unique:property_apis',
            'api_secret' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        $validated['created_by'] = auth()->id();

        $property = auth()->user()->property;
        $property->propertyApis()->create($validated);

        return redirect()->route('tenant.admin.property.settings')
            ->with('success', 'API key created successfully!');
    }

    public function deleteApiKey(Request $request, $id)
    {
        $property = auth()->user()->property;
        $propertyApi = $property->propertyApis()->where('id', $id)->firstOrFail();
        $propertyApi->delete();

        return redirect()->route('tenant.admin.property.settings')
            ->with('success', 'API key deleted successfully!');
    }

    // public function premium()
    // {
    //     $property = auth()->user()->property;
    //     $plans = \App\Models\SubscriptionTier::all();
    //     $activeSubscription = $property->activeSubscription() ?? $property->trialSubscription();

    //     return view('tenant.settings.premium', compact('property', 'plans', 'activeSubscription'));
    // }

    public function cancelSubscription(Request $request)
    {
        $property = auth()->user()->property;
        $subscription = $property->activeSubscription();

        if ($subscription) {
            $subscription->cancel();
            return redirect()->route('tenant.admin.settings.premium')
                ->with('success', 'Subscription cancelled successfully. You will retain access until the end of the billing period.');
        }

        return redirect()->route('tenant.admin.settings.premium')
            ->with('error', 'No active subscription found to cancel.');
    }

    public function resumeSubscription(Request $request)
    {
        $property = auth()->user()->property;
        $subscription = $property->subscriptions()->where('status', 'canceled')->latest()->first();

        if ($subscription && $subscription->canResume()) {
            $subscription->resume();
            return redirect()->route('tenant.admin.settings.premium')
                ->with('success', 'Subscription resumed successfully.');
        }

        return redirect()->route('tenant.admin.settings.premium')
            ->with('error', 'No canceled subscription found to resume or cannot be resumed.');
    }
}

