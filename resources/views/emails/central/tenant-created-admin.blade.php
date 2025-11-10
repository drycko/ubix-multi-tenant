@component('mail::message')
# New tenant created — {{ $tenant->name }}

Hello {{ $adminEmail ?? 'Administrator' }},

A new tenant account has just been created in {{ config('app.name') }}. Below are the details captured at creation:

@component('mail::panel')
**Tenant name:** {{ $tenant->name ?? '—' }}  
**Tenant ID:** {{ $tenant->id ?? '—' }}  
@if(!empty($tenant->subdomain))
**Subdomain:** {{ $tenant->subdomain }}.{{ parse_url(config('app.url'), PHP_URL_HOST) }}  
@endif
@if(!empty($tenant->domain))
**Custom domain:** {{ $tenant->domain }}  
@endif
@if(!empty($tenant->tenancy_db_name))
**Database name:** {{ $tenant->tenancy_db_name }}  
@endif
@if(!empty($tenant->plan))
**Plan:** {{ ucfirst($tenant->plan) }}  
@endif
@if(!empty($tenant->admin_email))
**Admin / Billing contact:** {{ $tenant->admin_email }}  
@endif
@if(!empty($tenant->contact_name))
**Contact name:** {{ $tenant->contact_name }}  
@endif

@if(!empty($tenant->temp_password))
**Temporary password:** `{{ $tenant->temp_password }}`  
(One-time password — tenant will be required to set a new password on first login.)
@endif

**Created at:** {{ optional($tenant->created_at)->toDayDateTimeString() ?? now()->toDayDateTimeString() }}
@endcomponent

@php
  // Build a sensible admin manage URL. Adjust if your admin panel path differs.
  $manageUrl = config('app.url') . '/central/tenants/' . ($tenant->id ?? '');
@endphp

@component('mail::button', ['url' => $manageUrl])
Manage tenant
@endcomponent

Quick actions you may want to take:
- Review billing and plan details for this tenant.  
- Configure tenant payment gateway credentials (if required).  
- Set up onboarding tasks or schedule manual migration if requested.

If you need to view more information, go to the admin panel or contact the tenant at {{ $tenant->admin_email ?? '—' }}.

Thanks,<br>
{{ config('app.name') }} System Notification

@slot('subcopy')
This is an automated notification. If you did not expect this tenant to be created, please contact support at {{ $adminEmail ?? config('app.admin_email') }} immediately.
@endslot
@endcomponent