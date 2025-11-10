<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .invoice-header {
            border-bottom: 3px solid #3B82F6;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .company-info {
            float: left;
            width: 60%;
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
            float: right;
            width: 35%;
            text-align: right;
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
        }
        
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
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
        
        .services-table .text-right {
            text-align: right;
        }
        
        .services-table .text-center {
            text-align: center;
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
        .status-overdue { background-color: #FEE2E2; color: #991B1B; }
        .status-cancelled { background-color: #F3F4F6; color: #6B7280; }
        
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #E5E7EB;
            text-align: center;
            color: #6B7280;
            font-size: 10px;
        }
        
        .subscription-info {
            background-color: #EFF6FF;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        
        .subscription-info-title {
            font-weight: bold;
            color: #1E40AF;
            margin-bottom: 8px;
        }
        
        .payment-info {
            background-color: #ECFDF5;
            padding: 10px;
            border-radius: 3px;
            margin-top: 5px;
            font-size: 11px;
            color: #047857;
        }
        
        .notes-section {
            margin-top: 30px;
            padding: 20px;
            background-color: #FFFBEB;
            border-left: 4px solid #F59E0B;
        }
        
        .notes-title {
            font-weight: bold;
            color: #92400E;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="invoice-header clearfix">
        <div class="company-info">
            <div class="company-name">{{ config('app.name', 'Multi-Tenant Platform') }}</div>
            <div class="company-details">
                Subscription Management Portal<br>
                Email: {{ config('mail.from.address') }}
            </div>
        </div>
        <div class="invoice-info">
            <div class="invoice-title">INVOICE</div>
            <div class="invoice-details">
                <strong>Invoice #:</strong> {{ $invoice->invoice_number }}<br>
                <strong>Date:</strong> {{ $invoice->invoice_date->format('F j, Y') }}<br>
                <strong>Due Date:</strong> {{ $invoice->due_date->format('F j, Y') }}<br>
                <span class="status-badge status-{{ $invoice->status }}">
                    {{ ucfirst(str_replace('_', ' ', $invoice->status)) }}
                </span>
            </div>
        </div>
    </div>

    <div class="bill-to">
        <div class="bill-to-title">BILL TO:</div>
        <div class="customer-info">
            <strong>{{ $tenant->name }}</strong><br>
            @if($tenant->contact_person)
            {{ $tenant->contact_person }}<br>
            @endif
            {{ $tenant->email }}<br>
            @if($tenant->contact_number)
            {{ $tenant->contact_number }}<br>
            @endif
            @if($tenant->address)
            {{ $tenant->address }}
            @endif
        </div>
    </div>

    @if($invoice->subscription)
    <div class="subscription-info">
        <div class="subscription-info-title">Subscription Information</div>
        <div>
            <strong>Plan:</strong> {{ $invoice->subscription->plan->name ?? 'N/A' }}<br>
            <strong>Billing Cycle:</strong> {{ ucfirst($invoice->subscription->billing_cycle ?? 'N/A') }}<br>
            <strong>Period:</strong> {{ $invoice->subscription->start_date?->format('M j, Y') }} - {{ $invoice->subscription->end_date?->format('M j, Y') }}
        </div>
    </div>
    @endif

    <table class="services-table">
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-center">Invoice Date</th>
                <th class="text-center">Due Date</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>{{ $invoice->notes ?? 'Subscription Payment' }}</strong>
                    @if($invoice->subscription && $invoice->subscription->plan)
                    <br>
                    <span style="color: #6B7280;">
                        {{ $invoice->subscription->plan->name }} - {{ ucfirst($invoice->subscription->billing_cycle) }} Plan
                    </span>
                    @endif
                </td>
                <td class="text-center">{{ $invoice->invoice_date->format('M j, Y') }}</td>
                <td class="text-center">{{ $invoice->due_date->format('M j, Y') }}</td>
                <td class="text-right">{{ $currency }} {{ number_format($invoice->amount, 2) }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3" class="text-right"><strong>TOTAL AMOUNT:</strong></td>
                <td class="text-right total-amount">{{ $currency }} {{ number_format($invoice->amount, 2) }}</td>
            </tr>
            @if($invoice->payments && $invoice->payments->where('status', 'completed')->count() > 0)
            @php
                $totalPaid = $invoice->payments->where('status', 'completed')->sum('amount');
                $balance = $invoice->amount - $totalPaid;
            @endphp
            <tr>
                <td colspan="3" class="text-right">Amount Paid:</td>
                <td class="text-right" style="color: #059669;">{{ $currency }} {{ number_format($totalPaid, 2) }}</td>
            </tr>
            <tr>
                <td colspan="3" class="text-right"><strong>Balance Due:</strong></td>
                <td class="text-right"><strong>{{ $currency }} {{ number_format($balance, 2) }}</strong></td>
            </tr>
            @endif
        </tfoot>
    </table>

    @if($invoice->payments && $invoice->payments->where('status', 'completed')->count() > 0)
    <div class="payment-info">
        <strong>Payment History:</strong><br>
        @foreach($invoice->payments->where('status', 'completed') as $payment)
        - {{ $currency }} {{ number_format($payment->amount, 2) }} paid on {{ $payment->created_at->format('F j, Y') }}
        @if($payment->payment_method)
        via {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
        @endif
        @if($payment->payment_reference)
        (Ref: {{ $payment->payment_reference }})
        @endif
        <br>
        @endforeach
    </div>
    @endif

    @if($invoice->notes)
    <div class="notes-section">
        <div class="notes-title">Notes:</div>
        {{ $invoice->notes }}
    </div>
    @endif

    <div class="footer">
        <div>Thank you for your business!</div>
        <div>Invoice generated on {{ now()->format('F j, Y \a\t g:i A') }}</div>
        <div>{{ config('app.name', 'Multi-Tenant Platform') }}</div>
    </div>
</body>
</html>
