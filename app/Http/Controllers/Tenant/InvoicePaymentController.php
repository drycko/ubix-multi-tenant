<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\BookingInvoice;
use App\Models\Tenant\InvoicePayment;
use App\Traits\LogsTenantUserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoicePaymentController extends Controller
{
    use LogsTenantUserActivity;

    /**
     * Display a listing of payments for an invoice.
     */
    public function index(BookingInvoice $bookingInvoice)
    {
        // Authorization check
        if ($bookingInvoice->property_id !== selected_property_id()) {
            abort(403, 'Unauthorized action.');
        }

        $payments = $bookingInvoice->invoicePayments()
            ->with('recordedBy')
            ->orderBy('payment_date', 'desc')
            ->paginate(15);

        $currency = property_currency();

        return view('tenant.invoice-payments.index', compact('bookingInvoice', 'payments', 'currency'));
    }

    /**
     * Show the form for creating a new payment.
     */
    public function create(BookingInvoice $bookingInvoice)
    {
        // Authorization check
        if ($bookingInvoice->property_id !== selected_property_id()) {
            abort(403, 'Unauthorized action.');
        }

        $paymentMethods = InvoicePayment::getPaymentMethods();
        $currency = property_currency();
        
        // Get primary guest from booking for default selection
        $defaultGuest = null;
        if ($bookingInvoice->booking) {
            $primaryGuestBooking = $bookingInvoice->booking->bookingGuests()->first();
            if ($primaryGuestBooking) {
                $defaultGuest = $primaryGuestBooking->guest;
            }
        }

        return view('tenant.invoice-payments.create', compact('bookingInvoice', 'paymentMethods', 'currency', 'defaultGuest'));
    }

    /**
     * Store a newly created payment in storage.
     */
    public function store(Request $request, BookingInvoice $bookingInvoice)
    {
        // Authorization check
        if ($bookingInvoice->property_id !== selected_property_id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'guest_id' => 'nullable|exists:guests,id',
            'payment_method' => 'required|string|max:50',
            'amount' => 'required|numeric|min:0.01|max:' . $bookingInvoice->remaining_balance,
            'payment_date' => 'required|date|before_or_equal:today',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|in:completed,pending',
        ]);

        DB::beginTransaction();
        try {
            // Create the payment
            $payment = InvoicePayment::create([
                'property_id' => selected_property_id(),
                'booking_invoice_id' => $bookingInvoice->id,
                'guest_id' => $validated['guest_id'], // Will auto-populate from booking if null
                'payment_method' => $validated['payment_method'],
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'reference_number' => $validated['reference_number'],
                'notes' => $validated['notes'],
                'status' => $validated['status'],
                'recorded_by' => auth()->id(),
            ]);

            // Log the activity
            $this->logTenantActivity(
                'record_payment',
                'Recorded payment of ' . property_currency() . ' ' . number_format($payment->amount, 2) . ' for invoice: ' . $bookingInvoice->invoice_number,
                $payment,
                [
                    'table' => 'invoice_payments',
                    'id' => $payment->id,
                    'invoice_id' => $bookingInvoice->id,
                    'amount' => $payment->amount,
                    'payment_method' => $payment->payment_method,
                ]
            );

            DB::commit();

            return redirect()
                ->route('tenant.booking-invoices.show', $bookingInvoice)
                ->with('success', 'Payment recorded successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Payment recording failed: ' . $e->getMessage());
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to record payment. Please try again.']);
        }
    }

    /**
     * Display the specified payment.
     */
    public function show(BookingInvoice $bookingInvoice, InvoicePayment $invoicePayment)
    {
        // Authorization check
        if ($bookingInvoice->property_id !== selected_property_id() || 
            $invoicePayment->property_id !== selected_property_id()) {
            abort(403, 'Unauthorized action.');
        }

        $invoicePayment->load('recordedBy');
        $currency = property_currency();

        return view('tenant.invoice-payments.show', compact('bookingInvoice', 'invoicePayment', 'currency'));
    }

    /**
     * Show the form for editing the specified payment.
     */
    public function edit(BookingInvoice $bookingInvoice, InvoicePayment $invoicePayment)
    {
        // Authorization check
        if ($bookingInvoice->property_id !== selected_property_id() || 
            $invoicePayment->property_id !== selected_property_id()) {
            abort(403, 'Unauthorized action.');
        }

        $paymentMethods = InvoicePayment::getPaymentMethods();
        $currency = property_currency();

        return view('tenant.invoice-payments.edit', compact('bookingInvoice', 'invoicePayment', 'paymentMethods', 'currency'));
    }

    /**
     * Update the specified payment in storage.
     */
    public function update(Request $request, BookingInvoice $bookingInvoice, InvoicePayment $invoicePayment)
    {
        // Authorization check
        if ($bookingInvoice->property_id !== selected_property_id() || 
            $invoicePayment->property_id !== selected_property_id()) {
            abort(403, 'Unauthorized action.');
        }

        // Calculate max amount (original amount + remaining balance)
        $maxAmount = $invoicePayment->amount + $bookingInvoice->remaining_balance;

        $validated = $request->validate([
            'payment_method' => 'required|string|max:50',
            'amount' => 'required|numeric|min:0.01|max:' . $maxAmount,
            'payment_date' => 'required|date|before_or_equal:today',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|in:completed,pending,failed',
        ]);

        DB::beginTransaction();
        try {
            $oldAmount = $invoicePayment->amount;
            $oldStatus = $invoicePayment->status;

            // Update the payment
            $invoicePayment->update($validated);

            // Log the activity
            $this->logTenantActivity(
                'update_payment',
                'Updated payment for invoice: ' . $bookingInvoice->invoice_number,
                $invoicePayment,
                [
                    'table' => 'invoice_payments',
                    'id' => $invoicePayment->id,
                    'old_amount' => $oldAmount,
                    'new_amount' => $invoicePayment->amount,
                    'old_status' => $oldStatus,
                    'new_status' => $invoicePayment->status,
                ]
            );

            DB::commit();

            return redirect()
                ->route('tenant.booking-invoices.show', $bookingInvoice)
                ->with('success', 'Payment updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update payment. Please try again.']);
        }
    }

    /**
     * Remove the specified payment from storage.
     */
    public function destroy(BookingInvoice $bookingInvoice, InvoicePayment $invoicePayment)
    {
        // Authorization check
        if ($bookingInvoice->property_id !== selected_property_id() || 
            $invoicePayment->property_id !== selected_property_id()) {
            abort(403, 'Unauthorized action.');
        }

        DB::beginTransaction();
        try {
            // Log the activity before deletion
            $this->logTenantActivity(
                'delete_payment',
                'Deleted payment of ' . property_currency() . ' ' . number_format($invoicePayment->amount, 2) . ' for invoice: ' . $bookingInvoice->invoice_number,
                $invoicePayment,
                [
                    'table' => 'invoice_payments',
                    'id' => $invoicePayment->id,
                    'amount' => $invoicePayment->amount,
                    'payment_method' => $invoicePayment->payment_method,
                ]
            );

            $invoicePayment->delete();

            DB::commit();

            return redirect()
                ->route('tenant.booking-invoices.show', $bookingInvoice)
                ->with('success', 'Payment deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete payment. Please try again.']);
        }
    }

    /**
     * AJAX endpoint to get payment form for modal.
     */
    public function getPaymentForm(BookingInvoice $bookingInvoice)
    {
        // Authorization check
        if ($bookingInvoice->property_id !== selected_property_id()) {
            abort(403, 'Unauthorized action.');
        }

        $paymentMethods = InvoicePayment::getPaymentMethods();
        $currency = property_currency();

        return view('tenant.invoice-payments.partials.payment-form', compact('bookingInvoice', 'paymentMethods', 'currency'));
    }
}