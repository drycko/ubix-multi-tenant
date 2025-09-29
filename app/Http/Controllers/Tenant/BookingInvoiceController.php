<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\BookingInvoice;
use Illuminate\Http\Request;

class BookingInvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function show(BookingInvoice $bookingInvoice)
    {
        // Show the details of the specified booking invoice
        return view('tenant.booking-invoices.show', compact('bookingInvoice'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BookingInvoice $bookingInvoice)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BookingInvoice $bookingInvoice)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BookingInvoice $bookingInvoice)
    {
        //
    }

    /**
     *  generate unique invoice number.
     */
    private function generate_unique_invoice_number($invoiceNumber)
    {
        $existingInvoice = BookingInvoice::where('invoice_number', $invoiceNumber)
            ->where('property_id', current_property()->id)
            ->first();

        if ($existingInvoice) {
            // If it exists, we need to increment the number
            $invoiceNumber = increment_unique_number($invoiceNumber);
            return $this->generate_unique_invoice_number($invoiceNumber);
        }

        return $invoiceNumber;
    }
}
