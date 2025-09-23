<?php

use Illuminate\Support\Facades\Auth;


if (!function_exists('is_super_user')) {
    function is_super_user()
    {
        // I want to do this check in a way that if the user is not logged in, it returns false
        return Auth::check() && Auth::user()->hasRole('super-user');
        // return Auth::check() && Auth::user()->property_id === null;
    }
}

if (!function_exists('current_property')) {
    function current_property()
    {
        if (is_super_user()) {
            return request()->attributes->get('current_property') ?? \App\Models\Property::first();
        }
        return Auth::user()->property ?? request()->attributes->get('current_property');
    }
}