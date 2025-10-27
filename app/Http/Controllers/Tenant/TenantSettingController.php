<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\TenantSetting;
use Illuminate\Http\Request;

class TenantSettingController extends Controller
{
    /**
     * Display a base settings page.
     */
    public function index()
    {
        // Show the base settings page
        return view('tenant.settings.index');
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
    public function show(TenantSetting $tenantSetting)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TenantSetting $tenantSetting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TenantSetting $tenantSetting)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TenantSetting $tenantSetting)
    {
        //
    }

    /**
     * Show the form for editing PayFast credentials.
     */
    public function editPayfast()
    {
        // must be super user
        if (!is_super_user()) {
            abort(403, 'Unauthorized access');
        }

        $settings = [
            'merchant_id' => TenantSetting::getSetting('payfast_merchant_id'),
            'is_test' => TenantSetting::getSetting('payfast_is_test'),
            'is_default' => TenantSetting::getSetting('payfast_is_default'),
            'merchant_key' => TenantSetting::getEncryptedSetting('payfast_merchant_key'),
            'passphrase' => TenantSetting::getEncryptedSetting('payfast_passphrase'),
        ];
        return view('tenant.settings.payfast', compact('settings'));
    }

    /**
     * Update PayFast credentials in storage.
     */
    public function updatePayfast(Request $request)
    {
        // must be super user (in future we will do a proper permission check in __construct)
        if (!is_super_user()) {
            abort(403, 'Unauthorized access');
        }

        $request->validate([
            'merchant_id' => 'required|string',
            'merchant_key' => 'required|string',
            'passphrase' => 'nullable|string',
            'is_test' => 'required|boolean',
            'is_default' => 'required|boolean',
        ]);

        TenantSetting::setSetting('payfast_merchant_id', $request->merchant_id);
        TenantSetting::setSetting('payfast_is_test', $request->is_test);
        TenantSetting::setEncryptedSetting('payfast_merchant_key', $request->merchant_key);
        TenantSetting::setEncryptedSetting('payfast_passphrase', $request->passphrase);
        // if is default we have to unset others
        if ($request->is_default) {
            // Here you would typically have logic to unset other payment gateways as default
            if (TenantSetting::getSetting('paygate_is_default')) {
                TenantSetting::setSetting('paygate_is_default', false);
            }
            TenantSetting::setSetting('payfast_is_default', true);
        } else {
            TenantSetting::setSetting('payfast_is_default', false);
        }
        TenantSetting::setSetting('payfast_is_default', $request->is_default);

        return redirect()->back()->with('success', 'PayFast credentials updated successfully.');
    }
}
