<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Information - {{ $booking->bcode }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #3B82F6;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #3B82F6;
            margin-bottom: 5px;
        }
        
        .property-name {
            font-size: 18px;
            color: #1F2937;
            margin-bottom: 10px;
        }
        
        .document-title {
            font-size: 20px;
            font-weight: bold;
            color: #1F2937;
            margin-top: 15px;
        }
        
        .booking-code {
            font-size: 14px;
            color: #6B7280;
            font-weight: bold;
        }
        
        .info-section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #3B82F6;
            border-bottom: 1px solid #E5E7EB;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        
        .info-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-label {
            display: table-cell;
            width: 30%;
            padding: 6px 10px;
            font-weight: bold;
            background-color: #F9FAFB;
            border: 1px solid #E5E7EB;
            color: #374151;
        }
        
        .info-value {
            display: table-cell;
            padding: 6px 10px;
            border: 1px solid #E5E7EB;
            color: #1F2937;
        }
        
        .guest-section {
            margin-bottom: 20px;
        }
        
        .guest-title {
            font-size: 13px;
            font-weight: bold;
            color: #059669;
            margin-bottom: 8px;
        }
        
        .special-requests {
            background-color: #FEF3C7;
            padding: 10px;
            border-left: 4px solid #F59E0B;
            margin-top: 10px;
            font-style: italic;
        }
        
        .package-info {
            background-color: #ECFDF5;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #10B981;
            margin-bottom: 20px;
        }
        
        .package-title {
            font-weight: bold;
            color: #047857;
            margin-bottom: 5px;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #E5E7EB;
            text-align: center;
            color: #6B7280;
            font-size: 10px;
        }
        
        .contact-info {
            background-color: #F0F9FF;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .contact-title {
            font-weight: bold;
            color: #0369A1;
            margin-bottom: 8px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-confirmed { background-color: #D1FAE5; color: #065F46; }
        .status-pending { background-color: #FEF3C7; color: #92400E; }
        .status-checked_in { background-color: #DBEAFE; color: #1E40AF; }
        .status-checked_out { background-color: #E0E7FF; color: #3730A3; }
        .status-cancelled { background-color: #FEE2E2; color: #991B1B; }
        
        .currency {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo"><img src="{{ asset('assets/images/ubix-logo-full.png') }}" alt="{{ config('app.name', 'Property Management') }}"></div>
        <div class="property-name">{{ $property->name ?? 'Property Name' }}</div>
        <div class="document-title">Room Information</div>
        <div class="booking-code">Booking: {{ $booking->bcode }}</div>
    </div>

    <!-- Booking Status -->
    <div class="info-section">
        <div class="section-title">Booking Status</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Status</div>
                <div class="info-value">
                    <span class="status-badge status-{{ $booking->status }}">
                        {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Package Information (if applicable) -->
    @if($booking->package)
    <div class="package-info">
        <div class="package-title">Package: {{ $booking->package->pkg_name }}</div>
        <div>Duration: {{ $booking->package->pkg_number_of_nights }} nights</div>
        <div>Base Price: <span class="currency">{{ $currency }} {{ number_format($booking->package->pkg_base_price, 2) }}</span></div>
        @if($booking->package->pkg_description)
        <div style="margin-top: 8px;">{!! strip_tags($booking->package->pkg_description) !!}</div>
        @endif
    </div>
    @endif

    <!-- Booking Details -->
    <div class="info-section">
        <div class="section-title">Booking Details</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Booking Code</div>
                <div class="info-value">{{ $booking->bcode }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Check-in Date</div>
                <div class="info-value">{{ $booking->arrival_date->format('l, F j, Y') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Check-out Date</div>
                <div class="info-value">{{ $booking->departure_date->format('l, F j, Y') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Number of Nights</div>
                <div class="info-value">{{ $booking->nights }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Room Sharing</div>
                <div class="info-value">{{ $booking->is_shared ? 'Shared Room' : 'Private Room' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Booking Source</div>
                <div class="info-value">{{ ucfirst(str_replace('_', ' ', $booking->source)) }}</div>
            </div>
        </div>
    </div>

    <!-- Room Information -->
    <div class="info-section">
        <div class="section-title">Room Information</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Room Number</div>
                <div class="info-value">{{ $booking->room->number }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Room Type</div>
                <div class="info-value">{{ $booking->room->type->name }}</div>
            </div>
            @if($booking->room->type->description)
            <div class="info-row">
                <div class="info-label">Room Description</div>
                <div class="info-value">{{ $booking->room->type->description }}</div>
            </div>
            @endif
            <div class="info-row">
                <div class="info-label">Daily Rate</div>
                <div class="info-value"><span class="currency">{{ $currency }} {{ number_format($booking->daily_rate, 2) }}</span></div>
            </div>
            <div class="info-row">
                <div class="info-label">Total Amount</div>
                <div class="info-value"><span class="currency">{{ $currency }} {{ number_format($booking->total_amount, 2) }}</span></div>
            </div>
        </div>
    </div>

    <!-- Guest Information -->
    @foreach($booking->bookingGuests as $index => $bookingGuest)
    <div class="guest-section">
        <div class="guest-title">
            {{ $bookingGuest->is_primary ? 'Primary Guest' : 'Guest ' . ($index + 1) }}
        </div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Name</div>
                <div class="info-value">{{ $bookingGuest->guest->first_name }} {{ $bookingGuest->guest->last_name }}</div>
            </div>
            @if($bookingGuest->guest->email)
            <div class="info-row">
                <div class="info-label">Email</div>
                <div class="info-value">{{ $bookingGuest->guest->email }}</div>
            </div>
            @endif
            @if($bookingGuest->guest->phone)
            <div class="info-row">
                <div class="info-label">Phone</div>
                <div class="info-value">{{ $bookingGuest->guest->phone }}</div>
            </div>
            @endif
            @if($bookingGuest->guest->nationality)
            <div class="info-row">
                <div class="info-label">Nationality</div>
                <div class="info-value">{{ $bookingGuest->guest->nationality }}</div>
            </div>
            @endif
            <div class="info-row">
                <div class="info-label">Adults</div>
                <div class="info-value">{{ $bookingGuest->count_adults }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Children</div>
                <div class="info-value">{{ $bookingGuest->count_children }}</div>
            </div>
        </div>
        
        @if($bookingGuest->special_requests)
        <div class="special-requests">
            <strong>Special Requests:</strong><br>
            {{ $bookingGuest->special_requests }}
        </div>
        @endif
    </div>
    @endforeach

    <!-- Property Contact Information -->
    <div class="contact-info">
        <div class="contact-title">Property Contact Information</div>
        @if($property->email)
        <div><strong>Email:</strong> {{ $property->email }}</div>
        @endif
        @if($property->phone)
        <div><strong>Phone:</strong> {{ $property->phone }}</div>
        @endif
        @if($property->address)
        <div><strong>Address:</strong> {{ $property->address }}</div>
        @endif
    </div>

    <div class="footer">
        Generated on {{ now()->format('F j, Y \a\t g:i A') }} | 
        {{ config('app.name', 'Property Management System') }}
    </div>
</body>
</html>