<?php

use App\Models\Tenant\Property;
use Illuminate\Support\Facades\Auth;

// Get the current tenant using Stancl Tenancy
if (!function_exists('current_tenant')) {
    function current_tenant()
    {
        // Use the tenancy() helper from Stancl package
        if (tenancy()->initialized) {
            // Get the current tenant
            // bypass APP_TIMEZONE with tenant timezone if set
            if (tenant() && tenant()->timezone) {
                date_default_timezone_set(tenant()->timezone);
            }
            return tenant();
        }

        // If tenancy is not initialized (e.g., in central domain)
        return null;
    }
}

// current tenant currency
if (!function_exists('tenant_currency')) {
    function tenant_currency()
    {   
        // first try to get from settings if set
        $currency = \App\Models\Tenant\TenantSetting::getSetting('currency');
        if ($currency) {
            return $currency;
        }
        $tenant = current_tenant();
        return $tenant ? $tenant->currency : null;
    }
}

if (!function_exists('is_super_user')) {
    function is_super_user()
    {
        // I want to do this check in a way that if the user is not logged in, it returns false
        if (!Auth::check()) {
            return false;
        }
        
        $user = Auth::user();
        
        // Check if user has super-user role with tenant guard
        $hasSuperUserRole = $user->hasRole('super-user', 'tenant');
        
        // Alternative check: super-users typically have property_id as null
        $hasNullPropertyId = is_null($user->property_id);
        
        return $hasSuperUserRole || $hasNullPropertyId;
    }
}

/**
 * Get the tenant ID if we're in a tenant context
 */
if (!function_exists('current_tenant_id')) {
    function current_tenant_id()
    {
        $tenant = current_tenant();
        return $tenant ? $tenant->getTenantKey() : null;
    }
}

// super user will need to select a property to work with

// Get the current property based on user role and session
if (!function_exists('current_property')) {
    function current_property()
    {
        // Get property from request attributes (set by PropertySelector middleware)
        if (request()->attributes->has('current_property')) {
            return request()->attributes->get('current_property');
        }

        // For super-users, check session for selected property
        if (is_super_user()) {
            $selectedPropertyId = session('selected_property_id');
            if ($selectedPropertyId) {
                return Property::find($selectedPropertyId);
            }
            // No property selected - return null to indicate "all properties" mode
            return null;
        }

        // For property-specific users, use their assigned property
        if (auth()->check() && auth()->user()->property_id) {
            return Property::find(auth()->user()->property_id);
        }

        // Fallback: get first property (for development)
        return Property::first();
    }
}

if (!function_exists('is_property_selected')) {
    function is_property_selected()
    {
        return current_property() !== null;
    }
}

if (!function_exists('selected_property_id')) {
    function selected_property_id()
    {
        $property = current_property();
        return $property ? $property->id : null;
    }
}

if (!function_exists('property_name')) {
    function property_name()
    {
        $property = current_property();
        // if property is null, return 'No Property Selected'
        return $property ? $property->name : 'No Property Selected';
    }
}

if (!function_exists('property_id')) {
    function property_id()
    {
        $property = current_property();
        return $property ? $property->id : null;
    }
}

if (!function_exists('property_currency')) {
    function property_currency()
    {
        $property = current_property();
        // if property is null, return tenant default currency or 'USD'
        return $property ? $property->currency : (tenant() ? tenant()->currency : 'USD');
    }
}

if (!function_exists('truncate')) {
    function truncate($string, $length = 100, $suffix = '...')
    {
        if (strlen($string) > $length) {
            return substr($string, 0, $length) . $suffix;
        }
        return $string;
    }
}

// clean time formt from imported csv files
if (!function_exists('clean_time')) {
    function clean_ctime($cleanTime)
    {
        // csv TIMEARRIVE needs to be cleaned/formatted if needed - currency it's just a string sometime like "17h00, 17-18h00 or 5pm"
        // format to HH:MM:SS - if there is a '-' or 'to' we take the last part
        if (!empty($cleanTime)) {
            if (strpos($cleanTime, '-') !== false) {
                $parts = explode('-', $cleanTime);
                $timePart = trim(end($parts));
            } elseif (stripos($cleanTime, 'to') !== false) {
                $parts = preg_split('/\s+to\s+/i', $cleanTime);
                $timePart = trim(end($parts));
            } else {
                $timePart = trim($cleanTime);
            }
            // Now parse timePart to HH:MM:SS
            $timePart = str_ireplace(['h', 'H'], ':', $timePart); // Replace h or H with :
            $timePart = str_ireplace(['am', 'pm', 'AM', 'PM'], '', $timePart); // Remove am/pm for now
            $timePart = trim($timePart);
            // If timePart is like 17:00 or 17:00:00 it's fine, if it's like 5 or 5:30 we need to convert to 24h format
            if (preg_match('/^\d{1,2}(:\d{2})?$/', $timePart)) {
                // If it's like 5 or 5:30
                if (strpos($timePart, ':') === false) {
                    $timePart .= ':00'; // Add minutes if missing
                }
                // Convert to 24h format assuming PM if less than 12
                list($hour, $minute) = explode(':', $timePart);
                if ($hour < 12) {
                    $hour += 12; // Convert to PM
                }
                $arrivalTime = sprintf('%02d:%02d:00', $hour, $minute);
            } else {
                // If it's already in HH:MM:SS format or invalid, just use as is or null
                $arrivalTime = preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $timePart) ? $timePart : null;
            }
        } else {
            $arrivalTime = null;
        }
        return $arrivalTime;
    }
}

