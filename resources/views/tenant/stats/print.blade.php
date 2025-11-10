<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistics Report - {{ $startDate->format('Y-m-d') }} to {{ $endDate->format('Y-m-d') }}</title>
    {{-- favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/images/favicon.ico') }}"/>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
            background: white;
        }
        
        .print-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid #3B82F6;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .company-info {
            flex: 1;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #3B82F6;
            margin-bottom: 10px;
        }
        
        .company-details {
            color: #6B7280;
            line-height: 1.6;
        }
        
        .report-info {
            text-align: right;
            flex: 0 0 300px;
        }
        
        .report-title {
            font-size: 28px;
            font-weight: bold;
            color: #1F2937;
            margin-bottom: 10px;
        }
        
        .report-details {
            background-color: #F3F4F6;
            padding: 15px;
            border-radius: 5px;
            text-align: left;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin: 30px 0;
        }
        
        .stat-card {
            background-color: #F9FAFB;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #3B82F6;
        }
        
        .stat-card.success {
            border-left-color: #10B981;
        }
        
        .stat-card.warning {
            border-left-color: #F59E0B;
        }
        
        .stat-card.danger {
            border-left-color: #EF4444;
        }
        
        .stat-card.info {
            border-left-color: #0EA5E9;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #1F2937;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 11px;
            color: #6B7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #1F2937;
            margin: 30px 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #E5E7EB;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .data-table th {
            background-color: #374151;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }
        
        .data-table td {
            padding: 10px;
            border-bottom: 1px solid #E5E7EB;
            font-size: 11px;
        }
        
        .data-table tr:nth-child(even) {
            background-color: #F9FAFB;
        }
        
        .text-right {
            text-align: right !important;
        }
        
        .text-center {
            text-align: center !important;
        }
        
        .property-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: bold;
            background-color: #DBEAFE;
            color: #1E40AF;
            margin-left: 10px;
        }
        
        .metric-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #E5E7EB;
        }
        
        .metric-label {
            font-weight: 600;
            color: #374151;
        }
        
        .metric-value {
            color: #6B7280;
        }
        
        .comparison-box {
            background-color: #EFF6FF;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        
        .comparison-title {
            font-weight: bold;
            color: #1E40AF;
            margin-bottom: 10px;
        }
        
        .comparison-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .comparison-item {
            background-color: white;
            padding: 10px;
            border-radius: 3px;
        }
        
        .change-positive {
            color: #10B981;
            font-weight: bold;
        }
        
        .change-negative {
            color: #EF4444;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #E5E7EB;
            text-align: center;
            color: #6B7280;
            font-size: 10px;
        }
        
        .print-actions {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #F8FAFC;
            border-radius: 5px;
            border: 1px solid #E2E8F0;
        }
        
        .btn {
            display: inline-block;
            padding: 8px 16px;
            margin: 0 5px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            border: none;
        }
        
        .btn-primary {
            background-color: #3B82F6;
            color: white;
        }
        
        .btn-secondary {
            background-color: #6B7280;
            color: white;
        }
        
        .btn-success {
            background-color: #10B981;
            color: white;
        }
        
        .btn-danger {
            background-color: #EF4444;
            color: white;
        }
        
        .top-performers {
            background-color: #ECFDF5;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        
        .top-performers-title {
            font-weight: bold;
            color: #047857;
            margin-bottom: 10px;
        }
        
        @media print {
            .print-actions {
                display: none !important;
            }
            
            body {
                margin: 0;
                padding: 15px;
            }
            
            .print-header {
                page-break-inside: avoid;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                page-break-inside: avoid;
            }
            
            .data-table {
                page-break-inside: avoid;
            }
            
            .section-title {
                page-break-after: avoid;
            }
        }
    </style>
</head>
<body>
    <!-- Print Actions (hidden when printing) -->
    <div class="print-actions">
        <button onclick="window.print()" class="btn btn-primary">
            🖨️ Print Report
        </button>
        <a href="{{ route('tenant.stats.download', array_merge(request()->query(), ['format' => 'pdf'])) }}" class="btn btn-danger">
            📥 Download PDF
        </a>
        <a href="{{ route('tenant.stats.download', array_merge(request()->query(), ['format' => 'csv'])) }}" class="btn btn-success">
            📥 Download CSV
        </a>
        <a href="{{ route('tenant.stats.index', request()->query()) }}" class="btn btn-secondary">
            ⬅️ Back to Statistics
        </a>
    </div>

    <div class="print-header">
        <div class="company-info">
            <div class="company-name">
                {{ config('app.name', 'Property Management System') }}
            </div>
            <div class="company-details">
                @if(!$viewingAllProperties && $propertyStats)
                    <strong>Property:</strong> {{ $propertyStats['name'] }}<br>
                @elseif($viewingAllProperties && $properties->count() > 0)
                    <strong>Properties:</strong> All Properties ({{ $properties->count() }} total)<br>
                @endif
                <strong>Generated:</strong> {{ now()->format('F j, Y g:i A') }}
            </div>
        </div>
        <div class="report-info">
            <div class="report-title">STATISTICS REPORT</div>
            <div class="report-details">
                <strong>Date Range:</strong><br>
                {{ $startDate->format('F j, Y') }}<br>
                to<br>
                {{ $endDate->format('F j, Y') }}<br>
                <strong>Duration:</strong> {{ $startDate->diffInDays($endDate) + 1 }} days
                @if($viewingAllProperties)
                    <br><span class="property-badge">All Properties</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Monthly Comparison -->
    @if($monthlyComparison)
    <div class="comparison-box">
        <div class="comparison-title">Monthly Comparison</div>
        <div class="comparison-grid">
            <div class="comparison-item">
                <div><strong>Bookings This Month:</strong> {{ $monthlyComparison['current_month_bookings'] }}</div>
                <div><strong>Previous Month:</strong> {{ $monthlyComparison['previous_month_bookings'] }}</div>
                @if($monthlyComparison['bookings_change'] != 0)
                    <div class="{{ $monthlyComparison['bookings_change'] > 0 ? 'change-positive' : 'change-negative' }}">
                        {{ $monthlyComparison['bookings_change'] > 0 ? '↑' : '↓' }} {{ abs($monthlyComparison['bookings_change']) }}% Change
                    </div>
                @endif
            </div>
            <div class="comparison-item">
                <div><strong>Revenue This Month:</strong> R {{ number_format($monthlyComparison['current_month_revenue'], 2) }}</div>
                <div><strong>Previous Month:</strong> R {{ number_format($monthlyComparison['previous_month_revenue'], 2) }}</div>
                @if($monthlyComparison['revenue_change'] != 0)
                    <div class="{{ $monthlyComparison['revenue_change'] > 0 ? 'change-positive' : 'change-negative' }}">
                        {{ $monthlyComparison['revenue_change'] > 0 ? '↑' : '↓' }} {{ abs($monthlyComparison['revenue_change']) }}% Change
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Overview Statistics -->
    <div class="section-title">Overview Statistics</div>
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ number_format($stats['total_bookings']) }}</div>
            <div class="stat-label">Total Bookings</div>
        </div>
        <div class="stat-card success">
            <div class="stat-value">{{ number_format($stats['active_bookings']) }}</div>
            <div class="stat-label">Active Bookings</div>
        </div>
        <div class="stat-card danger">
            <div class="stat-value">{{ number_format($stats['cancelled_bookings']) }}</div>
            <div class="stat-label">Cancelled Bookings</div>
        </div>
        <div class="stat-card info">
            <div class="stat-value">{{ number_format($stats['total_guests']) }}</div>
            <div class="stat-label">Total Guests</div>
        </div>
        <div class="stat-card success">
            <div class="stat-value">R {{ number_format($stats['total_revenue'], 2) }}</div>
            <div class="stat-label">Total Revenue</div>
        </div>
        <div class="stat-card warning">
            <div class="stat-value">R {{ number_format($stats['pending_revenue'], 2) }}</div>
            <div class="stat-label">Pending Revenue</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ number_format($stats['total_rooms']) }}</div>
            <div class="stat-label">Total Rooms</div>
        </div>
        <div class="stat-card info">
            <div class="stat-value">{{ number_format($stats['available_rooms']) }}</div>
            <div class="stat-label">Available Rooms</div>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="section-title">Key Performance Metrics</div>
    <div class="metric-row">
        <div class="metric-label">Occupancy Rate</div>
        <div class="metric-value">{{ $occupancyRate }}%</div>
    </div>
    <div class="metric-row">
        <div class="metric-label">Average Booking Duration</div>
        <div class="metric-value">{{ round($avgBookingDuration, 1) }} nights</div>
    </div>
    <div class="metric-row">
        <div class="metric-label">Revenue per Available Room</div>
        <div class="metric-value">
            R {{ $stats['available_rooms'] > 0 ? number_format($stats['total_revenue'] / $stats['available_rooms'], 2) : '0.00' }}
        </div>
    </div>
    <div class="metric-row">
        <div class="metric-label">Average Revenue per Booking</div>
        <div class="metric-value">
            R {{ $stats['total_bookings'] > 0 ? number_format($stats['total_revenue'] / $stats['total_bookings'], 2) : '0.00' }}
        </div>
    </div>

    <!-- Top Performing Room Types -->
    @if(count($topRoomTypes) > 0)
    <div class="top-performers">
        <div class="top-performers-title">Top Performing Room Types</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Room Type</th>
                    <th class="text-center">Bookings</th>
                    <th class="text-right">Revenue</th>
                    <th class="text-right">Avg Revenue/Booking</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topRoomTypes as $roomType)
                <tr>
                    <td><strong>{{ $roomType['name'] }}</strong></td>
                    <td class="text-center">{{ $roomType['bookings'] }}</td>
                    <td class="text-right">R {{ number_format($roomType['revenue'], 2) }}</td>
                    <td class="text-right">R {{ number_format($roomType['revenue'] / $roomType['bookings'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Bookings by Room Type -->
    <div class="section-title">Bookings by Room Type</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Room Type</th>
                <th class="text-center">Total Bookings</th>
                <th class="text-right">Percentage</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalRoomTypeBookings = array_sum($bookingsByRoomType['data']);
            @endphp
            @foreach($bookingsByRoomType['labels'] as $index => $label)
            <tr>
                <td>{{ $label }}</td>
                <td class="text-center">{{ $bookingsByRoomType['data'][$index] }}</td>
                <td class="text-right">
                    {{ $totalRoomTypeBookings > 0 ? round(($bookingsByRoomType['data'][$index] / $totalRoomTypeBookings) * 100, 1) : 0 }}%
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Daily Booking Trends (Summary) -->
    <div class="section-title">Daily Activity Summary</div>
    <div class="metric-row">
        <div class="metric-label">Total Days in Period</div>
        <div class="metric-value">{{ count($bookingsOverTime['labels']) }} days</div>
    </div>
    <div class="metric-row">
        <div class="metric-label">Average Daily Bookings</div>
        <div class="metric-value">
            {{ count($bookingsOverTime['data']) > 0 ? round(array_sum($bookingsOverTime['data']) / count($bookingsOverTime['data']), 1) : 0 }}
        </div>
    </div>
    <div class="metric-row">
        <div class="metric-label">Peak Booking Day</div>
        <div class="metric-value">
            @php
                $maxIndex = array_search(max($bookingsOverTime['data']), $bookingsOverTime['data']);
                $peakDay = $maxIndex !== false ? \Carbon\Carbon::parse($bookingsOverTime['labels'][$maxIndex])->format('M d, Y') : 'N/A';
                $peakCount = $maxIndex !== false ? $bookingsOverTime['data'][$maxIndex] : 0;
            @endphp
            {{ $peakDay }} ({{ $peakCount }} bookings)
        </div>
    </div>
    <div class="metric-row">
        <div class="metric-label">Average Daily Revenue</div>
        <div class="metric-value">
            R {{ count($revenueOverTime['data']) > 0 ? number_format(array_sum($revenueOverTime['data']) / count($revenueOverTime['data']), 2) : '0.00' }}
        </div>
    </div>

    <!-- Property Specific Stats (if single property) -->
    @if($propertyStats && !$viewingAllProperties)
    <div class="section-title">Property Details: {{ $propertyStats['name'] }}</div>
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ $propertyStats['total_rooms'] }}</div>
            <div class="stat-label">Total Rooms</div>
        </div>
        <div class="stat-card success">
            <div class="stat-value">{{ $propertyStats['available_rooms'] }}</div>
            <div class="stat-label">Available Rooms</div>
        </div>
        <div class="stat-card info">
            <div class="stat-value">{{ $propertyStats['occupied_rooms'] }}</div>
            <div class="stat-label">Occupied Rooms</div>
        </div>
        <div class="stat-card warning">
            <div class="stat-value">{{ $propertyStats['maintenance_rooms'] }}</div>
            <div class="stat-label">Under Maintenance</div>
        </div>
    </div>
    @endif

    <div class="footer">
        <div><strong>Statistics Report</strong></div>
        <div>Generated on {{ now()->format('F j, Y \a\t g:i A') }}</div>
        <div>{{ config('app.name', 'Property Management System') }}</div>
        @if(!$viewingAllProperties && $propertyStats)
            <div>Property: {{ $propertyStats['name'] }}</div>
        @endif
    </div>

    <script>
        // Auto-focus on print when page loads
        window.addEventListener('load', function() {
            // Small delay to ensure page is fully rendered
            setTimeout(function() {
                if (window.location.search.includes('autoprint=1')) {
                    window.print();
                }
            }, 500);
        });
    </script>
</body>
</html>
