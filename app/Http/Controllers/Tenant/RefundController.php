<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Refund;
use App\Models\Tenant\BookingInvoice;
use App\Models\Tenant\InvoicePayment;
use App\Traits\LogsTenantUserActivity;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class RefundController extends Controller
{
  use LogsTenantUserActivity;

  public function __construct()
  {
    $this->middleware(['auth:tenant', 'permission:view refunds'])->only(['index', 'show']);
    $this->middleware(['auth:tenant', 'permission:create refunds'])->only(['create', 'store']);
    $this->middleware(['auth:tenant', 'permission:edit refunds'])->only(['edit', 'update']);
    $this->middleware(['auth:tenant', 'permission:delete refunds'])->only(['destroy']);
  }

  public function index()
  {
    // Get property context
    $propertyId = selected_property_id();
    $refunds = Refund::latest()->paginate(20);
    $currency = property_currency();
    return view('tenant.refunds.index', compact('refunds', 'currency'));
  }
  
  public function create()
  {
    // Get property context
    $propertyId = selected_property_id();
    
    $invoices = BookingInvoice::where('property_id', $propertyId)->get();
    // get only invoices that total_paid is more than 0
    $invoices = $invoices->filter(function ($invoice) {
      return $invoice->total_paid > 0;
    });
    $payments = $invoices->flatMap->invoicePayments;
    // get only payments that are completed with total_refunded less than amount, and set refundable_amount attribute
    $payments = $payments->filter(function ($payment) {
      $payment->refundable_amount = $payment->amount - $payment->refunded_amount;
      return $payment->status === 'completed' && $payment->refundable_amount > 0;
    });
    $currency = property_currency();

    return view('tenant.refunds.create', compact('invoices', 'payments', 'currency'));
  }
  
  public function store(Request $request)
  {
    try {
      $request->validate([
        'payment_id' => 'required|exists:invoice_payments,id',
        'amount' => 'required|numeric|min:0.01',
        'reason' => 'required|string',
        'status' => 'required|in:pending,approved,rejected',
      ]);

      DB::beginTransaction();

      // let us make sure the payment selected exists and belongs to the property
      if ($request->payment_id) {
        $propertyId = selected_property_id();
        // check if the payment_id belongs to the invoice
        if ($request->payment_id) {
          $payment = InvoicePayment::where('id', $request->payment_id)->where('property_id', $propertyId)->first();
          if (!$payment) {
            DB::rollBack();
            return redirect()->back()->withErrors(['payment_id' => 'The selected payment does not belong to the selected invoice.'])->withInput();
          }
        }
        $invoiceId = $payment->booking_invoice_id;
        $invoice = BookingInvoice::where('property_id', $propertyId)->where('id', $invoiceId)->first();
        if (!$invoice) {
          DB::rollBack();
          return redirect()->back()->withErrors(['invoice_id' => 'The selected invoice is invalid.'])->withInput();
        }
        // Check that the refund amount does not exceed the paid amount
        $totalPaid = $invoice->total_paid;
        if ($request->amount > $totalPaid) {
          DB::rollBack();
          return redirect()->back()->withErrors(['amount' => 'The refund amount cannot exceed the total paid amount of the invoice.'])->withInput();
        }
      }
      else {
        DB::rollBack();
        return redirect()->back()->withErrors(['payment_id' => 'Payment ID is required.'])->withInput();
      }

      // Create refund record
      $refund = Refund::create([
        'invoice_id' => $invoiceId,
        'payment_id' => $request->payment_id,
        'user_id' => auth()->id(),
        'amount' => $request->amount,
        'reason' => $request->reason,
        'status' => $request->status ?? 'pending',
      ]);

      // Log the activity
      $this->logTenantActivity(
          'create_refund',
          'Created refund: ' . $refund->id,
          $refund,
          [
              'table' => 'refunds',
              'id' => $refund->id,
              'user_id' => auth()->id(),
              'action' => 'create'
          ]
      );
      DB::commit();

      // TODO: Integrate with payment gateway API or log manual refund
      return redirect()->route('tenant.refunds.index')->with('success', 'Refund request submitted.');
    } catch (\Exception $e) {
      DB::rollBack();
      \Log::error('Refund processing error: ' . $e->getMessage());
      return redirect()->back()->withErrors(['error' => 'An error occurred while processing the refund: ' . $e->getMessage()])->withInput();
    }
  }
  
  public function show(Refund $refund)
  {
    // Get property context
    $propertyId = selected_property_id();
    
    $invoices = BookingInvoice::where('property_id', $propertyId)->get();
    // get only invoices that total_paid is more than 0
    $invoices = $invoices->filter(function ($invoice) {
      return $invoice->total_paid > 0;
    });
    $payments = $invoices->flatMap->invoicePayments;
    // get only payments that are completed with total_refunded less than amount, and set refundable_amount attribute
    $payments = $payments->filter(function ($payment) {
      $payment->refundable_amount = $payment->amount - $payment->refunded_amount;
      return $payment->status === 'completed' && $payment->refundable_amount > 0;
    });
    $currency = property_currency();

    return view('tenant.refunds.show', compact('refund', 'currency', 'invoices', 'payments'));
  }
  
  public function edit(Refund $refund)
  {
    // Get property context
    $propertyId = selected_property_id();
    
    $invoices = BookingInvoice::where('property_id', $propertyId)->get();
    // get only invoices that total_paid is more than 0
    $invoices = $invoices->filter(function ($invoice) {
      return $invoice->total_paid > 0;
    });
    $payments = $invoices->flatMap->invoicePayments;
    // get only payments that are completed with total_refunded less than amount, and set refundable_amount attribute
    $payments = $payments->filter(function ($payment) {
      $payment->refundable_amount = $payment->amount - $payment->refunded_amount;
      return $payment->status === 'completed' && $payment->refundable_amount > 0;
    });
    $currency = property_currency();
    
    return view('tenant.refunds.edit', compact('refund', 'currency', 'invoices', 'payments'));
  }
  
  public function update(Request $request, Refund $refund)
  {
    try {
      $request->validate([
        // 'payment_id' => 'required|exists:invoice_payments,id',
        'amount' => 'required|numeric|min:0.01',
        'reason' => 'required|string',
        'status' => 'required|in:pending,approved,declined',
      ]);

      DB::beginTransaction();
      // let us make sure the payment selected exists and belongs to the property
      // if ($request->payment_id) {
      //   $propertyId = selected_property_id();
      //   // check if the payment_id belongs to the invoice
      //   if ($request->payment_id) {
      //     $payment = InvoicePayment::where('id', $request->payment_id)->where('property_id', $propertyId)->first();
      //     if (!$payment) {
      //       DB::rollBack();
      //       return redirect()->back()->withErrors(['payment_id' => 'The selected payment does not belong to the selected invoice.'])->withInput();
      //     }
      //   }
      //   $invoiceId = $payment->booking_invoice_id;
      //   $invoice = BookingInvoice::where('property_id', $propertyId)->where('id', $invoiceId)->first();
      //   if (!$invoice) {
      //     DB::rollBack();
      //     return redirect()->back()->withErrors(['invoice_id' => 'The selected invoice is invalid.'])->withInput();
      //   }
      //   // Check that the refund amount does not exceed the paid amount
      //   $totalPaid = $invoice->total_paid;
      //   if ($request->amount > $totalPaid) {
      //     DB::rollBack();
      //     return redirect()->back()->withErrors(['amount' => 'The refund amount cannot exceed the total paid amount of the invoice.'])->withInput();
      //   }
      // }
      // else {
      //   DB::rollBack();
      //   return redirect()->back()->withErrors(['payment_id' => 'Payment ID is required.'])->withInput();
      // }
      $refund->update($request->only(['amount', 'reason', 'status']));
      // Log the activity
      $this->logTenantActivity(
          'update_refund',
          'Updated refund: ' . $refund->id,
          $refund,
          [
              'table' => 'refunds',
              'id' => $refund->id,
              'user_id' => auth()->id(),
              'action' => 'update'
          ]
      );
      DB::commit();
      return redirect()->route('tenant.refunds.show', $refund)->with('success', 'Refund updated.');
    } catch (\Exception $e) {
      DB::rollBack();
      \Log::error('Refund update error: ' . $e->getMessage());
      return redirect()->back()->withErrors(['error' => 'An error occurred while updating the refund: ' . $e->getMessage()])->withInput();
    }
  }
  
  public function destroy(Refund $refund)
  {
    $refund->delete();
    return redirect()->route('tenant.refunds.index')->with('success', 'Refund deleted.');
  }
}
