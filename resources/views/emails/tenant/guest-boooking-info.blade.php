@component('mail::message')
# Booking Information for Booking #{{ $booking->bcode }}

Dear {{ $booking->guests->first()->first_name }} {{ $booking->guests->first()->last_name }},

We hope this message finds you well. Please find below the details for your recent booking with us.

**Booking Code:** {{ $booking->bcode }}
**Check-in Date:** {{ $booking->check_in_date }}
**Check-out Date:** {{ $booking->check_out_date }}
**Number of Guests:** {{ $booking->number_of_guests }}

If you have any questions or need further assistance, please do not hesitate to contact us.

Thank you for choosing our services!

Best regards,  
{{ config('app.name') }} Team
@endcomponent