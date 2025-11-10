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
  
    public function __construct()
    {
        // use the central database connection from here because I am in the central app
        config(['database.connections.tenant' => config('database.connections.central')]);
        $this->middleware('auth:web');
        // TODO: Add permission middleware when central permissions are implemented
        $this->middleware('permission:view subscriptions')->only(['index', 'show']);
        $this->middleware('permission:manage subscriptions')->only(['destroy', 'renew', 'payInvoice', 'cancel']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
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
        try {
            // delete all associated invoices and their payments
            $subscription->invoices()->whereIn('status', ['pending', 'partially_paid'])->each(function ($invoice) {
                $invoice->deleteWithPayments();
                
                $this->logAdminActivity(
                    "delete",
                    "subscription_invoices",
                    $invoice->id,
                    "Canceled invoice #{$invoice->invoice_number} associated with subscription #{$invoice->subscription_id}"
                );
                $this->createAdminNotification("Invoice #{$invoice->invoice_number} was canceled");
            });
            $subscription->delete();
            
            
            $this->logAdminActivity(
                'delete',
                'subscriptions',
                $subscription->id,
                "Deleted subscription #{$subscription->id}"
            );
            $this->createAdminNotification("Subscription #{$subscription->id} was deleted");

            return redirect()->route('central.subscriptions.index')->with('success', 'Subscription deleted successfully.');
        } catch (\Exception $e) {
            \Log::error('Failed to delete subscription: ' . $e->getMessage());
            return redirect()->route('central.subscriptions.index')->with('error', 'Failed to delete subscription: ' . $e->getMessage());
        }
    }

    /**
     * Trashed resource listing.
     */
    public function trashed()
    {
        $trashedSubscriptions = Subscription::onlyTrashed()->with(['plan', 'tenant'])->paginate(10);
        $currency = config('app.currency', 'USD');
        return view('central.subscriptions.trashed', compact('trashedSubscriptions', 'currency'));
    }

    /**
    * Restore the specified trashed resource.
    */
    public function restore($id)
    {
        try {
            $subscription = Subscription::onlyTrashed()->findOrFail($id);
            $subscription->restore();
            
            $this->logAdminActivity(
                'restore',
                'subscriptions',
                $subscription->id,
                "Restored subscription #{$subscription->id}"
            );
            $this->createAdminNotification("Subscription #{$subscription->id} was restored");

            return redirect()->route('central.subscriptions.trashed')->with('success', 'Subscription restored successfully.');
        } catch (\Exception $e) {
            \Log::error('Failed to restore subscription: ' . $e->getMessage());
            return redirect()->route('central.subscriptions.trashed')->with('error', 'Failed to restore subscription: ' . $e->getMessage());
        }
    }

    /**
     * Force delete the specified trashed resource.
     */
    public function forceDelete($id)
    {
        try {
            $subscription = Subscription::onlyTrashed()->findOrFail($id);
            $subscription->forceDelete();
            
            $this->logAdminActivity(
                'force_delete',
                'subscriptions',
                $subscription->id,
                "Permanently deleted subscription #{$subscription->id}"
            );
            $this->createAdminNotification("Subscription #{$subscription->id} was permanently deleted");

            return redirect()->route('central.subscriptions.trashed')->with('success', 'Subscription permanently deleted successfully.');
        } catch (\Exception $e) {
            \Log::error('Failed to permanently delete subscription: ' . $e->getMessage());
            return redirect()->route('central.subscriptions.trashed')->with('error', 'Failed to permanently delete subscription: ' . $e->getMessage());
        }
    }

    /**
     * Renew the specified subscription.
     */
    public function renew(Request $request, Subscription $subscription)
    {
        try {

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
        } catch (\Exception $e) {
            \Log::error('Failed to renew subscription: ' . $e->getMessage());
            return redirect()->route('central.subscriptions.show', $subscription)->with('error', 'Failed to renew subscription: ' . $e->getMessage());
        }
    }

    /**
     * Pay the invoice for the specified subscription.
     */
    public function payInvoice(Request $request, Subscription $subscription)
    {
        try {
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
        } catch (\Exception $e) {
            \Log::error('Failed to pay invoice: ' . $e->getMessage());
            return redirect()->route('central.subscriptions.show', $subscription)->with('error', 'Failed to pay invoice: ' . $e->getMessage());
        }
    }

    /**
     * Cancel the specified subscription.
     */
    public function cancel(Subscription $subscription)
    {
        try {
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
        } catch (\Exception $e) {
            \Log::error('Failed to cancel subscription: ' . $e->getMessage());
            return redirect()->route('central.subscriptions.show', $subscription)->with('error', 'Failed to cancel subscription: ' . $e->getMessage());
        }
    }
}
