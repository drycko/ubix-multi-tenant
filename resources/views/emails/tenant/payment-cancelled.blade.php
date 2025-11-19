@component('mail::message')
# Booking Payment Cancelled - Payment #{{ $payment->id }}

# Your order has been cancelled

Hi {{ $guest->first_name }},

We wanted to inform you that your order for Invoice {{ $invoice->invoice_number }} has been cancelled. If this was a mistake or you wish to proceed with your booking, please return to {{$invoice->booking->property->name ?? current_tenant()->name ?? 'our website' }} and try again.

Your order details are as follows:

**Invoice Number:** {{ $invoice->invoice_number }}<br>
**Booking Code:** {{ $payment->booking->bcode }}<br>
**Guest Name:** {{ $guest->name }}<br>

If you have any questions, feel free to contact us.


Thanks,
{{ $invoice->booking->property->name }} Team
@endcomponent