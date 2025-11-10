<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tax;
use App\Traits\LogsAdminActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaxController extends Controller
{
  use LogsAdminActivity;
  
  public function __construct()
  {
    // use the central database connection from here because I am in the central app
    config(['database.connections.tenant' => config('database.connections.central')]);
    $this->middleware('auth:web');
    // TODO: Add permission middleware when central permissions are implemented
    $this->middleware('permission:view taxes')->except(['index', 'show']);
    $this->middleware('permission:manage taxes')->only(['create', 'store', 'edit', 'update', 'destroy']);
  }
  
  /**
  * Display a listing of taxes.
  */
  public function index(Request $request)
  {
    // use the central database connection from here because I am in the central app
    // config(['database.connections.tenant' => config('database.connections.central')]);  
    // Build query
    $query = Tax::query();
    
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
    
    return view('central.taxes.index', compact('taxes'));
  }
  
  /**
  * Show the form for creating a new tax.
  */
  public function create()
  {
    return view('central.taxes.create');
  }
  
  /**
  * Store a newly created tax in storage.
  */
  public function store(Request $request)
  {
    $validated = $request->validate([
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
      
      // Log activity
      $this->logAdminActivity(
        "store_tax",
        "taxes",
        $tax->id,
        "Created a new tax"
      );
      $this->createAdminNotification("A new tax was created");
      DB::commit();
      
      return redirect()->route('central.taxes.index')
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
    return view('central.taxes.show', compact('tax'));
  }
  
  /**
  * Show the form for editing the specified tax.
  */
  public function edit(Tax $tax)
  {
    return view('central.taxes.edit', compact('tax'));
  }
  
  /**
  * Update the specified tax in storage.
  */
  public function update(Request $request, Tax $tax)
  {
    $validated = $request->validate([
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
      
      // Log activity
      $this->logAdminActivity(
        "update_tax",
        "taxes",
        $tax->id,
        "Updated a tax"
      );
      $this->createAdminNotification("A tax was updated");
      
      DB::commit();
      
      return redirect()->route('central.taxes.index')
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
    DB::beginTransaction();
    try {
      // Log the activity before deletion
      $this->logAdminActivity(
        'delete_tax',
        'taxes',
        $tax->id,
        'Deleted a tax: ' . $tax->name
      );
      $this->createAdminNotification("A tax was deleted");
      
      $tax->delete();
      
      DB::commit();
      
      if (request()->expectsJson()) {
        return response()->json(['message' => 'Tax deleted successfully.']);
      }
      
      return redirect()->route('central.taxes.index')
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
    DB::beginTransaction();
    try {
      $oldStatus = $tax->is_active;
      $newStatus = !$tax->is_active;
      
      $tax->update(['is_active' => $newStatus]);
      
      $status = $tax->is_active ? 'activated' : 'deactivated';
      
      // Log activity
      $this->logAdminActivity(
        'toggle_tax_status',
        'taxes',
        $tax->id,
        "Tax status changed from " . ($oldStatus ? 'active' : 'inactive') . " to " . ($newStatus ? 'active' : 'inactive')
      );
      $this->createAdminNotification("Tax status was changed");
      
      DB::commit();
      
      if (request()->expectsJson()) {
        return response()->json([
          'message' => "Tax {$status} successfully.",
          'is_active' => $tax->is_active
        ]);
      }
      
      return redirect()->route('central.taxes.index')
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
