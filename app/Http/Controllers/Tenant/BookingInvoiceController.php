<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\BookingInvoice;
use App\Models\Tenant\InvoicePayment;
use App\Traits\LogsTenantUserActivity;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class BookingInvoiceController extends Controller
{
    use LogsTenantUserActivity;
    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get property context
        $propertyId = selected_property_id();
        
        // Build the query with filters
        $query = BookingInvoice::with(['booking.room', 'booking.bookingGuests.guest'])
            ->where('property_id', $propertyId);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('invoice_number', 'like', "%{$request->search}%")
                  ->orWhereHas('booking', function($bookingQuery) use ($request) {
                      $bookingQuery->where('bcode', 'like', "%{$request->search}%");
                  });
            });
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(15);
        $currency = property_currency();

        return view('tenant.booking-invoices.index', compact('invoices', 'currency'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Implementation if needed
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Implementation if needed
    }

    /**
     * Display the specified resource.
     */
    public function show(BookingInvoice $bookingInvoice)
    {
        // Authorization check
        if ($bookingInvoice->property_id !== selected_property_id()) {
            abort(403, 'Unauthorized action.');
        }

        // Eager load relationships
        $bookingInvoice->load([
            'booking.room.type', 
            'booking.package', 
            'booking.bookingGuests.guest',
            'invoicePayments.recordedBy'
        ]);

        $paymentMethods = InvoicePayment::getPaymentMethods();
        
        $property = current_property();
        $currency = property_currency();

        return view('tenant.booking-invoices.show', compact('bookingInvoice', 'property', 'currency', 'paymentMethods'));
    }

    /**
     * Download the invoice as PDF.
     */
    public function download(BookingInvoice $bookingInvoice)
    {
        // Authorization check
        if ($bookingInvoice->property_id !== selected_property_id()) {
            abort(403, 'Unauthorized action.');
        }

        // Eager load relationships
        $bookingInvoice->load(['booking.room.type', 'booking.package', 'booking.bookingGuests.guest']);
        
        $property = current_property();
        $currency = property_currency();

        // Log the download activity
        $this->logTenantActivity(
            'download_invoice',
            'Downloaded invoice: ' . $bookingInvoice->invoice_number,
            $bookingInvoice,
            [
                'table' => 'booking_invoices',
                'id' => $bookingInvoice->id,
                'user_id' => auth()->id(),
                'action' => 'download'
            ]
        );

        // Generate PDF
        $pdf = Pdf::loadView('tenant.booking-invoices.pdf', compact('bookingInvoice', 'property', 'currency'));

        // Configure PDF options
        $pdf->setPaper('A4', 'portrait');
        $pdf->getDomPDF()->set_option("enable_php", true);
        $pdf->getDomPDF()->set_option("enable_remote", true);
        $pdf->getDomPDF()->set_option("enable_html5_parser", true);

        // Generate filename
        $filename = sprintf('invoice-%s-%s.pdf', 
            $bookingInvoice->invoice_number,
            now()->format('Y-m-d_His')
        );

        return $pdf->download($filename);
    }

    /**
     * Print view of the invoice.
     */
    public function print(BookingInvoice $bookingInvoice)
    {
        // Authorization check
        if ($bookingInvoice->property_id !== selected_property_id()) {
            abort(403, 'Unauthorized action.');
        }

        // Eager load relationships
        $bookingInvoice->load(['booking.room.type', 'booking.package', 'booking.bookingGuests.guest']);
        
        $property = current_property();
        $currency = property_currency();

        return view('tenant.booking-invoices.print', compact('bookingInvoice', 'property', 'currency'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BookingInvoice $bookingInvoice)
    {
        // Implementation if needed
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BookingInvoice $bookingInvoice)
    {
        // Implementation if needed
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BookingInvoice $bookingInvoice)
    {
        // Implementation if needed
    }

    /**
     * Generate unique invoice number.
     */
    private function generate_unique_invoice_number($invoiceNumber)
    {
        $existingInvoice = BookingInvoice::where('invoice_number', $invoiceNumber)
            ->where('property_id', selected_property_id())
            ->first();

        if ($existingInvoice) {
            // If it exists, we need to increment the number
            $invoiceNumber = increment_unique_number($invoiceNumber);
            return $this->generate_unique_invoice_number($invoiceNumber);
        }

        return $invoiceNumber;
    }
}
