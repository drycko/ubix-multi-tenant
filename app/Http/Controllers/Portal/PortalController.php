<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantAdmin;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionInvoice;
use App\Services\PaymentGateways\PayfastGatewayService;
use App\Services\TaxCalculationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class PortalController extends Controller
{
    protected PayfastGatewayService $payfastGatewayService;

    public function __construct(PayfastGatewayService $payfastGatewayService)
    {
        $this->middleware('auth:tenant_admin');
        $this->payfastGatewayService = $payfastGatewayService;
        $this->taxCalculationService = new TaxCalculationService();
    }
    /**
     * Show the tenant admin dashboard
     */
    public function dashboard()
    {
        $admin = Auth::guard('tenant_admin')->user();
        $tenant = $admin->tenant;
        
        // Load relationships
        $tenant->load('domains');
        $currentSubscription = $tenant->currentSubscription;
        
        if ($currentSubscription) {
            $currentSubscription->load('plan');
        }
        
        // Get recent invoices
        $recentInvoices = SubscriptionInvoice::where('tenant_id', $tenant->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Get subscription history
        $subscriptionHistory = Subscription::where('tenant_id', $tenant->id)
            ->with('plan')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        $currency = config('app.currency', 'USD');
        
        return view('portal.dashboard', compact(
            'admin',
            'tenant',
            'currentSubscription',
            'recentInvoices',
            'subscriptionHistory',
            'currency'
        ));
    }

    /**
     * Show subscription management page
     */
    public function subscription()
    {
        $admin = Auth::guard('tenant_admin')->user();
        
        if (!$admin->canManageBilling()) {
            return redirect()->route('portal.dashboard')
                ->with('error', 'You do not have permission to manage billing.');
        }
        
        $tenant = $admin->tenant;
        $currentSubscription = $tenant->currentSubscription;
        
        if ($currentSubscription) {
            $currentSubscription->load('plan');
        }
        
        // Get available plans
        $availablePlans = SubscriptionPlan::where('is_active', true)->get();
        $currency = config('app.currency', 'USD');
        
        return view('portal.subscription', compact(
            'admin',
            'tenant',
            'currentSubscription',
            'availablePlans',
            'currency'
        ));
    }

    /**
     * Show invoices page
     */
    public function invoices()
    {
        $admin = Auth::guard('tenant_admin')->user();
        
        if (!$admin->canManageBilling()) {
            return redirect()->route('portal.dashboard')
                ->with('error', 'You do not have permission to view invoices.');
        }
        
        $tenant = $admin->tenant;
        $invoices = SubscriptionInvoice::where('tenant_id', $tenant->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        $currency = config('app.currency', 'USD');
        
        return view('portal.invoices', compact('admin', 'tenant', 'invoices', 'currency'));
    }

    /**
     * Show account settings page
     */
    public function settings()
    {
        $admin = Auth::guard('tenant_admin')->user();
        $tenant = $admin->tenant;
        
        return view('portal.settings', compact('admin', 'tenant'));
    }

    /**
     * Update account settings
     */
    public function updateSettings(Request $request)
    {
        $admin = Auth::guard('tenant_admin')->user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:tenant_admins,email,' . $admin->id,
            'phone' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
        ]);
        
        $admin->update($validated);
        
        return redirect()->route('portal.settings')
            ->with('success', 'Settings updated successfully.');
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $admin = Auth::guard('tenant_admin')->user();
        
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);
        
        if (!Hash::check($request->current_password, $admin->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }
        
        $admin->update([
            'password' => Hash::make($request->password),
        ]);
        
        return redirect()->route('portal.settings')
            ->with('success', 'Password updated successfully.');
    }

    /**
     * Request plan upgrade
     */
    public function requestUpgrade(Request $request)
    {
        try {
            $admin = Auth::guard('tenant_admin')->user();
            
            if (!$admin->canManageBilling()) {
                return redirect()->route('portal.subscription')
                    ->with('error', 'You do not have permission to manage billing.');
            }
            
            $validated = $request->validate([
                'plan_id' => 'required|exists:subscription_plans,id',
                'billing_cycle' => 'required|in:monthly,yearly',
            ]);
            
            $tenant = $admin->tenant;
            $plan = SubscriptionPlan::findOrFail($validated['plan_id']);
            
            // Create invoice for the plan change
            $price = $validated['billing_cycle'] === 'yearly' ? $plan->yearly_price : $plan->monthly_price;
            $endDate = $validated['billing_cycle'] === 'yearly' ? now()->addYear() : now()->addMonth();
            
            // Create inactive subscription pending payment
            $newSubscription = Subscription::create([
                'tenant_id' => $tenant->id,
                'subscription_plan_id' => $plan->id,
                'price' => $price,
                'billing_cycle' => $validated['billing_cycle'],
                'start_date' => now(),
                'end_date' => $endDate,
                'status' => 'inactive',
            ]);
            // Calculate tax for the booking
            $taxCalculation = $this->taxCalculationService->calculateTaxForInvoice($price);
            
            // Create invoice
            $newInvoice = SubscriptionInvoice::create([
                'tenant_id' => $tenant->id,
                'subscription_id' => $newSubscription->id,
                'invoice_number' => SubscriptionInvoice::generateInvoiceNumber(),
                'invoice_date' => now(),
                'due_date' => now()->addDays(7),
                'amount' => $taxCalculation['total_amount'],
                'subtotal_amount' => $taxCalculation['subtotal_amount'],
                'tax_amount' => $taxCalculation['tax_amount'],
                'tax_rate' => $taxCalculation['tax_rate'],
                'tax_name' => $taxCalculation['tax_name'],
                'tax_type' => $taxCalculation['tax_type'],
                'tax_inclusive' => $taxCalculation['tax_inclusive'],
                'tax_id' => $taxCalculation['tax_id'],
                'status' => 'pending',
                'notes' => "Upgrade to {$plan->name} ({$validated['billing_cycle']})",
            ]);

            // create invoice payment record with status pending
            // unique transaction id
            $paymentId = 'PF_INV' . $newInvoice->id . '_' . Str::random(6);
            $newInvoice->payments()->create([
                'subscription_id' => $newSubscription->id,
                'invoice_id' => $newInvoice->id,
                'amount' => $price,
                'payment_date' => null,
                'payment_method' => 'payfast',
                'notes' => "Payment for invoice {$newInvoice->invoice_number}",
                'transaction_id' => $paymentId,
                'status' => 'pending',
            ]);
            // we will update this when payment is completed in return handler

            // redirect to the new invoice created for payment
            return redirect()->route('portal.invoices.show', $newInvoice->id)
                ->with('success', 'Plan upgrade request submitted. Please complete payment to activate.');
        } catch (\Exception $e) {
            \Log::error('Error requesting plan upgrade: ' . $e->getMessage());
            return redirect()->route('portal.subscription')
                ->with('error', 'An error occurred while processing your request: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified invoice
     */
    public function showInvoice(SubscriptionInvoice $invoice)
    {
        $admin = Auth::guard('tenant_admin')->user();
        
        if (!$admin->canManageBilling()) {
            return redirect()->route('portal.dashboard')
                ->with('error', 'You do not have permission to view invoices.');
        }

        // Authorization check - ensure invoice belongs to admin's tenant
        if ($invoice->tenant_id !== $admin->tenant_id) {
            abort(403, 'Unauthorized action.');
        }

        // Eager load relationships
        $invoice->load(['subscription.plan', 'payments', 'tenant']);
        
        $tenant = $admin->tenant;
        $currency = config('app.currency', 'USD');

        $payfastForm = $this->payfastGatewayService->buildPayfastForm($invoice);

        return view('portal.invoices.show', compact('invoice', 'admin', 'tenant', 'currency', 'payfastForm'));
    }

    /**
     * Download the invoice as PDF
     */
    public function downloadInvoice(SubscriptionInvoice $invoice)
    {
        $admin = Auth::guard('tenant_admin')->user();
        
        if (!$admin->canManageBilling()) {
            return redirect()->route('portal.dashboard')
                ->with('error', 'You do not have permission to download invoices.');
        }

        // Authorization check - ensure invoice belongs to admin's tenant
        if ($invoice->tenant_id !== $admin->tenant_id) {
            abort(403, 'Unauthorized action.');
        }

        // Eager load relationships
        $invoice->load(['subscription.plan', 'tenant']);
        
        $tenant = $invoice->tenant;
        $currency = config('app.currency', 'USD');

        // Generate PDF
        $pdf = Pdf::loadView('portal.invoices.pdf', compact('invoice', 'tenant', 'currency'));

        // Configure PDF options
        $pdf->setPaper('A4', 'portrait');
        $pdf->getDomPDF()->set_option("enable_php", true);
        $pdf->getDomPDF()->set_option("enable_remote", true);
        $pdf->getDomPDF()->set_option("enable_html5_parser", true);

        // Generate filename
        $filename = sprintf('invoice-%s-%s.pdf', 
            $invoice->invoice_number,
            now()->format('Y-m-d_His')
        );

        return $pdf->download($filename);
    }

    /**
     * Print view of the invoice
     */
    public function printInvoice(SubscriptionInvoice $invoice)
    {
        $admin = Auth::guard('tenant_admin')->user();
        
        if (!$admin->canManageBilling()) {
            return redirect()->route('portal.dashboard')
                ->with('error', 'You do not have permission to print invoices.');
        }

        // Authorization check - ensure invoice belongs to admin's tenant
        if ($invoice->tenant_id !== $admin->tenant_id) {
            abort(403, 'Unauthorized action.');
        }

        // Eager load relationships
        $invoice->load(['subscription.plan', 'tenant']);
        
        $tenant = $invoice->tenant;
        $currency = config('app.currency', 'USD');

        $payfastForm = $this->payfastGatewayService->buildPayfastForm($invoice);

        return view('portal.invoices.print', compact('invoice', 'admin', 'tenant', 'currency', 'payfastForm'));
    }
}
