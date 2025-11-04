@component('mail::message')
# Booking Information for Booking #{{ $booking->bcode }}

Dear {{ $primaryGuest->first_name }} {{ $primaryGuest->last_name }},

We hope this message finds you well. Please find below the details for your recent booking with us.

**Booking Code:** {{ $booking->bcode }}<br>
**Check-in Date:** {{ $booking->arrival_date }}<br>
**Check-out Date:** {{ $booking->departure_date }}<br>
**Number of Guests:** {{ $booking->number_of_guests }}<br>
**Package Name:** {{ $booking->package->pkg_name ?? 'N/A' }}<br>

If you have any questions or need further assistance, please do not hesitate to contact us.

Thank you for choosing our services!

Best regards,  
{{ $booking->property->name }} Team
@endcomponent