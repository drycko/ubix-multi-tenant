<?php

use App\Models\Property;

if (!function_exists('current_property')) {
    function current_property()
    {
        // Get property from request attributes (set by middleware)
        if (request()->attributes->has('currentProperty')) {
            return request()->attributes->get('currentProperty');
        }

        // Get property from authenticated user
        if (auth()->check() && auth()->user()->property_id) {
            return Property::find(auth()->user()->property_id);
        }

        // Get property from session (for property selection)
        if (session()->has('current_property_id')) {
            return Property::find(session('current_property_id'));
        }

        // Fallback: get first property (for super admin or development)
        return Property::first();
    }
}

if (!function_exists('property_name')) {
    function property_name()
    {
        $property = current_property();
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
        return $property ? $property->currency : 'USD';
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
if (!function_exists('getCountries')) {
    /**
     * Get the list of countries from the JSON file.
     *
     * @return array
     */
    function getCountries(): array
    {
        $json = file_get_contents(__DIR__ . '/../../resources/countries.json');
        return json_decode($json, true);
    }
}