// function to increment unique number strings, e.g. INV-001 to INV-002
if (!function_exists('increment_unique_number')) {
    function increment_unique_number($number)
    {
        // Match the numeric part at the end of the string
        if (preg_match('/(.*?)(\d+)$/', $number, $matches)) {
            $prefix = $matches[1]; // The non-numeric prefix
            $num = $matches[2];    // The numeric part
            $newNum = str_pad($num + 1, strlen($num), '0', STR_PAD_LEFT); // Increment and pad with leading zeros
            return $prefix . $newNum; // Combine prefix with new number
        } else {
            // If no numeric part, just append '1'
            return $number . '1';
        }
    }
}

/*
I want to first read the countries from my json file and
return them as an array
*/
if (!function_exists('get_countries')) {
    /**
     * Get the list of countries from the JSON file.
     *
     * @return array
     */
    function get_countries(): array
    {
        $json = file_get_contents('../public/vendor/countries.json');
        return json_decode($json, true);
    }
}

// get currencies from countries.json
if (!function_exists('get_currencies')) {
    /**
     * Get the list of unique currencies from the countries JSON file.
     *
     * @return array
     */
    function get_currencies(): array
    {
        $countries = get_countries();
        $currencies = [];
        foreach ($countries as $country) {
            if (isset($country['currency']['code']) && !in_array($country['currency']['code'], $currencies)) {
                $currencies[] = $country['currency']['code'];
            }
        }
        sort($currencies);
        return $currencies;
    }
}

// allowed curencies
if (!function_exists('allowed_currencies')) {
    function allowed_currencies(): array
    {
        return ['USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY', 'CNY', 'INR', 'BRL', 'ZAR'];
    }
}

// get supported currencies (intersection of all currencies and allowed currencies)
if (!function_exists('get_supported_currencies')) {
    function get_supported_currencies(): array
    {
        $allCurrencies = get_currencies();
        $allowed = allowed_currencies();
        $supported = array_intersect($allCurrencies, $allowed);
        
        // Return as associative array with code => name for easy use in forms
        $countries = get_countries();
        $result = [];
        
        foreach ($supported as $currencyCode) {
            // Find the currency details from any country that uses this currency
            foreach ($countries as $country) {
                if (isset($country['currency']['code']) && $country['currency']['code'] === $currencyCode) {
                    $result[$currencyCode] = $country['currency']['name'];
                    break;
                }
            }
        }
        
        return $result;
    }
}

// get currency name by code
if (!function_exists('get_currency_name')) {
    function get_currency_name($currencyCode): string
    {
        $countries = get_countries();
        foreach ($countries as $country) {
            if (isset($country['currency']['code']) && $country['currency']['code'] === $currencyCode) {
                return $country['currency']['name'];
            }
        }
        return $currencyCode; // Fallback to code if name not found
    }
}

// get currency symbol by code
if (!function_exists('get_currency_symbol')) {
    function get_currency_symbol($currencyCode): string
    {
        $countries = get_countries();
        foreach ($countries as $country) {
            if (isset($country['currency']['code']) && $country['currency']['code'] === $currencyCode) {
                return $country['currency']['symbol'];
            }
        }
        return '$'; // Fallback to dollar sign
    }
}

// get supported timezones
if (!function_exists('get_supported_timezones')) {
    function get_supported_timezones(): array
    {
        $timezones = [];
        foreach (timezone_identifiers_list() as $timezone) {
            // Create readable format: timezone => "Timezone (UTC+/-X)"
            $dt = new DateTime('now', new DateTimeZone($timezone));
            $offset = $dt->format('P');
            $timezones[$timezone] = str_replace('_', ' ', $timezone) . " (UTC{$offset})";
        }
        return $timezones;
    }
}

// get supported locales (based on available countries for now we only support en)
if (!function_exists('get_supported_locales')) {
    function get_supported_locales(): array
    {
        return ['en' => 'English'];
    }
}

// format price with currency 
if (!function_exists('format_price')) {
    /**
     * Format a price with the given currency.
     *
     * @param float|int $price The price to format
     * @param string|null $currency The currency code (e.g., USD, EUR)
     * @param bool $showCurrency Whether to show the currency code
     * @return string
     */
    function format_price($price, $currency = null, $showCurrency = true): string
    {
        if ($currency === null) {
            $currency = property_currency();
        }
        
        // Get currency symbol
        $symbol = get_currency_symbol($currency);
        
        $formattedPrice = number_format((float) $price, 2, '.', ',');
        
        return $showCurrency ? "{$symbol} {$formattedPrice}" : $formattedPrice;
    }
}