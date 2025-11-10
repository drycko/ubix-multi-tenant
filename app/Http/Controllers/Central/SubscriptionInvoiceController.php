<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPayment;
use App\Models\Subscription;
use App\Traits\LogsAdminActivity;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;


class SubscriptionInvoiceController extends Controller
{
    use LogsAdminActivity;
  
    public function __construct()
    {
        // use the central database connection from here because I am in the central app
        config(['database.connections.tenant' => config('database.connections.central')]);
        $this->middleware('auth:web');
        // TODO: Add permission middleware when central permissions are implemented
        $this->middleware('permission:view subscription invoices')->only(['index', 'show']);
        $this->middleware('permission:manage subscription invoices')->only(['destroy', 'payInvoiceManually', 'cancel']);
        $this->middleware('permission:download subscription invoices')->only(['download', 'print']);
        $this->middleware('permission:view trashed data')->only(['trashed']);
        $this->middleware('permission:restore trashed data')->only(['restore']);
        $this->middleware('permission:force delete trashed data')->only(['forceDelete']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // calculate and store taxes for existing invoices that do not have tax fields populated and add currency (do this for deleted invoices as well)
        SubscriptionInvoice::withTrashed()->whereNull('tax_id')->orWhereNull('currency')->each(function ($invoice) {
            // default tax is the first tax found in the taxes table
            $defaultTax = \App\Models\Tax::first();
            $defaultTaxRate = $defaultTax ? $defaultTax->rate : 0;
            $defaultTaxName = $defaultTax ? $defaultTax->name : 'No Tax';
            $defaultTaxType = $defaultTax ? $defaultTax->type : 'percentage';
            $defaultCurrency = config('app.currency', 'USD');


            $subtotal = $invoice->amount / (1 + ($defaultTaxRate / 100));
            $taxAmount = $invoice->amount - $subtotal;

            $invoice->subtotal_amount = round($subtotal, 2);
            $invoice->tax_amount = round($taxAmount, 2);
            $invoice->tax_rate = $defaultTaxRate;
            $invoice->tax_name = $defaultTaxName;
            $invoice->tax_type = $defaultTaxType;
            $invoice->tax_inclusive = true; // assuming existing invoices are tax inclusive
            $invoice->currency = $defaultCurrency;
            // Here we would normally link to a tax record, but for this example, we will leave it null
            $invoice->save();
        });
        // delete invoices without relationships
        SubscriptionInvoice::doesntHave('tenant')->orDoesntHave('subscription')->each(function ($invoice) {
            $this->logAdminActivity(
                'delete',
                'subscription_invoices',
                $invoice->id,
                "Deleted invoice #{$invoice->invoice_number} due to missing relationships"
            );
            $invoice->delete();
        });
        
        // Display a listing of subscription invoices with their relationships
        $query = SubscriptionInvoice::with(['tenant', 'subscription.plan']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('tenant', function($tenantQuery) use ($search) {
                      $tenantQuery->where('name', 'like', "%{$search}%")
                                  ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->filled('from_date')) {
            $query->whereDate('invoice_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('invoice_date', '<=', $request->to_date);
        }

        $currency = config('app.currency', 'USD');

        $invoices = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        
        return view('central.invoices.index', compact('invoices', 'currency'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // not needed
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // not needed
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {

        // user can view the invoice based on permissions
        if (!auth()->user()->can('view subscription invoices')) {
            return redirect()->route('central.invoices.index')->with('error', 'You do not have permission to view this invoice.');
        }

        $invoice = SubscriptionInvoice::with(['tenant', 'subscription'])->where('id', $id)->first();
        
        if (!$invoice) {
            return redirect()->route('central.invoices.index')->with('error', 'Invoice not found');
        }

        // Fetch application details from central settings
        $app_name = config('app.name_long', 'Laravel Application');
        $app_email = config('app.email', 'info@nexusflow.com');
        $app_address_line_1 = config('app.address_line_1', '1234 Main St');
        $app_address_line_2 = config('app.address_line_2', 'Suite 100');
        $app_address_city = config('app.address_city', 'Anytown');
        $app_address_state = config('app.address_state', 'CA');
        $app_address_zip = config('app.address_zip', '12345');

        // invoice tenant billing details
        if (!$invoice->tenant) {
            return redirect()->route('central.invoices.index')->with('error', 'Invoice tenant not found');
        }
        
        $tenant_name = $invoice->tenant->name;
        $tenant_email = $invoice->tenant->email;
        // tenant address is stored in the tenants table like this(13 Main Street, Cape Town, South Africa, 8001) so we should split it by commas
        $tenant_address = $invoice->tenant->address ? explode(',', $invoice->tenant->address) : [];
        $tenant_address_line_1 = $tenant_address[0] ?? '';
        $tenant_address_line_2 = $tenant_address[1] ?? '';
        $tenant_address_city = $tenant_address[2] ?? '';
        $tenant_address_state = $tenant_address[3] ?? '';
        $tenant_address_zip = $tenant_address[4] ?? '';

        $paymentMethods = SubscriptionInvoice::INVOICE_PAYMENT_METHODS;
        $currency = config('app.currency', 'USD');

        return view('central.invoices.show', compact(
            'invoice', 'app_name', 'app_email', 'app_address_line_1', 'app_address_line_2', 
            'app_address_city', 'app_address_state', 'app_address_zip', 'tenant_name', 
            'tenant_email', 'tenant_address_line_1', 'tenant_address_line_2', 
            'tenant_address_city', 'tenant_address_state', 'tenant_address_zip',
            'paymentMethods', 'currency'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SubscriptionInvoice $subscriptionInvoice)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SubscriptionInvoice $subscriptionInvoice)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SubscriptionInvoice $subscriptionInvoice)
    {
        try {
            // delete only if invoice is not paid
            if (in_array($subscriptionInvoice->status, ['paid', 'cancelled'])) {
                return redirect()->route('central.invoices.index')->with('error', 'Only pending, partially paid, or overdue invoices can be deleted. Cancel or refund the invoice first.');
            }
            $invoiceNumber = $subscriptionInvoice->invoice_number;
            $subscriptionInvoice->delete();
            $this->logAdminActivity(
                'delete',
                'subscription_invoices',
                $subscriptionInvoice->id,
                "Deleted invoice #{$invoiceNumber}"
            );
            return redirect()->route('central.invoices.index')->with('success', 'Invoice deleted successfully.');
        } catch (\Exception $e) {
            \Log::error('Error deleting invoice: ' . $e->getMessage());
            return redirect()->route('central.invoices.index')->with('error', 'An error occurred while deleting the invoice.');
        }
    }

    /**
     * trashed invoices
     */
    public function trashed()
    {
        $invoices = SubscriptionInvoice::onlyTrashed()->with(['tenant', 'subscription.plan'])->orderBy('deleted_at', 'desc')->paginate(15)->withQueryString();
        
        return view('central.invoices.trashed', compact('invoices'));
    }

    /**
     * Restore a trashed invoice.
     */
    public function restore($id)
    {
        try {
            $invoice = SubscriptionInvoice::onlyTrashed()->findOrFail($id);
            $invoice->restore();
            $this->logAdminActivity(
                'restore',
                'subscription_invoices',
                $invoice->id,
                "Restored invoice #{$invoice->invoice_number}"
            );
            return redirect()->route('central.invoices.trashed')->with('success', 'Invoice restored successfully.');
        } catch (\Exception $e) {
            \Log::error('Error restoring invoice: ' . $e->getMessage());
            return redirect()->route('central.invoices.trashed')->with('error', 'An error occurred while restoring the invoice.');
        }
    }

    /**
     * Permanently delete a trashed invoice.
     */
    public function forceDelete($id)
    {
        try {
            $invoice = SubscriptionInvoice::onlyTrashed()->findOrFail($id);
            $invoice->forceDelete();
            $this->logAdminActivity(
                'force_delete',
                'subscription_invoices',
                $invoice->id,
                "Permanently deleted invoice #{$invoice->invoice_number}"
            );
            return redirect()->route('central.invoices.trashed')->with('success', 'Invoice permanently deleted successfully.');
        } catch (\Exception $e) {
            \Log::error('Error permanently deleting invoice: ' . $e->getMessage());
            return redirect()->route('central.invoices.trashed')->with('error', 'An error occurred while permanently deleting the invoice.');
        }
    }

    /**
     * Display the print view of the invoice.
     */
    public function print($id)
    {

        // user can view the invoice based on permissions
        if (!auth()->user()->can('view subscription invoices')) {
            return redirect()->route('central.invoices.index')->with('error', 'You do not have permission to view this invoice.');
        }

        $invoice = SubscriptionInvoice::with(['tenant', 'subscription'])->where('id', $id)->first();
        
        if (!$invoice) {
            return redirect()->route('central.invoices.index')->with('error', 'Invoice not found');
        }

        // Fetch application details from central settings
        $app_name = config('app.name_long', 'Laravel Application');
        $app_email = config('app.email', 'info@nexusflow.com');
        $app_address_line_1 = config('app.address_line_1', '1234 Main St');
        $app_address_line_2 = config('app.address_line_2', 'Suite 100');
        $app_address_city = config('app.address_city', 'Anytown');
        $app_address_state = config('app.address_state', 'CA');
        $app_address_zip = config('app.address_zip', '12345');

        // invoice tenant billing details
        if (!$invoice->tenant) {
            return redirect()->route('central.invoices.index')->with('error', 'Invoice tenant not found');
        }
        
        $tenant_name = $invoice->tenant->name;
        $tenant_email = $invoice->tenant->email;
        // tenant address is stored in the tenants table like this(13 Main Street, Cape Town, South Africa, 8001) so we should split it by commas
        $tenant_address = $invoice->tenant->address ? explode(',', $invoice->tenant->address) : [];
        $tenant_address_line_1 = $tenant_address[0] ?? '';
        $tenant_address_line_2 = $tenant_address[1] ?? '';
        $tenant_address_city = $tenant_address[2] ?? '';
        $tenant_address_state = $tenant_address[3] ?? '';
        $tenant_address_zip = $tenant_address[4] ?? '';

        return view('central.invoices.print', compact('invoice', 'app_name', 'app_email', 'app_address_line_1', 'app_address_line_2', 'app_address_city', 'app_address_state', 'app_address_zip', 'tenant_name', 'tenant_email', 'tenant_address_line_1', 'tenant_address_line_2', 'tenant_address_city', 'tenant_address_state', 'tenant_address_zip'));
    }

    /**
     * Pay the invoice manually without a payment gateway.
     */
    public function payInvoiceManually(Request $request, $id)
    {

        try {
            $validate = $request->validate([
                'payment_method' => 'required|string|max:255',
                'payment_reference' => 'nullable|string|max:255',
                'paid_at' => 'required|date',
                'notes' => 'nullable|string|max:1000',
            ]);

            // start a transaction
            \DB::beginTransaction();
            
            $invoice = SubscriptionInvoice::with('subscription')->findOrFail($id);

            // Check if invoice is already paid
            if ($invoice->status === 'paid') {
                return redirect()->route('central.invoices.index')->with('error', 'Invoice is already marked as paid.');
            }

            // Create payment record for the full invoice amount
            SubscriptionPayment::create([
                'subscription_id' => $invoice->subscription->id,
                'invoice_id' => $invoice->id,
                'amount' => $invoice->amount,
                'payment_date' => $validate['paid_at'],
                'payment_method' => $validate['payment_method'],
                'notes' => $validate['notes'] ?? null,
                'transaction_id' => $validate['payment_reference'] ?? null,
                'status' => 'completed',
            ]);

            // Mark the invoice as paid with the provided date and reference
            $invoice->update([
                'status' => 'paid',
                'paid_at' => $validate['paid_at'],
                'payment_reference' => $validate['payment_reference'],
            ]);

            // if the subscription was not active, activate it
            if ($invoice->subscription && $invoice->subscription->status !== 'active') {
                // deactivate all other subscriptions for this tenant
                $oldSubs = Subscription::where('tenant_id', $invoice->subscription->tenant_id)
                    ->whereIn('status', ['active', 'trial', 'expired']);
                $oldSubs->where('id', '!=', $invoice->subscription->id)
                    ->update(['status' => 'canceled']);
                
                // update start and end date of the subscription based on billing cycle
                $start_date = now();
                $end_date = match($invoice->subscription->billing_cycle) {
                    'monthly' => now()->addMonth(),
                    'quarterly' => now()->addMonths(3),
                    'semi_annually' => now()->addMonths(6),
                    'annually' => now()->addYear(),
                    default => now()->addMonth(),
                };
                
                $invoice->subscription->update([
                    'status' => 'active', 
                    'start_date' => $start_date, 
                    'end_date' => $end_date
                ]);
            }

            \DB::commit();

            $this->logAdminActivity(
                "update",
                "subscription_invoices",
                $invoice->id,
                "Manually marked invoice #{$invoice->invoice_number} as paid via {$validate['payment_method']}"
            );
            
            $this->createAdminNotification("Invoice #{$invoice->invoice_number} was manually marked as paid");
            
            return redirect()->route('central.invoices.index')->with('success', "Invoice #{$invoice->invoice_number} has been marked as paid successfully.");
        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->route('central.invoices.index')->with('error', 'Error processing payment: ' . $e->getMessage());
        }
    }

    /**
     * Download the invoice as PDF.
     */
    public function download($id)
    {

        // user can download the invoice based on permissions
        if (!auth()->user()->can('download invoices')) {
            return redirect()->route('central.invoices.index')->with('error', 'You do not have permission to download this invoice.');
        }

        $invoice = SubscriptionInvoice::with(['tenant', 'subscription'])->where('id', $id)->first();
        
        if (!$invoice) {
            return redirect()->route('central.invoices.index')->with('error', 'Invoice not found');
        }

        // Fetch application details from central settings
        $app_name = config('app.name_long', 'Laravel Application');
        $app_email = config('app.email', 'support@nexusflow.co.za');
        $app_address_line_1 = config('app.address_line_1', '1234 Main St');
        $app_address_line_2 = config('app.address_line_2', 'Suite 100');
        $app_address_city = config('app.address_city', 'Anytown');
        $app_address_state = config('app.address_state', 'CA');
        $app_address_zip = config('app.address_zip', '12345');

        // invoice tenant billing details
        if (!$invoice->tenant) {
            return redirect()->route('central.invoices.index')->with('error', 'Invoice tenant not found');
        }
        
        $tenant_name = $invoice->tenant->name;
        $tenant_email = $invoice->tenant->email;
        // tenant address is stored in the tenants table like this(13 Main Street, Cape Town, South Africa, 8001) so we should split it by commas
        $tenant_address = $invoice->tenant->address ? explode(',', $invoice->tenant->address) : [];
        $tenant_address_line_1 = $tenant_address[0] ?? '';
        $tenant_address_line_2 = $tenant_address[1] ?? '';
        $tenant_address_city = $tenant_address[2] ?? '';
        $tenant_address_state = $tenant_address[3] ?? '';
        $tenant_address_zip = $tenant_address[4] ?? '';

        // Log activity
        $this->logAdminActivity(
            'download',                    // activity type
            'subscription_invoices',       // table name
            $invoice->id,                  // record id
            "Downloaded invoice #{$invoice->invoice_number}" // description
        );

        // Generate PDF
        $pdf = Pdf::loadView('central.invoices.print', compact(
            'invoice', 'app_name', 'app_email', 'app_address_line_1', 'app_address_line_2', 
            'app_address_city', 'app_address_state', 'app_address_zip', 'tenant_name', 
            'tenant_email', 'tenant_address_line_1', 'tenant_address_line_2', 
            'tenant_address_city', 'tenant_address_state', 'tenant_address_zip'
        ));

        // Configure PDF options
        $pdf->setPaper('A4', 'portrait');
        $pdf->getDomPDF()->set_option("enable_php", true);
        $pdf->getDomPDF()->set_option("enable_remote", true);
        $pdf->getDomPDF()->set_option("enable_html5_parser", true);

        // Generate a filename with timestamp to prevent caching
        $filename = sprintf('invoice_%s_%s.pdf', 
            $invoice->invoice_number,
            now()->format('Y-m-d_His')
        );

        // Return the PDF as a download with proper headers
        return $pdf->stream($filename, [
            'Attachment' => true,
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }

    /**
     * Cancel an invoice.
     */
    public function cancel(Request $request, $id)
    {
        try {
            $validate = $request->validate([
                'cancellation_reason' => 'required|string|max:1000',
            ]);

            $invoice = SubscriptionInvoice::findOrFail($id);

            // Check if invoice can be cancelled
            if ($invoice->status === 'paid') {
                return redirect()->route('central.invoices.index')->with('error', 'Cannot cancel a paid invoice. Please issue a refund instead.');
            }

            if ($invoice->status === 'cancelled') {
                return redirect()->route('central.invoices.index')->with('error', 'Invoice is already cancelled.');
            }

            // Update invoice status
            $invoice->update([
                'status' => 'cancelled',
                'notes' => ($invoice->notes ? $invoice->notes . "\n\n" : '') . 
                          "Cancelled on " . now()->format('Y-m-d H:i:s') . "\nReason: " . $validate['cancellation_reason']
            ]);

            $this->logAdminActivity(
                "update",
                "subscription_invoices",
                $invoice->id,
                "Cancelled invoice #{$invoice->invoice_number}. Reason: {$validate['cancellation_reason']}"
            );

            $this->createAdminNotification("Invoice #{$invoice->invoice_number} was cancelled");

            return redirect()->route('central.invoices.index')->with('success', "Invoice #{$invoice->invoice_number} has been cancelled successfully.");
        } catch (\Exception $e) {
            return redirect()->route('central.invoices.index')->with('error', 'Error cancelling invoice: ' . $e->getMessage());
        }
    }
}
