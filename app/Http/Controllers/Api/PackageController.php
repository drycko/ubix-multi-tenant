<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Package;
use App\Models\RoomPackage;
use App\Models\PropertyApi;
use Illuminate\Http\Request;
use App\Http\Resources\PackageResource;
use App\Services\HtmlSanitizerService;

class PackageController extends Controller
{
    /**
     * Return package data through API.
     */

    public function index(Request $request)
    {
        try {
            $apiKey = $request->header('X-Property-Api-Key');
            if (!$apiKey) {
                return response()->json(['error' => 'API key required'], 401);
            }

            $tenantPropertyApi = PropertyApi::where('api_key', $apiKey)->first();
            if (!$tenantPropertyApi) {
                return response()->json(['error' => 'Invalid API key: ' . $apiKey], 401);
            }

            $ipAddress = $request->ip();

            $property = $tenantPropertyApi->property;
            $packages = Package::where('property_id', $property->id)->where('pkg_status', 'active')->get();

            // Log API activity
            $property->apiActivities()->create([
                'api_key' => $apiKey,
                'endpoint' => '/api/packages',
                'method' => 'GET',
                'request_payload' => null,
                'response_payload' => $packages->toArray(),
                'ip_address' => $ipAddress,
            ]);

            return PackageResource::collection($packages);
        } catch (\Exception $e) {
            \Log::error('PackageController@index error: ' . $e->getMessage());
            return response()->json(['error' => 'Server error, ' . $e->getMessage()], 500);
        }
    }

}

