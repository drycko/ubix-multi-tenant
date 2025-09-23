<?php

use App\Models\Company;

// current company function
if (!function_exists('current_company')) {
    function current_company()
    {
        // Get company from request attributes (set by middleware)
        if (request()->attributes->has('currentCompany')) {
            return request()->attributes->get('currentCompany');
        }

        // Get company from authenticated user
        if (auth()->check() && auth()->user()->company_id) {
            return Company::find(auth()->user()->company_id);
        }

        // Get company from session (for company selection)
        if (session()->has('current_company_id')) {
            return Company::find(session('current_company_id'));
        }

        // Fallback: get first company (for super admin or development)
        return Company::first();
    }
}

// default currency function
if (!function_exists('default_currency')) {
    function default_currency()
    {
        $company = current_company();
        $currency = $company ? $company->currency : 'USD';
        // we still need to convert the currency to its code then return the code

        return strtoupper($currency);
    }
}