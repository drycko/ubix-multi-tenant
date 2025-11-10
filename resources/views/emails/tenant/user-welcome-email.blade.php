@component('mail::message')
# Welcome to {{ config('app.name') }}!

Hi {{ $user->name }},

Thank you for joining us! We're excited to have you on board.


**User Details:**

@component('mail::panel')
**Temporary login (one-time):**  
Email: {{ $user->email }}  
Password: `{{ $temp_password }}`  
(You will be prompted to set a new password on first login.)
@endcomponent


**Property Details:**
@component('mail::panel')
Tenant: {{ $tenant->name ?? '—' }}
Property: {{ $user->property->name ?? '—' }}
**Site URL:**
@if(!empty($tenant->domain))
{{ $tenant->domain }}

@endcomponent

@component('mail::button', ['url' => route('tenant.dashboard')])
Go to Dashboard
@endcomponent

@endif
@component('mail::panel')
**Admin Contact:**

If you have any questions or need assistance, feel free to reach out to our support team at {{ $adminEmail }}.

@endcomponent

Best regards,<br>
{{ config('app.name') }} Team
@endcomponent
