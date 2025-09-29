<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\CentralSetting;
use App\Traits\LogsAdminActivity;
use Illuminate\Http\Request;

class CentralSettingController extends Controller
{
    use LogsAdminActivity;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // use the central database connection from here because I am in the central app
        config(['database.connections.tenant' => config('database.connections.central')]);

        // we need to manually add new permissions
        

        // Fetch the central settings (These are key value pairs)
        $settings = CentralSetting::allSettings();
        return view('central.settings.index', compact('settings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // not needed since the settings index is the form
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // use the central database connection from here because I am in the central app
        config(['database.connections.tenant' => config('database.connections.central')]);

        // Validate and save settings
        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'site_email' => 'required|email|max:255',
            'support_email' => 'nullable|email|max:255',
            'contact_email' => 'nullable|email|max:255',
            'site_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            // Add more settings validation as needed
        ]);

        // for now we do not save anything (we will get back to this)
        return redirect()->route('central.settings')->with('success', 'Settings updated successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(CentralSetting $centralSetting)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CentralSetting $centralSetting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CentralSetting $centralSetting)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CentralSetting $centralSetting)
    {
        //
    }
}
