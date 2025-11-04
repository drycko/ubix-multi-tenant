@component('mail::message')
# Invoice for Booking #{{ $bookingInvoice->booking->bcode }}

Dear {{ $bookingInvoice->booking->guests->first()->first_name }} {{ $bookingInvoice->booking->guests->first()->last_name }},

We hope this message finds you well. Please find attached the invoice for your recent booking with us.

**Invoice Number:** {{ $bookingInvoice->invoice_number }}
**Booking Code:** {{ $bookingInvoice->booking->bcode }}
You can view and download your invoice by clicking the button below:

@component('mail::button', ['url' => $bookingInvoice->getInvoiceUrl()])
View Invoice
@endcomponent
If you have any questions or need further assistance, please do not hesitate to contact us.

Thank you for choosing our services!

Best regards,  
{{ $bookingInvoice->booking->property->name }} Team
@endcomponent