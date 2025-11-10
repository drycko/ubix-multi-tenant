@component('mail::message')
# Welcome to {{ config('app.name') }}!

Hello {{ $tenant->contact_name ?? $tenant->name ?? 'there' }},

Thank you for joining {{ config('app.name') }} — we’re excited to have you on board. Your tenant has been created successfully. Below are the important details to get you started.

@component('mail::panel')
**Tenant:** {{ $tenant->name ?? '—' }}  
@if(!empty($tenant->domain))
**Site URL:** {{ $tenant->domain }}  
@elseif(!empty($tenant->url))
**Site URL:** {{ $tenant->url }}  
@elseif(!empty($tenant->subdomain))
**Site URL:** https://{{ $tenant->subdomain }}.{{ parse_url(config('app.url'), PHP_URL_HOST) }}  
@else
**Site URL:** (you can find this in your admin panel)
@endif

@if(!empty($tenant->plan))
**Plan:** {{ ucfirst($tenant->plan) }}
@endif

@if(!empty($tenant->data['tenant_admin_id']) && !empty($tenant->data['tenant_admin_temp_password']))
**Temporary login (one-time):**  
Email: {{ $tenant->admin->email ?? $tenant->email ?? '—' }}  
Password: `{{ $tenant->tenant_admin_temp_password }}`  
(You will be prompted to set a new password on first login.)
@endif
@endcomponent

@php
    // Fallback login URL resolution
    $loginUrl = $tenant->url ?? (
        !empty($tenant->domain)
            ? (Str::startsWith($tenant->domain, 'http') ? $tenant->domain : 'https://'.$tenant->domain)
            : (!empty($tenant->subdomain) ? 'https://'.$tenant->subdomain.'.'.parse_url(config('app.url'), PHP_URL_HOST) : config('app.url'))
    );
@endphp

@component('mail::button', ['url' => $loginUrl.'/login'])
Go to your dashboard
@endcomponent

## Next steps
- Complete your profile and business details in Settings → Account.  
- Connect your payment gateway from Settings → Payments to start accepting bookings.  
- Invite team members from Settings → Users if you need additional staff accounts.

## Accessing the {{config('app.name')}} Billing Portal
To manage your subscription and billing, please log in to the [Billing Portal]({{ config('app.url') }}/p/login) using your tenant admin credentials.

If you have any questions or need help getting started, please contact our support team at {{ $adminEmail ?? config('app.admin_email') }} — we're happy to help.

Thanks,<br>
The {{ config('app.name') }} Team

@slot('subcopy')
If you didn't request this account or believe this message was sent in error, please contact us at {{ $adminEmail ?? config('app.admin_email') }} and we'll assist you.
@endslot
@endcomponent