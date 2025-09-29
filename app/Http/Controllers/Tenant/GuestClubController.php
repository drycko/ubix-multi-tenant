<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\GuestClub;
use Illuminate\Http\Request;

class GuestClubController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch all guest clubs for the current property
        $guestClubs = GuestClub::where('property_id', property_id())->paginate(15);
        // count of members in each club
        foreach ($guestClubs as $club) {
            $club->member_count = $club->members()->count();

        }

        // Return a view with the guest clubs
        return view('tenant.guest-clubs.index', compact('guestClubs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Return a view to create a new guest club
        return view('tenant.guest-clubs.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
        try {
            // Ensure property context is available
            if (property_id() === null) {
                abort(403, 'Unauthorized action. No property context available.');
            }
            // Validate and create a new guest club
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'is_active' => 'required|boolean',
            ]);

            $guestClub = GuestClub::create(array_merge($validated, ['property_id' => property_id()]));
            // validate and create associated benefits form input [benefit_name[], benefit_description[]] if provided
            if ($request->has('benefit_name') && $request->has('benefit_description')) {
                $benefits = [];
                foreach ($request->input('benefit_name') as $key => $name) {
                    $benefits[] = [
                        'benefit_name' => $name,
                        'benefit_description' => $request->input('benefit_description')[$key] ?? null,
                    ];
                }
                $guestClub->benefits()->createMany($benefits);
            }

            return redirect()->route('tenant.admin.guest-clubs.index')->with('success', 'Guest club created successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'An error occurred while creating the guest club: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(GuestClub $guestClub)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GuestClub $guestClub)
    {
        // Return a view to edit the guest club
        return view('tenant.guest-clubs.edit', compact('guestClub'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GuestClub $guestClub)
    {
        // Validate and update the guest club
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);
        $guestClub->update($validated);

        // Update if benefit name exists and insert if not associated benefits if provided
        if ($request->has('benefit_name') && $request->has('benefit_description')) {
            $benefits = [];
            foreach ($request->input('benefit_name') as $key => $name) {
                $benefits[] = [
                    'benefit_name' => $name,
                    'benefit_description' => $request->input('benefit_description')[$key] ?? null,
                ];
            }
            // Delete existing benefits and create new ones
            $guestClub->benefits()->delete();
            $guestClub->benefits()->createMany($benefits);
        }

        return redirect()->route('tenant.admin.guest-clubs.index')->with('success', 'Guest club updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GuestClub $guestClub)
    {
        // Soft delete the guest club
        $guestClub->delete();
        return redirect()->route('tenant.admin.guest-clubs.index')->with('success', 'Guest club deleted successfully.');
    }
}
