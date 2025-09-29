<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPayment;
use App\Traits\LogsAdminActivity;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    use LogsAdminActivity;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // use the central database connection from here because I am in the central app
        config(['database.connections.tenant' => config('database.connections.central')]);
        
        // Fetch all subscriptions with their associated plans and tenants
        $subscriptions = Subscription::with(['plan', 'tenant'])->paginate(10);
        $subscriptions->getCollection()->transform(function ($subscription) {
            $subscription->plan = SubscriptionPlan::find($subscription->subscription_plan_id) ?? null;
            return $subscription;
        });
        $currency = config('app.currency', 'USD');
        return view('central.subscriptions.index', compact('subscriptions', 'currency'));
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
    public function show(Subscription $subscription)
    {
        // use the central database connection from here because I am in the central app
        config(['database.connections.tenant' => config('database.connections.central')]);

        $subscription->plan = SubscriptionPlan::find($subscription->subscription_plan_id) ?? null;
        $subscription->hasOutstandingInvoices = $subscription->hasOutstandingInvoices();

        $paymentMethods = SubscriptionInvoice::INVOICE_PAYMENT_METHODS;
        $subscriptionInvoices = SubscriptionInvoice::where('subscription_id', $subscription->id)->get();
        $subscription->invoices = $subscriptionInvoices;
        
        // validate the plan if the plan is found
        if (!$subscription->plan) {
            return redirect()->route('central.subscriptions.show', $subscription)->with('error', 'Subscription plan not found.');
        }

        $currency = config('app.currency', 'USD');
        return view('central.subscriptions.show', compact('subscription', 'currency', 'paymentMethods'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Subscription $subscription)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Subscription $subscription)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subscription $subscription)
    {
        // use the central database connection from here because I am in the central app
        config(['database.connections.tenant' => config('database.connections.central')]);

        $subscription->delete();
        
        $this->logAdminActivity(
            'delete',
            'subscriptions',
            $subscription->id,
            "Deleted subscription #{$subscription->id}"
        );
        $this->createAdminNotification("Subscription #{$subscription->id} was deleted");
    }

    /**
     * Renew the specified subscription.
     */
    public function renew(Request $request, Subscription $subscription)
    {
        // use the central database connection from here because I am in the central app
        config(['database.connections.tenant' => config('database.connections.central')]);

        $request->validate([
            'new_end_date' => 'required|date|after:today',
        ]);

        $oldEndDate = $subscription->end_date;
        $subscription->renew($request->input('new_end_date'));
        
        $this->logAdminActivity(
            'update',
            'subscriptions',
            $subscription->id,
            "Renewed subscription #{$subscription->id} from {$oldEndDate} until " . $request->input('new_end_date')
        );
        $this->createAdminNotification("Subscription #{$subscription->id} was renewed until " . $request->input('new_end_date'));

        return redirect()->route('central.subscriptions.show', $subscription)->with('success', 'Subscription renewed successfully.');
    }

    /**
     * Pay the invoice for the specified subscription.
     */
    public function payInvoice(Request $request, Subscription $subscription)
    {
        // use the central database connection from here because I am in the central app
        config(['database.connections.tenant' => config('database.connections.central')]);
        // Logic to pay the invoice
        // This is a placeholder implementation
        // In a real application, you would integrate with a payment gateway

        // For demonstration, let's assume the payment is always successful
        $subscription->invoices()->where('status', 'pending')->each(function ($invoice) use ($subscription) {
            $invoice->markAsPaid();
            
            $this->logAdminActivity(
                "update",
                "subscription_invoices",
                $invoice->id,
                "Processed payment for invoice #{$invoice->invoice_number} for subscription #{$subscription->id}"
            );
            $this->createAdminNotification("Payment processed for invoice #{$invoice->invoice_number}");
        });

        return redirect()->route('central.subscriptions.show', $subscription)->with('success', 'Invoice paid successfully.');
    }

    /**
     * Cancel the specified subscription.
     */
    public function cancel(Subscription $subscription)
    {
        // use the central database connection from here because I am in the central app
        config(['database.connections.tenant' => config('database.connections.central')]);
        // validation to check if the subscription is already canceled
        if ($subscription->status === 'canceled') {
            return redirect()->route('central.subscriptions.show', $subscription)->with('error', 'Subscription is already canceled.');
        }
        
        $subscription->cancel();
        
        $this->logAdminActivity(
            "update",
            "subscriptions",
            $subscription->id,
            "Canceled subscription #{$subscription->id}"
        );
        $this->createAdminNotification("Subscription #{$subscription->id} was canceled");

        return redirect()->route('central.subscriptions.show', $subscription)->with('success', 'Subscription canceled successfully.');
    }
}
