<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $bookingInvoice->invoice_number }}</title>
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

        .btn-danger {
            background-color: #EF4444;
            color: white;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            position: relative;
        }

        .alert-success {
            background-color: #D1FAE5;
            color: #065F46;
        }
        .alert-danger {
            background-color: #FEE2E2;
            color: #991B1B;
        }
        .btn-close {
            position: absolute;
            top: 10px;
            right: 10px;
            background: none;
            border: none;
            font-size: 16px;
            cursor: pointer;
            color: inherit;
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
  {{-- payfast cancel --}}
    <div class="alert alert-danger">
        <strong>Payment Cancelled</strong>
        <p>Your payment for Invoice {{ $bookingInvoice->invoice_number }} has been cancelled. If this was a mistake, please try again.</p>
    </div>
    <div class="print-actions">
        <a href="{{ route('tenant.booking-invoices.public-view', $bookingInvoice) }}" class="btn btn-primary">Return to Invoice</a>
        
        <a href="{{ route('tenant.booking-invoices.public-view', $bookingInvoice) }}" class="btn btn-success">Retry Payment</a>
    </div>
</body>
</html>