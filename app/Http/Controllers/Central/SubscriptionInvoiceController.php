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
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // use the central database connection from here because I am in the central app
        config(['database.connections.tenant' => config('database.connections.central')]);
        
        // Display a listing of subscription invoices with their relationships
        $invoices = SubscriptionInvoice::with(['tenant', 'subscription'])->orderBy('created_at', 'desc')->paginate(15);
        return view('central.invoices.index', compact('invoices'));
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
        // use the central database connection from here because I am in the central app
        config(['database.connections.tenant' => config('database.connections.central')]);

        // user can view the invoice based on permissions
        if (!auth()->user()->can('view invoices')) {
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
        //
    }

    /**
     * Display the print view of the invoice.
     */
    public function print($id)
    {
        // use the central database connection from here because I am in the central app
        config(['database.connections.tenant' => config('database.connections.central')]);

        // user can view the invoice based on permissions
        if (!auth()->user()->can('view invoices')) {
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
        // use the central database connection from here because I am in the central app
        config(['database.connections.tenant' => config('database.connections.central')]);

        try {
            $validate = $request->validate([
                'amount' => 'required|numeric|min:0',
                'payment_method' => 'required|string|max:255|in:' . implode(',', SubscriptionInvoice::INVOICE_PAYMENT_METHODS),
                'transaction_id' => 'nullable|string|max:255',
                'notes' => 'nullable|string|max:1000',
            ]);

            // start a transaction
            \DB::beginTransaction();
            
            $invoice = SubscriptionInvoice::with('subscription')->findOrFail($id);

            // validate amount is not more than invoice amount
            if ($validate['amount'] > $invoice->amount) {
                return redirect()->route('central.invoices.show', $invoice)->with('error', 'Payment amount is more than invoice amount.');
            }

            // Create payment record
            SubscriptionPayment::create([
                'subscription_id' => $invoice->subscription->id,
                'invoice_id' => $invoice->id,
                'amount' => $validate['amount'],
                'payment_date' => now(),
                'payment_method' => $validate['payment_method'],
                'notes' => $validate['notes'] ?? null,
                'transaction_id' => $validate['transaction_id'] ?? null,
                'status' => 'completed',
            ]);

            // Mark the invoice as paid if full amount is paid
            if ($validate['amount'] === $invoice->amount) {
                $invoice->markAsPaid();

                // if the subscription was not active
                if ($invoice->subscription->status !== 'active') {
                    // deactivate all other subscriptions for this tenant
                    $oldSubs = Subscription::where('tenant_id', $invoice->subscription->tenant_id)
                        ->whereIn('status', ['active', 'trial', 'expired']);
                    $oldSubs->where('id', '!=', $invoice->subscription->id)
                        ->update(['status' => 'canceled']);
                    // update start and end date of the subscription based on billing cycle (yearly, monthly)
                    $start_date = now();
                    $end_date = $invoice->subscription->billing_cycle == 'yearly' ? now()->addYear() : now()->addMonth();
                    $invoice->subscription->update(['status' => 'active', 'start_date' => $start_date, 'end_date' => $end_date]);
                }
                
                // Log the completion of payment
                $this->logAdminActivity(
                    "update",
                    "subscription_invoices",
                    $invoice->id,
                    "Invoice #{$invoice->invoice_number} marked as fully paid"
                );
            } else {
                $invoice->update(['status' => 'partially_paid']);
                
                // Log the partial payment
                $this->logAdminActivity(
                    "update",
                    "subscription_invoices",
                    $invoice->id,
                    "Partial payment of {$validate['amount']} received for invoice #{$invoice->invoice_number}"
                );
            }

            \DB::commit();

            $this->logAdminActivity(
                "update",
                "subscription_invoices",
                $invoice->id,
                "Manually paid invoice #{$invoice->invoice_number}"
            );
            $this->createAdminNotification("Invoice #{$invoice->invoice_number} was manually paid");
            
            return redirect()->route('central.invoices.show', $invoice)->with('success', 'Invoice paid successfully.');
        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->route('central.invoices.show', $id)->with('error', 'Error paying invoice: ' . $e->getMessage());
        }
    }

    /**
     * Download the invoice as PDF.
     */
    public function download($id)
    {
        // use the central database connection from here because I am in the central app
        config(['database.connections.tenant' => config('database.connections.central')]);

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
}
