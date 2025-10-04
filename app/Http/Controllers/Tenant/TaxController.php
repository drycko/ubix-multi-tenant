<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Tax;
use App\Models\Tenant\Property;
use App\Traits\LogsTenantUserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaxController extends Controller
{
    use LogsTenantUserActivity;
    /**
     * Display a listing of taxes.
     */
    public function index(Request $request)
    {
        // must be super user
        if (!is_super_user()) {
            abort(403, 'Unauthorized access');
        }

        $properties = Property::where('is_active', true)->get(); // fetch all active properties

        // Get property filter
        $propertyId = $request->get('property_id');
        if ($propertyId && !$properties->contains('id', $propertyId)) {
            abort(403, 'Unauthorized access to property');
        }

        // Build query
        $query = Tax::with('property')
            ->whereIn('property_id', $properties->pluck('id'));

        if ($propertyId) {
            $query->where('property_id', $propertyId);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $taxes = $query->ordered()->paginate(15);

        return view('tenant.taxes.index', compact('taxes', 'properties', 'propertyId'));
    }

    /**
     * Show the form for creating a new tax.
     */
    public function create()
    {
        if (!is_super_user()) {
            abort(403, 'Unauthorized access');
        }
        $properties = Property::where('is_active', true)->get(); // fetch all active properties

        return view('tenant.taxes.create', compact('properties'));
    }

    /**
     * Store a newly created tax in storage.
     */
    public function store(Request $request)
    {
        
        if (!is_super_user()) {
            abort(403, 'Unauthorized access');
        }

        $propertyIds = Property::where('is_active', true)->pluck('id')->toArray(); // fetch all active property IDs

        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id|in:' . implode(',', $propertyIds),
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0|max:9999.9999',
            'type' => 'required|in:percentage,fixed',
            'is_inclusive' => 'boolean',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:1000',
            'display_order' => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $data = $validated;
            $data['is_inclusive'] = $request->boolean('is_inclusive');
            $data['is_active'] = $request->boolean('is_active', true);
            $data['display_order'] = $request->get('display_order', 0);

            $tax = Tax::create($data);

            // Log the activity
            $this->logTenantActivity(
                'create_tax',
                'Created tax: ' . $tax->name . ' (' . $tax->formatted_rate . ') for property: ' . $tax->property->name,
                $tax,
                [
                    'table' => 'taxes',
                    'id' => $tax->id,
                    'name' => $tax->name,
                    'rate' => $tax->rate,
                    'type' => $tax->type,
                    'property_id' => $tax->property_id,
                    'user_id' => auth()->id(),
                ]
            );

            DB::commit();

            return redirect()->route('tenant.taxes.index')
                ->with('success', 'Tax created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Tax creation failed: ' . $e->getMessage());
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create tax. Please try again.']);
        }
    }

    /**
     * Display the specified tax.
     */
    public function show(Tax $tax)
    {
        if (!is_super_user()) {
            abort(403, 'Unauthorized access');
        }
        $properties = Property::where('is_active', true)->get(); // fetch all active properties
        
        if (!$properties->contains('id', $tax->property_id)) {
            abort(403, 'Unauthorized access to this tax');
        }

        $tax->load('property');

        return view('tenant.taxes.show', compact('tax'));
    }

    /**
     * Show the form for editing the specified tax.
     */
    public function edit(Tax $tax)
    {
        if (!is_super_user()) {
            abort(403, 'Unauthorized access');
        }
        $properties = Property::where('is_active', true)->get(); // fetch all active properties
        
        if (!$properties->contains('id', $tax->property_id)) {
            abort(403, 'Unauthorized access to this tax');
        }

        return view('tenant.taxes.edit', compact('tax', 'properties'));
    }

    /**
     * Update the specified tax in storage.
     */
    public function update(Request $request, Tax $tax)
    {
        if (!is_super_user()) {
            abort(403, 'Unauthorized access');
        }
        $properties = Property::where('is_active', true)->get(); // fetch all active properties
        $propertyIds = $properties->pluck('id')->toArray();
        
        if (!$properties->contains('id', $tax->property_id)) {
            abort(403, 'Unauthorized access to this tax');
        }

        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id|in:' . implode(',', $propertyIds),
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0|max:9999.9999',
            'type' => 'required|in:percentage,fixed',
            'is_inclusive' => 'boolean',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:1000',
            'display_order' => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $oldName = $tax->name;
            $oldRate = $tax->rate;
            $oldType = $tax->type;
            $oldIsActive = $tax->is_active;

            $data = $validated;
            $data['is_inclusive'] = $request->boolean('is_inclusive');
            $data['is_active'] = $request->boolean('is_active', true);
            $data['display_order'] = $request->get('display_order', 0);

            $tax->update($data);

            // Log the activity
            $this->logTenantActivity(
                'update_tax',
                'Updated tax: ' . $tax->name . ' for property: ' . $tax->property->name,
                $tax,
                [
                    'table' => 'taxes',
                    'id' => $tax->id,
                    'old_name' => $oldName,
                    'new_name' => $tax->name,
                    'old_rate' => $oldRate,
                    'new_rate' => $tax->rate,
                    'old_type' => $oldType,
                    'new_type' => $tax->type,
                    'old_is_active' => $oldIsActive,
                    'new_is_active' => $tax->is_active,
                    'property_id' => $tax->property_id,
                    'user_id' => auth()->id(),
                ]
            );

            DB::commit();

            return redirect()->route('tenant.taxes.index')
                ->with('success', 'Tax updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Tax update failed: ' . $e->getMessage());
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update tax. Please try again.']);
        }
    }

    /**
     * Remove the specified tax from storage.
     */
    public function destroy(Tax $tax)
    {
        
        if (!is_super_user()) {
            abort(403, 'Unauthorized access');
        }
        $properties = Property::where('is_active', true)->get(); // fetch all active properties
        
        if (!$properties->contains('id', $tax->property_id)) {
            abort(403, 'Unauthorized access to this tax');
        }

        DB::beginTransaction();
        try {
            // Log the activity before deletion
            $this->logTenantActivity(
                'delete_tax',
                'Deleted tax: ' . $tax->name . ' (' . $tax->formatted_rate . ') for property: ' . $tax->property->name,
                $tax,
                [
                    'table' => 'taxes',
                    'id' => $tax->id,
                    'name' => $tax->name,
                    'rate' => $tax->rate,
                    'type' => $tax->type,
                    'property_id' => $tax->property_id,
                    'user_id' => auth()->id(),
                ]
            );

            $tax->delete();

            DB::commit();

            if (request()->expectsJson()) {
                return response()->json(['message' => 'Tax deleted successfully.']);
            }

            return redirect()->route('tenant.taxes.index')
                ->with('success', 'Tax deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Tax deletion failed: ' . $e->getMessage());
            
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Failed to delete tax. Please try again.'], 500);
            }
            
            return back()->withErrors(['error' => 'Failed to delete tax. Please try again.']);
        }
    }

    /**
     * Toggle tax status.
     */
    public function toggleStatus(Tax $tax)
    {
        if (!is_super_user()) {
            abort(403, 'Unauthorized access');
        }
        $properties = Property::where('is_active', true)->get(); // fetch all active properties
        
        if (!$properties->contains('id', $tax->property_id)) {
            abort(403, 'Unauthorized access to this tax');
        }

        DB::beginTransaction();
        try {
            $oldStatus = $tax->is_active;
            $newStatus = !$tax->is_active;
            
            $tax->update(['is_active' => $newStatus]);

            $status = $tax->is_active ? 'activated' : 'deactivated';

            // Log the activity
            $this->logTenantActivity(
                'toggle_tax_status',
                'Tax ' . $status . ': ' . $tax->name . ' for property: ' . $tax->property->name,
                $tax,
                [
                    'table' => 'taxes',
                    'id' => $tax->id,
                    'changes' => [
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                    ],
                    'action' => $status,
                    'user_id' => auth()->id(),
                ]
            );

            DB::commit();

            if (request()->expectsJson()) {
                return response()->json([
                    'message' => "Tax {$status} successfully.",
                    'is_active' => $tax->is_active
                ]);
            }

            return redirect()->route('tenant.taxes.index')
                ->with('success', "Tax {$status} successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Tax status toggle failed: ' . $e->getMessage());
            
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Failed to update tax status. Please try again.'], 500);
            }
            
            return back()->withErrors(['error' => 'Failed to update tax status. Please try again.']);
        }
    }
}