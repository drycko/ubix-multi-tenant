@component('mail::message')
# NEW BOOKING ORDER NOTIFICATION - #{{ $booking->bcode }}

You have received an order from {{ $booking->primary_guest->first_name }} {{ $booking->primary_guest->last_name }}. Here are the booking details:

ORDER #{{ $booking->id }}

**Property:** {{ $booking->property->name }}

**Package:** {{ $booking->package->pkg_name }}<br>
**Room Type:** {{ $booking->room->web_description ?? 'N/A' }}<br>
**Price:** {{ $booking->total_amount ?? 'N/A' }}<br>
**Check-in Date:** {{ $booking->arrival_date }}<br>
**Check-out Date:** {{ $booking->departure_date }}<br>

Guests Information:<br>
@foreach ($booking->bookingGuests() as $bookingGuest)<br>
**Guest {{ $loop->iteration }}**<br>
**Name:** {{ $bookingGuest->guest->first_name }} {{ $bookingGuest->guest->last_name }}<br>
**ID No:** {{ $bookingGuest->guest->id_number }}<br>
**Email:** {{ $bookingGuest->guest->email }}<br>
**Phone:** {{ $bookingGuest->guest->phone_number }}<br>
**Gown Size:** {{ $bookingGuest->guest->gown_size ?? 'N/A' }}<br>
**Dietary Requirements:** {{ $bookingGuest->special_requests ?? 'N/A' }}<br>
______________

@endforeach


If you have any questions, feel free to contact us for feature requests and feedback.

Thanks,
{{ config('app.name') }}
@endcomponent