<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Traits\LogsAdminActivity;
use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
    use LogsAdminActivity;
  
    public function __construct()
    {
        // use the central database connection from here because I am in the central app
        config(['database.connections.tenant' => config('database.connections.central')]);
        $this->middleware('auth:web');
        // TODO: Add permission middleware when central permissions are implemented
        $this->middleware('permission:view subscription plans')->only(['index', 'show']);
        $this->middleware('permission:manage subscription plans')->only(['destroy']);
        $this->middleware('permission:view trashed data')->only(['trashed']);
        $this->middleware('permission:restore trashed data')->only(['restore', 'restoreAll']);
        $this->middleware('permission:force delete trashed data')->only(['forceDelete', 'forceDeleteAll']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // use the central database connection from here because I am in the central app
        config(['database.connections.tenant' => config('database.connections.central')]);
        
        // Display a listing of subscription plans
        $subscriptionPlans = SubscriptionPlan::whereNull('deleted_at')->orderBy('sort_order', 'asc')->paginate(10);
        $currency = config('app.currency', 'USD');
        return view('central.subscription_plans.index', compact('subscriptionPlans', 'currency'));
    }

    /**
     * Display a trashed listing of the resource.
     */
    public function trashed()
    {
        // use the central database connection from here because I am in the central app
        config(['database.connections.tenant' => config('database.connections.central')]);

        // Display a listing of trashed subscription plans (why are soft deleted plans not showing up with onlyTrashed()?
        $trashedPlans = SubscriptionPlan::onlyTrashed()->orderBy('deleted_at', 'desc')->get();
        foreach ($trashedPlans as $plan) {
            $plan->deletedByAdmin = \App\Models\AdminActivity::where('table_name', 'subscription_plans')
                ->whereIn('activity_type', ['soft_delete', 'delete'])
                ->where('record_id', $plan->id)
                ->latest()
                ->first();
            $plan->deletedByAdminName = $plan->deletedByAdmin ? $plan->deletedByAdmin->admin->name : null;

        }
        $currency = config('app.currency', 'USD');
        return view('central.subscription_plans.trash', compact('trashedPlans', 'currency'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // use the central database connection from here because I am in the central app
        config(['database.connections.tenant' => config('database.connections.central')]);
        
        // check if user can manage plans
        if (!auth()->user()->can('manage plans')) {
            return redirect()->back()->with('error', 'You do not have permission to perform this action.');
        }

        $additionalFeatures = SubscriptionPlan::ADDITIONAL_FEATURES;
        $billingPeriods = SubscriptionPlan::BILLING_PERIODS;
        // Show form to create a new subscription plan
        $currency = config('app.currency', 'USD');
        return view('central.subscription_plans.create', compact('currency', 'additionalFeatures', 'billingPeriods'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // use the central database connection from here because I am in the central app
            config(['database.connections.tenant' => config('database.connections.central')]);

            // check if user can manage plans
            if (!auth()->user()->can('manage plans')) {
                return redirect()->back()->with('error', 'You do not have permission to perform this action.');
            }

            // Validate the request
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'slug' => 'required|string|max:255|unique:subscription_plans,slug',
                'description' => 'nullable|string',
                'monthly_price' => 'required|numeric|min:0',
                'yearly_price' => 'required|numeric|min:0',
                'max_properties' => 'required|integer|min:1',
                'max_users' => 'required|integer|min:1',
                'max_rooms' => 'required|integer|min:1',
                'max_guests' => 'required|integer|min:1',
                'has_analytics' => 'sometimes|boolean',
                'has_support' => 'sometimes|boolean',
                'has_api_access' => 'sometimes|boolean',
                'sort_order' => 'nullable|integer',
                'is_active' => 'sometimes|boolean',
                'features' => 'nullable|array',
                'limitations' => 'nullable|array',
            ]);

            // start transaction
            \DB::beginTransaction();

            // validate the array of features and limitations to be only from the predefined list
            $validated['features'] = array_intersect($validated['features'] ?? [], array_keys(SubscriptionPlan::ADDITIONAL_FEATURES));
            $validated['limitations'] = array_intersect($validated['limitations'] ?? [], array_keys(SubscriptionPlan::ADDITIONAL_FEATURES));
            // has analytics if there is analytics in features
            $validated['has_analytics'] = in_array('analytics', $validated['features'] ?? []);
            // has support if there is support in features
            $validated['has_support'] = in_array('support', $validated['features'] ?? []);
            // has api access if there is api_access in features
            $validated['has_api_access'] = in_array('api_access', $validated['features'] ?? []);
            // is_active by default true
            $validated['is_active'] = $validated['is_active'] ?? true;
            // increment sort order by 1 from the last plan
            $lastPlan = SubscriptionPlan::orderBy('sort_order', 'desc')->first();
            $validated['sort_order'] = ($lastPlan->sort_order ?? 0) + 1;

            // Create the subscription plan
            $subscriptionPlan = SubscriptionPlan::create($validated);

            // record admin activity
            if (auth()->user()) {
                $adminActivity = new \App\Models\AdminActivity();
                $adminActivity->admin_id = auth()->user()->id;
                $adminActivity->activity_type = 'create';
                $adminActivity->ip_address = request()->ip();
                $adminActivity->user_agent = request()->header('User-Agent');
                $adminActivity->table_name = 'subscription_plans';
                $adminActivity->record_id = $subscriptionPlan->id;
                $adminActivity->description = 'Created subscription plan: ' . ($validated['name'] ?? 'N/A');
                $adminActivity->is_read = false;
                $adminActivity->save();
            }

            // record admin notification
            if (auth()->user()) {
                $adminNotification = new \App\Models\AdminNotification();
                $adminNotification->admin_id = auth()->user()->id;
                $adminNotification->notification_type = 'system';
                $adminNotification->message = 'Subscription plan "' . ($validated['name'] ?? 'N/A') . '" has been created.';
                $adminNotification->ip_address = request()->ip();
                $adminNotification->user_agent = request()->header('User-Agent');
                $adminNotification->is_read = false;
                $adminNotification->save();
            }

            // commit transaction
            \DB::commit();

            // Redirect to the subscription plans list with success message
            return redirect()->route('central.plans.index')->with('success', 'Subscription plan created successfully.');
        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'An error occurred while creating the subscription plan: ' . $e->getMessage());
        }

    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // use the central database connection from here because I am in the central app
        config(['database.connections.tenant' => config('database.connections.central')]);

        $subscriptionPlan = SubscriptionPlan::findOrFail($id);
        $currency = config('app.currency', 'USD');

        // Show details of a specific subscription plan
        return view('central.subscription_plans.show', compact('subscriptionPlan', 'currency'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // use the central database connection from here because I am in the central app
        config(['database.connections.tenant' => config('database.connections.central')]);
        
        $subscriptionPlan = SubscriptionPlan::findOrFail($id);
        
        // check if user can manage plans
        if (!auth()->user()->can('manage plans')) {
            return redirect()->back()->with('error', 'You do not have permission to perform this action.');
        }

        $additionalFeatures = SubscriptionPlan::ADDITIONAL_FEATURES;
        $billingPeriods = SubscriptionPlan::BILLING_PERIODS;
        // Show form to create a new subscription plan
        $currency = config('app.currency', 'USD');
        // Show the form for editing the specified subscription plan
        return view('central.subscription_plans.edit', compact('subscriptionPlan', 'additionalFeatures', 'billingPeriods', 'currency'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            // use the central database connection from here because I am in the central app
            config(['database.connections.tenant' => config('database.connections.central')]);
            
            $subscriptionPlan = SubscriptionPlan::findOrFail($id);

            // Validate the request data
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'slug' => 'required|string|max:255|unique:subscription_plans,slug,' . $subscriptionPlan->id,
                'description' => 'nullable|string',
                'monthly_price' => 'required|numeric|min:0',
                'yearly_price' => 'required|numeric|min:0',
                'max_properties' => 'required|integer|min:1',
                'max_users' => 'required|integer|min:1',
                'max_rooms' => 'required|integer|min:1',
                'max_guests' => 'required|integer|min:1',
                'has_analytics' => 'sometimes|boolean',
                'has_support' => 'sometimes|boolean',
                'has_api_access' => 'sometimes|boolean',
                'sort_order' => 'nullable|integer',
                'is_active' => 'sometimes|boolean',
                'features' => 'nullable|array',
                'limitations' => 'nullable|array',
            ]);

            \Log::info('Updating subscription plan with features: ', $validated['features'] ?? []);

            // start transaction
            \DB::beginTransaction();

            // validate the array of features and limitations to be only from the predefined list (store as json)
            $validated['features'] = array_intersect($validated['features'] ?? [], array_keys(SubscriptionPlan::ADDITIONAL_FEATURES));
            $validated['limitations'] = array_intersect($validated['limitations'] ?? [], array_keys(SubscriptionPlan::ADDITIONAL_FEATURES));
            // has analytics if there is analytics in features
            $validated['has_analytics'] = in_array('analytics', $validated['features'] ?? []);
            // has support if there is support in features
            $validated['has_support'] = in_array('support', $validated['features'] ?? []);
            // has api access if there is api_access in features
            $validated['has_api_access'] = in_array('api_access', $validated['features'] ?? []);
            // is_active by default true
            $validated['is_active'] = $validated['is_active'] ?? true;

            // Update the subscription plan
            $subscriptionPlan->update($validated);

            // record admin activity
            if (auth()->user()) {
                $adminActivity = new \App\Models\AdminActivity();
                $adminActivity->admin_id = auth()->user()->id;
                $adminActivity->activity_type = 'update';
                $adminActivity->ip_address = request()->ip();
                $adminActivity->user_agent = request()->header('User-Agent');
                $adminActivity->table_name = 'subscription_plans';
                $adminActivity->record_id = $subscriptionPlan->id;
                $adminActivity->description = 'Updated subscription plan: ' . ($validated['name'] ?? 'N/A');
                $adminActivity->is_read = false;
                $adminActivity->save();
            }

            // record admin notification
            if (auth()->user()) {
                $adminNotification = new \App\Models\AdminNotification();
                $adminNotification->admin_id = auth()->user()->id;
                $adminNotification->notification_type = 'system';
                $adminNotification->message = 'Subscription plan "' . ($validated['name'] ?? 'N/A') . '" has been updated.';
                $adminNotification->ip_address = request()->ip();
                $adminNotification->user_agent = request()->header('User-Agent');
                $adminNotification->is_read = false;
                $adminNotification->save();
            }
            // commit transaction
            \DB::commit();
            // Redirect to the subscription plans list with success message
            return redirect()->route('central.plans.index')->with('success', 'Subscription plan updated successfully.');
        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'An error occurred while updating the subscription plan: ' . $e->getMessage());
        }
    }

    /**
     * Soft delete the specified resource from storage.
     */
    public function softDelete($id)
    {
        // use the central database connection from here because I am in the central app
        config(['database.connections.tenant' => config('database.connections.central')]);
        
        $subscriptionPlan = SubscriptionPlan::findOrFail($id);
        try {
            // check if user can manage plans
            if (!auth()->user()->can('manage plans')) {
                return redirect()->back()->with('error', 'You do not have permission to perform this action.');
            }

            // start transaction
            \DB::beginTransaction();

            // soft delete the subscription plan
            $planName = $subscriptionPlan->name;
            $subscriptionPlan->delete(); // what is the difference between delete and destroy in laravel? So this delete will not destroy the record permanently but will set the deleted_at timestamp?

            // record admin activity
            if (auth()->user()) {
                $adminActivity = new \App\Models\AdminActivity();
                $adminActivity->admin_id = auth()->user()->id;
                $adminActivity->activity_type = 'soft_delete';
                $adminActivity->ip_address = request()->ip();
                $adminActivity->user_agent = request()->header('User-Agent');
                $adminActivity->table_name = 'subscription_plans';
                $adminActivity->record_id = $subscriptionPlan->id;
                $adminActivity->description = 'Soft deleted subscription plan: ' . $planName;
                $adminActivity->is_read = false;
                $adminActivity->save();
            }

            // record admin notification
            if (auth()->user()) {
                $adminNotification = new \App\Models\AdminNotification();
                $adminNotification->admin_id = auth()->user()->id;
                $adminNotification->notification_type = 'system';
                $adminNotification->message = 'Subscription plan "' . $planName . '" has been soft deleted.';
                $adminNotification->ip_address = request()->ip();
                $adminNotification->user_agent = request()->header('User-Agent');
                $adminNotification->is_read = false;
                $adminNotification->save();
            }

            // commit transaction
            \DB::commit();

            // Redirect to the subscription plans list with success message
            return redirect()->route('central.plans.index')->with('success', 'Subscription plan soft deleted successfully.');
        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->back()->with('error', 'An error occurred while soft deleting the subscription plan: ' . $e->getMessage());
        }
    }

    /**
     * Restore the specified resource from soft delete.
     */
    public function restore($id)
    {
        // use the central database connection from here because I am in the central app
        config(['database.connections.tenant' => config('database.connections.central')]);

        try {
            \DB::beginTransaction();

            $subscriptionPlan = SubscriptionPlan::onlyTrashed()->where('id', $id)->firstOrFail();
            // check if user can manage plans
            if (!auth()->user()->can('manage plans')) {
                return redirect()->back()->with('error', 'You do not have permission to perform this action.');
            }

            // restore the subscription plan
            $planName = $subscriptionPlan->name;
            $subscriptionPlan->restore();

            // record admin activity
            if (auth()->user()) {
                $adminActivity = new \App\Models\AdminActivity();
                $adminActivity->admin_id = auth()->user()->id;
                $adminActivity->activity_type = 'restore';
                $adminActivity->ip_address = request()->ip();
                $adminActivity->user_agent = request()->header('User-Agent');
                $adminActivity->table_name = 'subscription_plans';
                $adminActivity->record_id = $subscriptionPlan->id;
                $adminActivity->description = 'Restored subscription plan: ' . $planName;
                $adminActivity->is_read = false;
                $adminActivity->save();
            }

            // record admin notification
            if (auth()->user()) {
                $adminNotification = new \App\Models\AdminNotification();
                $adminNotification->admin_id = auth()->user()->id;
                $adminNotification->notification_type = 'system';
                $adminNotification->message = 'Subscription plan "' . $planName . '" has been restored.';
                $adminNotification->ip_address = request()->ip();
                $adminNotification->user_agent = request()->header('User-Agent');
                $adminNotification->is_read = false;
                $adminNotification->save();
            }

            \DB::commit();

            return redirect()->back()->with('success', 'Subscription plan restored successfully.');
        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->back()->with('error', 'An error occurred while restoring the subscription plan: ' . $e->getMessage());
        }
    }

    /**
     * Restore all resources from soft delete.
     */
    public function restoreAll()
    {
        // use the central database connection from here because I am in the central app
        config(['database.connections.tenant' => config('database.connections.central')]);

        try {
            \DB::beginTransaction();

            $trashedPlans = SubscriptionPlan::onlyTrashed()->get();

            // check if user can manage plans
            if (!auth()->user()->can('manage plans')) {
                return redirect()->back()->with('error', 'You do not have permission to perform this action.');
            }

            foreach ($trashedPlans as $subscriptionPlan) {
                $planName = $subscriptionPlan->name;
                $subscriptionPlan->restore();

                // record admin activity
                if (auth()->user()) {
                    $adminActivity = new \App\Models\AdminActivity();
                    $adminActivity->admin_id = auth()->user()->id;
                    $adminActivity->activity_type = 'restore';
                    $adminActivity->ip_address = request()->ip();
                    $adminActivity->user_agent = request()->header('User-Agent');
                    $adminActivity->table_name = 'subscription_plans';
                    $adminActivity->record_id = $subscriptionPlan->id;
                    $adminActivity->description = 'Restored subscription plan: ' . $planName;
                    $adminActivity->is_read = false;
                    $adminActivity->save();
                }

                // record admin notification
                if (auth()->user()) {
                    $adminNotification = new \App\Models\AdminNotification();
                    $adminNotification->admin_id = auth()->user()->id;
                    $adminNotification->notification_type = 'system';
                    $adminNotification->message = 'Subscription plan "' . $planName . '" has been restored.';
                    $adminNotification->ip_address = request()->ip();
                    $adminNotification->user_agent = request()->header('User-Agent');
                    $adminNotification->is_read = false;
                    $adminNotification->save();
                }
            }

            \DB::commit();

            return redirect()->back()->with('success', 'All subscription plans restored successfully.');
        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->back()->with('error', 'An error occurred while restoring subscription plans: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // use the central database connection from here because I am in the central app
        config(['database.connections.tenant' => config('database.connections.central')]);

        try {
            \DB::beginTransaction();

            $subscriptionPlan = SubscriptionPlan::findOrFail($id);

            // check if user can manage plans
            if (!auth()->user()->can('manage plans')) {
                return redirect()->back()->with('error', 'You do not have permission to perform this action.');
            }

            // Perform force delete
            $subscriptionPlan->forceDelete();

            // record admin activity
            if (auth()->user()) {
                $adminActivity = new \App\Models\AdminActivity();
                $adminActivity->admin_id = auth()->user()->id;
                $adminActivity->activity_type = 'delete';
                $adminActivity->ip_address = request()->ip();
                $adminActivity->user_agent = request()->header('User-Agent');
                $adminActivity->table_name = 'subscription_plans';
                $adminActivity->record_id = $subscriptionPlan->id;
                $adminActivity->description = 'Deleted subscription plan: ' . $subscriptionPlan->name;
                $adminActivity->is_read = false;
                $adminActivity->save();
            }

            // record admin notification
            if (auth()->user()) {
                $adminNotification = new \App\Models\AdminNotification();
                $adminNotification->admin_id = auth()->user()->id;
                $adminNotification->notification_type = 'system';
                $adminNotification->message = 'Subscription plan "' . $subscriptionPlan->name . '" has been deleted.';
                $adminNotification->ip_address = request()->ip();
                $adminNotification->user_agent = request()->header('User-Agent');
                $adminNotification->is_read = false;
                $adminNotification->save();
            }

            \DB::commit();

            return redirect()->back()->with('success', 'Subscription plan deleted successfully.');
        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->back()->with('error', 'An error occurred while deleting the subscription plan: ' . $e->getMessage());
        }
    }
}
