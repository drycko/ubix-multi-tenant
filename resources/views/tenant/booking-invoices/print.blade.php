<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $bookingInvoice->invoice_number }}</title>
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
        
        .invoice-info {
            text-align: right;
            flex: 0 0 250px;
        }
        
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #1F2937;
            margin-bottom: 10px;
        }
        
        .invoice-details {
            background-color: #F3F4F6;
            padding: 15px;
            border-radius: 5px;
            text-align: left;
        }
        
        .bill-to {
            margin: 30px 0;
            padding: 20px;
            background-color: #F9FAFB;
            border-left: 4px solid #3B82F6;
        }
        
        .bill-to-title {
            font-size: 14px;
            font-weight: bold;
            color: #3B82F6;
            margin-bottom: 10px;
        }
        
        .customer-info {
            font-size: 13px;
            line-height: 1.6;
        }
        
        .services-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }
        
        .services-table th {
            background-color: #374151;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: bold;
        }
        
        .services-table td {
            padding: 12px;
            border-bottom: 1px solid #E5E7EB;
        }
        
        .text-right {
            text-align: right !important;
        }
        
        .text-center {
            text-align: center !important;
        }
        
        .total-row {
            background-color: #F3F4F6;
            font-weight: bold;
        }
        
        .total-amount {
            font-size: 16px;
            color: #059669;
        }

        .paid-amount {
            color: #10B981;
        }

        .refunded-amount {
            color: #EF4444;
        }
        .balance-due {
            color: #B91C1C;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-paid { background-color: #D1FAE5; color: #065F46; }
        .status-pending { background-color: #FEF3C7; color: #92400E; }
        .status-partially_paid { background-color: #DBEAFE; color: #1E40AF; }
        .status-cancelled { background-color: #FEE2E2; color: #991B1B; }
        
        .special-requests {
            margin-top: 30px;
            padding: 20px;
            background-color: #FFFBEB;
            border-left: 4px solid #F59E0B;
        }
        
        .special-requests-title {
            font-weight: bold;
            color: #92400E;
            margin-bottom: 10px;
        }
        
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #E5E7EB;
            text-align: center;
            color: #6B7280;
            font-size: 10px;
        }
        
        .booking-details {
            background-color: #EFF6FF;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        
        .booking-details-title {
            font-weight: bold;
            color: #1E40AF;
            margin-bottom: 8px;
        }
        
        .package-info {
            background-color: #ECFDF5;
            padding: 10px;
            border-radius: 3px;
            margin-top: 5px;
            font-size: 11px;
            color: #047857;
        }
        
        .shared-room {
            background-color: #FEF3C7;
            padding: 5px 8px;
            border-radius: 3px;
            font-size: 10px;
            color: #92400E;
            margin-top: 3px;
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
            
            .services-table {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <!-- Print Actions (hidden when printing) -->
    <div class="print-actions">
        <button onclick="window.print()" class="btn btn-primary">
            🖨️ Print Invoice
        </button>
        <a href="{{ route('tenant.booking-invoices.download', $bookingInvoice) }}" class="btn btn-success">
            📥 Download PDF
        </a>
        <a href="{{ route('tenant.booking-invoices.show', $bookingInvoice) }}" class="btn btn-secondary">
            ⬅️ Back to Invoice
        </a>
    </div>

    <div class="print-header">
        <div class="company-info">
            <div class="company-name">{{ $property->name }}</div>
            <div class="company-details">
                @if($property->address)
                {{ $property->address }}<br>
                @endif
                @if($property->phone)
                Phone: {{ $property->phone }}<br>
                @endif
                @if($property->email)
                Email: {{ $property->email }}
                @endif
            </div>
        </div>
        <div class="invoice-info">
            <div class="invoice-title">INVOICE</div>
            <div class="invoice-details">
                <strong>Invoice #:</strong> {{ $bookingInvoice->invoice_number }}<br>
                <strong>Date:</strong> {{ $bookingInvoice->created_at->format('F j, Y') }}<br>
                <strong>Booking:</strong> {{ $bookingInvoice->booking->bcode }}<br>
                <span class="status-badge status-{{ $bookingInvoice->status }}">
                    {{ ucfirst(str_replace('_', ' ', $bookingInvoice->status)) }}
                </span>
            </div>
        </div>
    </div>

    @php
        $primaryGuest = $bookingInvoice->booking->bookingGuests->where('is_primary', true)->first()?->guest;
    @endphp

    @if($primaryGuest)
    <div class="bill-to">
        <div class="bill-to-title">BILL TO:</div>
        <div class="customer-info">
            <strong>{{ $primaryGuest->first_name }} {{ $primaryGuest->last_name }}</strong><br>
            @if($primaryGuest->email)
            {{ $primaryGuest->email }}<br>
            @endif
            @if($primaryGuest->phone)
            {{ $primaryGuest->phone }}<br>
            @endif
            @if($primaryGuest->physical_address)
            {{ $primaryGuest->physical_address }}<br>
            @endif
            @if($primaryGuest->nationality)
            {{ $primaryGuest->nationality }}
            @endif
        </div>
    </div>
    @endif

    <div class="booking-details">
        <div class="booking-details-title">Booking Information</div>
        <div>
            <strong>Check-in:</strong> {{ $bookingInvoice->booking->arrival_date->format('l, F j, Y') }}<br>
            <strong>Check-out:</strong> {{ $bookingInvoice->booking->departure_date->format('l, F j, Y') }}<br>
            <strong>Duration:</strong> {{ $bookingInvoice->booking->nights }} night(s)<br>
            <strong>Room Type:</strong> {{ $bookingInvoice->booking->is_shared ? 'Shared Room' : 'Private Room' }}
        </div>
    </div>

    <table class="services-table">
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-center">Check-in</th>
                <th class="text-center">Check-out</th>
                <th class="text-center">Nights</th>
                <th class="text-right">Rate</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>Room {{ $bookingInvoice->booking->room->number }}</strong><br>
                    <span style="color: #6B7280;">{{ $bookingInvoice->booking->room->type->name }}</span>
                    @if($bookingInvoice->booking->package)
                    <div class="package-info">
                        Package: {{ $bookingInvoice->booking->package->pkg_name }}
                    </div>
                    @endif
                    @if($bookingInvoice->booking->is_shared)
                    <div class="shared-room">Shared Room</div>
                    @endif
                </td>
                <td class="text-center">{{ $bookingInvoice->booking->arrival_date->format('M j, Y') }}</td>
                <td class="text-center">{{ $bookingInvoice->booking->departure_date->format('M j, Y') }}</td>
                <td class="text-center">{{ $bookingInvoice->booking->nights }}</td>
                <td class="text-right">{{ $currency }} {{ number_format($bookingInvoice->booking->daily_rate, 2) }}</td>
                <td class="text-right">{{ $currency }} {{ number_format($bookingInvoice->amount, 2) }}</td>
            </tr>
        </tbody>
        <tfoot>
            @if($bookingInvoice->taxes && $bookingInvoice->taxes['amount'] > 0)
            <tr>
                <td colspan="5" class="text-right">Subtotal:</td>
                <td class="text-right">{{ $currency }} {{ number_format($bookingInvoice->taxes['subtotal'], 2) }}</td>
            </tr>
            <tr>
                <td colspan="5" class="text-right">Tax ({{ $bookingInvoice->taxes['name'] }} - {{ $bookingInvoice->taxes['rate'] }}{{ $bookingInvoice->taxes['type'] == 'percentage' ? '%' : '' }}):</td>
                <td class="text-right">{{ $currency }} {{ number_format($bookingInvoice->taxes['amount'], 2) }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td colspan="5" class="text-right"><strong>TOTAL AMOUNT:</strong></td>
                <td class="text-right total-amount">{{ $currency }} {{ number_format($bookingInvoice->amount, 2) }}</td>
            </tr>
            @if ($bookingInvoice->amount_paid > 0)
            <tr>
                <td colspan="5" class="text-right">Amount Paid:</td>
                <td class="text-right paid-amount">{{ $currency }} {{ number_format($bookingInvoice->amount_paid, 2) }}</td>
            </tr>
            @endif
            @if ($bookingInvoice->total_refunded > 0)
            <tr>
                <td colspan="5" class="text-right">Total Refunded:</td>
                <td class="text-right refunded-amount">- {{ $currency }} {{ number_format($bookingInvoice->total_refunded, 2) }}</td>
            </tr>
            @endif
            <tr>
                <td colspan="5" class="text-right"><strong>Balance Due:</strong></td>
                <td class="text-right balance-due"><strong>{{ $currency }} {{ number_format($bookingInvoice->remaining_balance, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    @if($bookingInvoice->booking->bookingGuests->whereNotNull('special_requests')->count() > 0)
    <div class="special-requests">
        <div class="special-requests-title">Special Requests:</div>
        @foreach($bookingInvoice->booking->bookingGuests->whereNotNull('special_requests') as $bookingGuest)
        <div style="margin-bottom: 8px;">
            <strong>{{ $bookingGuest->guest->first_name }} {{ $bookingGuest->guest->last_name }}:</strong>
            {{ $bookingGuest->special_requests }}
        </div>
        @endforeach
    </div>
    @endif

    <div class="footer">
        <div>Thank you for your business!</div>
        <div>Invoice printed on {{ now()->format('F j, Y \a\t g:i A') }}</div>
        <div>{{ config('app.name', 'Property Management System') }}</div>
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