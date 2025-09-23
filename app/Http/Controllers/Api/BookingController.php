<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PropertyApi;
use App\Models\Package;
use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the booking parameters
        try {
            //code...
            $validated = $request->validate([
                'package_id' => 'required|integer',
                'room_type_id' => 'required|integer',
                'arrival_date' => 'required|date',
                'departure_date' => 'required|date|after:arrival_date',
                'is_shared_room' => 'required|boolean',
                'primary_guest' => 'required|array',
                'primary_guest.first_name' => 'required|string|max:255',
                'primary_guest.last_name' => 'required|string|max:255',
                'primary_guest.email' => 'required|email|max:255',
                'primary_guest.phone' => 'nullable|string|max:20',
                'primary_guest.gown_size' => 'nullable|string|max:10',
                'primary_guest.id_no' => 'nullable|string|max:100',
                'primary_guest.is_returning' => 'required|boolean',
                'primary_guest.dietary_allergies' => 'nullable|string|max:500',
                'primary_guest.special_requests' => 'nullable|string|max:1000',

                // Only require additional_guest fields if is_shared_room is true
                'additional_guest' => 'sometimes|required_if:is_shared_room,true|array',
                'additional_guest.first_name' => 'required_if:is_shared_room,true|string|max:255',
                'additional_guest.last_name' => 'required_if:is_shared_room,true|string|max:255',
                'additional_guest.email' => 'required_if:is_shared_room,true|email|max:255',
                'additional_guest.phone' => 'nullable|string|max:20',
                'additional_guest.gown_size' => 'nullable|string|max:10',
                'additional_guest.id_no' => 'nullable|string|max:100',
                'additional_guest.is_returning' => 'required_if:is_shared_room,true|boolean',
                'additional_guest.dietary_allergies' => 'nullable|string|max:500',
                'additional_guest.special_requests' => 'nullable|string|max:1000',
            ]);
            // get request IP address
            $ipAddress = $request->ip();

            \Log::error('BookingController@store dates: ' . $validated['arrival_date'] . ' to ' . $validated['departure_date'] . ' from IP: ' . $ipAddress . ' is_shared_room: ' . ($validated['is_shared_room'] ? 'yes' : 'no'));
            // [2025-09-16 01:54:54] local.ERROR: BookingController@store dates: 2026-01-11 to 2026-01-17 from IP: 102.182.190.134 is_shared_room: no  
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('BookingController@store validation error: ' . print_r($e->errors(), true));
            return response()->json(['errors' => $e->errors()], 422);
        }

        // validate property API key from custom header
        $propertyApiKey = $request->header('X-Property-Api-Key');
        if (!$propertyApiKey) {
            return response()->json(['message' => 'Property API key is required'], 400);
        }
        // check the API key against your database
        $propertyApi = PropertyApi::where('api_key', $propertyApiKey)->where('is_active', true)->first();
        if (!$propertyApi) {
            return response()->json(['error' => 'Invalid API key: ' . $propertyApiKey], 401);
        }

        $property = $propertyApi->property;
        // make sure property has access to bookings (could be a permission check here)
        $propertySubscription = $property->activeSubscription() ?? $property->trialSubscription();
        if (!$propertySubscription) {
            return response()->json(['message' => 'Property does not have an active subscription.'], 403);
        }
        $propertyPlan = $propertySubscription->subscriptionTier;
        if (!$propertyPlan || !$propertyPlan->has_api_access) {
        // check if property has API access in their plan
            return response()->json(['message' => 'Property subscription plan does not allow API access.'], 403);
        }

        $subscriptionMaxBookings = $propertyPlan->max_bookings_per_month;
        // count current month bookings
        $canMakeBooking = $property->canMakeBooking();
        if (!$canMakeBooking) {
            return response()->json(['message' => 'Booking limit reached for the current month.'], 403);
        }

        // we need to limit requests to avoid abuse
        $requestCount = $property->apiActivities()->where('created_at', '>=', now()->startOfMonth())->count();
        if ($subscriptionMaxBookings !== null && $requestCount >= $subscriptionMaxBookings) {
            return response()->json(['message' => 'Monthly API request limit reached.'], 403);
        }

        // limit API calls from same IP address to 10 per hour
        $ipRequestCount = $property->apiActivities()->where('ip_address', $ipAddress)->where('created_at', '>=', now()->subHour())->count();
        if ($ipRequestCount >= 10) {
            return response()->json(['message' => 'Too many requests detected from this IP address. Please try again later.'], 429);
        }

        // find the package, ensure they belong to the property
        $package = Package::where('id', $validated['package_id'])->where('property_id', $property->id)->first();
        if (!$package || $package->pkg_status !== 'active') {
            return response()->json(['message' => 'Invalid package ID'], 400);
        }
        //  find room type (belongs to property not package)
        $roomType = $property->roomTypes()->where('id', $validated['room_type_id'])->first();
        if (!$roomType) {
            return response()->json(['message' => 'Invalid room type ID'], 400);
        }

        // get current daily rate from this room type for shared or private (should have effective_from, effective_until is nullable for ongoing rates)
        $dailyRate = $roomType->rates()->where('is_shared', $validated['is_shared_room'])->where('effective_from', '<=', now())->where(function ($query) {
            $query->where('effective_until', '>=', now())
                  ->orWhereNull('effective_until');
        })->first();
         // make sure there is a rate defined
        if (!$dailyRate) {
            return response()->json(['message' => 'No rate defined for this room type and sharing option'], 400);
        }

        // format dates
        $arrivalDate = date('Y-m-d', strtotime($validated['arrival_date']));
        $departureDate = date('Y-m-d', strtotime($validated['departure_date']));

        // check room availability for the package and room type
        $rooms = Room::getAvailableRooms($arrivalDate, $departureDate)->where('room_type_id', $roomType->id);
        // Only filter rooms that belong to the package and get the first available one
        $availableRoom = $rooms->filter(function ($room) use ($package) {
            return $room->packages && $room->packages->contains($package);
        })->first();

        if (!$availableRoom) {
            return response()->json(['message' => 'No available rooms for the selected package and room type'], 400);
        }

        // guests
        $primaryGuest = $validated['primary_guest'];
        $additionalGuest = $validated['is_shared_room'] && isset($validated['additional_guest']) ? $validated['additional_guest'] : null;
        // $additionalGuest = $validated

        // \Log::error('BookingController@store creating booking for ' . $primaryGuest['first_name'] . ' ' . $primaryGuest['last_name'] . ' & ' . ($additionalGuest['first_name'] ?? '') . ' in room ' . $availableRoom->name . ' from ' . $arrivalDate . ' to ' . $departureDate);
        // return;

        // Create the booking
        try {

            $booking = Booking::createBooking($property, $package, $roomType, $availableRoom, $arrivalDate, $departureDate, $validated['is_shared_room'], $primaryGuest, $additionalGuest, 'wordpress', $dailyRate->amount, $ipAddress);
             \Log::error('Booking created: ' . $booking->bcode);
             // respond with required booking details for checkout
            $responsePayload = [
                'message' => 'Booking created successfully',
                'booking_id' => $booking->id,
                'booking_reference' => $booking->bcode,
                'room_id' => $availableRoom->id,
                'room_name' => $availableRoom->name,
                'package_name' => $package->pkg_name,
                'primary_guest_meta' => $booking->primaryGuest()->legacy_meta,
                'arrival_date' => $booking->arrival_date,
                'departure_date' => $booking->departure_date,
                'number_of_nights' => $booking->nights,
                'daily_rate' => $booking->daily_rate,
                'total_amount' => $booking->total_amount,
                'payment_status' => $booking->invoices->first()->status ?? 'pending',
            ];

            // log the API activity
            $property->apiActivities()->create([
                'property_id' => $property->id,
                'api_key' => $propertyApiKey,
                'endpoint' => '/api/bookings',
                'method' => 'POST',
                'request_payload' => json_encode($validated),
                'response_payload' => json_encode($responsePayload),
                'ip_address' => $ipAddress,
            ]);

            return response()->json($responsePayload); // looks like the response is double encoded
        } catch (\Exception $e) {
            \Log::error('BookingController@store error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to create booking: ' . $e->getMessage()], 500);
        }
    }

    /**
     * update booking status
     */

    public function status(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|string|in:' . implode(',', Booking::VALID_STATUSES),
                'reason' => 'nullable|string|max:500',
                'invoice_data' => 'sometimes|array',
                'invoice_data.wc_order_number' => 'required_with:invoice_data|string|max:100',
                'invoice_data.invoice_date' => 'required_with:invoice_data|date',
                'invoice_data.invoice_amount' => 'required_with:invoice_data|numeric|min:0',
                'invoice_data.currency' => 'required_with:invoice_data|string|size:3',
                'invoice_data.payment_method' => 'nullable|string|max:100',
            ]);

            // validate property API key from custom header
            $propertyApiKey = $request->header('X-Property-Api-Key');
            if (!$propertyApiKey) {
                return response()->json(['message' => 'Property API key is required'], 400);
            }

            // check the API key against your database
            $propertyApi = PropertyApi::where('api_key', $propertyApiKey)->where('is_active', true)->first();
            if (!$propertyApi) {
                return response()->json(['error' => 'Invalid API key'], 401);
            }
            $property = $propertyApi->property;
            // find the booking, ensure it belongs to the property
            $booking = Booking::where('id', $id)->where('property_id', $property->id)->first();
            if (!$booking) {
                return response()->json(['message' => 'Booking not found'], 404);
            }

            $ipAddress = $request->ip();
            // update the booking status
            $booking->status = $validated['status'];
            // if there is invoice data, update the booking invoice details
            if (isset($validated['invoice_data'])) {
                $booking->status = $validated['status'];
                $invoiceData = $validated['invoice_data'];
                $booking->invoice_number = $invoiceData['wc_order_number'];
                $booking->invoice_date = date('Y-m-d', strtotime($invoiceData['invoice_date']));
                $booking->invoice_amount = $invoiceData['invoice_amount'];
                // add currency to legacy_meta json field
                $bookingLegacyMeta = $booking->legacy_meta ? json_decode($booking->legacy_meta, true) : [];
                $bookingLegacyMeta['paymentMethod'] = $invoiceData['payment_method'] ?? 'api_update';
                $bookingLegacyMeta['currency'] = $invoiceData['currency'];
                $bookingLegacyMeta['paymentIp'] = $ipAddress;
                $booking->legacy_meta = json_encode($bookingLegacyMeta);
                $booking->save();

                $invoice_number = Booking::generateUniqueInvoiceNumber();
                // $invoice_status = 'pending';

                // $booking_invoice = $booking->invoices()->create([
                //     'property_id' => current_property()->id,
                //     'invoice_number' => $invoice_number,
                //     'amount' => $totalAmount,
                //     'status' => $invoice_status,
                // ]);

                // create or update invoice record
                $invoice = $booking->invoices()->updateOrCreate(
                    [
                        'booking_id' => $booking->id,
                        'property_id' => $property->id,
                    ],
                    [
                        'invoice_number' => $invoice_number,
                        'external_reference' => $invoiceData['wc_order_number'],
                        'invoice_date' => $booking->invoice_date,
                        'amount' => $booking->invoice_amount,
                        'currency' => $invoiceData['currency'],
                        'status' => 'paid', // assuming payment is done if invoice data is provided
                        'payment_method' => $invoiceData['payment_method'] ?? 'api_update',
                    ]
                );

                $responsePayload = [
                    'message' => 'Booking status updated successfully',
                    'booking_id' => $booking->id,
                    'booking_reference' => $booking->bcode,
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'status' => $booking->status,
                ];
            }
            else {
                $booking->status = $validated['status'];
                $booking->save();
                $responsePayload = [
                    'message' => 'Booking status updated successfully',
                    'booking_id' => $booking->id,
                    'booking_reference' => $booking->bcode,
                    'status' => $booking->status,
                ];
            }

            // log the API activity
            $property->apiActivities()->create([
                'property_id' => $property->id,
                'api_key' => $propertyApiKey,
                'endpoint' => '/api/bookings/' . $booking->id . '/status',
                'method' => 'POST',
                'request_payload' => json_encode($validated),
                'response_payload' => json_encode($responsePayload),
                'ip_address' => $ipAddress,
            ]);

            return response()->json($responsePayload, 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('BookingController@status error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update booking status: ' . $e->getMessage()], 500);
        }
        
        
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
