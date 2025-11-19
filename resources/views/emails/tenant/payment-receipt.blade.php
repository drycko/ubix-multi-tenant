@component('mail::message')
# Booking Payment Receipt - Payment #{{ $payment->id }}

Thank you for your payment. Here are the details of your transaction:

**Invoice Number:** {{ $invoice->invoice_number }}<br>
**Booking Code:** {{ $payment->booking->bcode }}<br>
**Guest Name:** {{ $guest->name }}<br>
**Amount Paid:** {{ $payment->amount }}


You will receive your booking confirmation next as soon as a room is allocated to your booking.

If you have any questions, feel free to contact us.

Thanks,
{{ $invoice->booking->property->name }} Team
@endcomponent