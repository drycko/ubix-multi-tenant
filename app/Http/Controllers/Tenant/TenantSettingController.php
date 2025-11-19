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
        $paymentGateways = [
            'payfast' => [
                'is_default' => TenantSetting::getSetting('payfast_is_default'),
            ],
            'paygate' => [
                'is_default' => TenantSetting::getSetting('paygate_is_default'),
            ],
        ];
        return view('tenant.settings.index', compact('paymentGateways'));
    }

    /**
     * Show general settings page.
     */
    public function general()
    {
        // must be super user
        if (!is_super_user()) {
            abort(403, 'Unauthorized access');
        }

        // Fetch the tenant settings (These are key value pairs)
        $settings = TenantSetting::allSettings();
        return view('tenant.settings.general', compact('settings'));
    }

    /**
     * Update general settings.
     */
    public function updateGeneral(Request $request)
    {
        // must be super user
        if (!is_super_user()) {
            abort(403, 'Unauthorized access');
        }

        // Validate and save settings
        $validated = $request->validate([
            'tenant_name' => 'required|string|max:255',
            'tenant_admin_email' => 'required|email|max:255',
            'tenant_phone' => 'nullable|string|max:20',
            'tenant_address_street' => 'nullable|string|max:255',
            'tenant_address_street_2' => 'nullable|string|max:255',
            'tenant_address_city' => 'nullable|string|max:100',
            'tenant_address_state' => 'nullable|string|max:100',
            'tenant_address_zip' => 'nullable|string|max:20',
            'tenant_address_country' => 'nullable|string|max:100',
            'tenant_website' => 'nullable|url|max:255',
            'tenant_tax_number' => 'nullable|string|max:50',
            'tenant_registration_number' => 'nullable|string|max:50',
            'tenant_currency' => 'nullable|string|max:10',
            'tenant_timezone' => 'nullable|string|max:50',
            'tenant_logo' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Handle logo upload if present
        if ($request->hasFile('tenant_logo') && config('app.env') === 'production' && config('filesystems.default') === 'gcs') {
            $file = $request->file('tenant_logo');
            $gcsPath = 'tenant' . $tenant_id . '/branding/' . uniqid() . '_' . $file->getClientOriginalName();
            $stream = fopen($file->getRealPath(), 'r');
            Storage::disk('gcs')->put($gcsPath, $stream);
            fclose($stream);
            $validated['tenant_logo'] = $gcsPath;

        }elseif ($request->hasFile('tenant_logo')) {
            $file = $request->file('tenant_logo');
            $logoPath = $file->store('branding', 'public');;
            $validated['tenant_logo'] = $logoPath;
        } else {
            // Remove tenant_logo from validated if no file uploaded
            unset($validated['tenant_logo']);
        }

        foreach ($validated as $key => $value) {
            TenantSetting::setSetting($key, $value);
        }

        return redirect()->back()->with('success', 'General settings updated successfully.');
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

    /**
     * Show the form for editing PayGate credentials.
     */
    public function editPaygate()
    {
        // must be super user
        if (!is_super_user()) {
            abort(403, 'Unauthorized access');
        }

        $settings = [
            'merchant_id' => TenantSetting::getSetting('paygate_merchant_id'),
            'is_test' => TenantSetting::getSetting('paygate_is_test'),
            'is_default' => TenantSetting::getSetting('paygate_is_default'),
            'merchant_key' => TenantSetting::getEncryptedSetting('paygate_merchant_key'),
            'passphrase' => TenantSetting::getEncryptedSetting('paygate_passphrase'),
        ];
        return view('tenant.settings.paygate', compact('settings'));
    }
    /**
     * Update PayGate credentials in storage.
     */
    public function updatePaygate(Request $request)
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

        TenantSetting::setSetting('paygate_merchant_id', $request->merchant_id);
        TenantSetting::setSetting('paygate_is_test', $request->is_test);
        TenantSetting::setEncryptedSetting('paygate_merchant_key', $request->merchant_key);
        TenantSetting::setEncryptedSetting('paygate_passphrase', $request->passphrase);
        // if is default we have to unset others
        if ($request->is_default) {
            // Here you would typically have logic to unset other payment gateways as default
            if (TenantSetting::getSetting('payfast_is_default')) {
                TenantSetting::setSetting('payfast_is_default', false);
            }
            TenantSetting::setSetting('paygate_is_default', true);
        } else {
            TenantSetting::setSetting('paygate_is_default', false);
        }
        TenantSetting::setSetting('paygate_is_default', $request->is_default);

        return redirect()->back()->with('success', 'PayGate credentials updated successfully.');
    }
}
