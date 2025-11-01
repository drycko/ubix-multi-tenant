@component('mail::message')
# Guest Portal Login

Hello {{ $guest->name ?? 'Guest' }},

Click the button below to securely log in to your guest dashboard and manage your bookings.

@component('mail::button', ['url' => $link])
Login Now
@endcomponent

This link is valid for 30 minutes.

If you did not request this, you can ignore this email.

Thanks,<br>
{{ config('app.name') }}
@endcomponent