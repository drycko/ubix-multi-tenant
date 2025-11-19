@component('mail::message')
# Booking Payment Failed - Payment #{{ $payment->id }}

# Sorry, your order was unsuccessful

Hi {{ $guest->first_name }},

Unfortunately, we couldn't complete your order due to an issue with your payment method.

If you'd like to continue with your purchase, please return to {{$invoice->booking->property->name ?? current_tenant()->name ?? 'our website' }} and try a different method of payment.

Your order details are as follows:

**Invoice Number:** {{ $invoice->invoice_number }}<br>
**Booking Code:** {{ $payment->booking->bcode }}<br>
**Guest Name:** {{ $guest->name }}<br>

**Please note:** Without a successfull payment your booking will not be confirmed.


If you have any questions, feel free to contact us.

Thanks,
{{ $invoice->booking->property->name }} Team
@endcomponent
