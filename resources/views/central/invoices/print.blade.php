<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $invoice->invoice_number }} - {{ $app_name }}</title>
    
    <!-- Load Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        @page {
            margin: 1.5cm 2cm;
            size: A4 portrait;
        }
        
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        
        body {
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            background: #fff;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            padding: 0 15px;
            margin: 0 auto;
        }

        .logo-text {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        hr {
            border: none;
            border-top: 1px solid #dee2e6;
            margin: 20px 0;
            clear: both;
        }

        .invoice-header {
            border-bottom: 2px solid #dee2e6;
            margin-bottom: 30px;
            padding-bottom: 20px;
            width: 100%;
            display: block;
        }

        .text-muted {
            color: #6c757d;
        }

        .badge {
            padding: 5px 10px;
            font-size: 12px;
            border-radius: 4px;
            display: inline-block;
        }

        .badge.bg-success {
            background-color: #198754;
            color: #fff;
            border: 1px solid #198754;
        }

        .badge.bg-warning {
            background-color: #ffc107;
            color: #000;
            border: 1px solid #ffc107;
        }

        .badge.bg-danger {
            background-color: #dc3545;
            color: #fff;
            border: 1px solid #dc3545;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }

        .table th,
        .table td {
            padding: 12px;
            border: 1px solid #dee2e6;
            text-align: left;
        }

        .table th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
            color: #6c757d;
            background-color: #f8f9fa;
        }

        .table tfoot {
            font-weight: bold;
            background-color: #f8f9fa;
        }

        .table > :not(:first-child) {
            border-top: 2px solid #dee2e6;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            font-size: 11px;
            color: #6c757d;
            text-align: center;
        }

        .row {
            display: block;
            clear: both;
            margin: 0 -15px;
        }

        .row:after {
            content: "";
            display: table;
            clear: both;
        }

        .col-md-6 {
            float: left;
            width: 50%;
            padding: 0 15px;
        }

        .text-end {
            text-align: right;
        }

        .fw-semibold {
            font-weight: 600;
        }

        .mb-1 { margin-bottom: 5px; }
        .mb-2 { margin-bottom: 10px; }
        .mb-3 { margin-bottom: 15px; }
        .mb-4 { margin-bottom: 20px; }
        .mb-5 { margin-bottom: 25px; }

        @media print {
            body {
                min-width: 992px !important;
            }
            .container {
                min-width: 992px !important;
            }
            .badge {
                border: 1px solid #000;
            }
            .badge.bg-success {
                background-color: transparent !important;
                color: #000;
                border-color: #198754;
            }
            .badge.bg-warning {
                background-color: transparent !important;
                color: #000;
                border-color: #ffc107;
            }
            .badge.bg-danger {
                background-color: transparent !important;
                color: #000;
                border-color: #dc3545;
            }
            .table {
                width: 100% !important;
                break-inside: auto !important;
            }
            .table td,
            .table th {
                background-color: transparent !important;
            }
        }
    </style>
</head>
<body class="bg-white">
    <div class="container py-5">
        <!-- Invoice Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center invoice-header">
                    <div>
                        <div class="logo-text mb-2">{{ $app_name }}</div>
                        <div class="text-muted">
                            {{ $app_address_line_1 }}<br>
                            @if($app_address_line_2){{ $app_address_line_2 }}<br>@endif
                            {{ $app_address_city }}, {{ $app_address_state }} {{ $app_address_zip }}
                        </div>
                    </div>
                    <div class="text-end">
                        <h5 class="text-uppercase text-muted mb-2">Invoice</h5>
                        <div class="mb-1">#{{ $invoice->invoice_number }}</div>
                        <div class="text-muted">Issued: {{ $invoice->created_at->format('M d, Y') }}</div>
                        <div class="text-muted">Due: {{ \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Billing Info -->
        <div class="row mb-5">
            <div class="col-md-6">
                <h6 class="text-uppercase text-muted mb-3">Billed To:</h6>
                <h5 class="mb-2">{{ $tenant_name }}</h5>
                <div class="text-muted">
                    @if($tenant_address_line_1){{ $tenant_address_line_1 }}<br>@endif
                    @if($tenant_address_line_2){{ $tenant_address_line_2 }}<br>@endif
                    @if($tenant_address_city || $tenant_address_state)
                        {{ $tenant_address_city }}@if($tenant_address_state), {{ $tenant_address_state }}@endif {{ $tenant_address_zip }}<br>
                    @endif
                    @if($invoice->tenant->phone)Phone: {{ $invoice->tenant->phone }}<br>@endif
                    Email: {{ $invoice->tenant->email }}
                </div>
            </div>
            <div class="col-md-6 text-md-end">
                <h6 class="text-uppercase text-muted mb-3">Invoice Details:</h6>
                <div class="text-muted">
                    <div class="mb-1">
                        <span class="text-dark">Subscription ID:</span> {{ $invoice->subscription->id ?? 'N/A' }}
                    </div>
                    <div class="mb-1">
                        <span class="text-dark">Plan:</span> 
                        {{ $invoice->subscription->plan->name ?? 'N/A' }}
                    </div>
                    <div class="mb-1">
                        <span class="text-dark">Billing Cycle:</span> 
                        {{ ucfirst($invoice->subscription->billing_cycle ?? 'N/A') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice Items -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered">
                <thead class="bg-light">
                    <tr>
                        <th scope="col" class="text-uppercase">Description</th>
                        <th scope="col" class="text-uppercase text-end" style="width: 150px">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $invoice->subscription->plan->name ?? 'Subscription Plan' }}</div>
                            <div class="text-muted">
                                {{ ucfirst($invoice->subscription->billing_cycle ?? '') }} billing cycle
                                ({{ $invoice->subscription->start_date ? \Carbon\Carbon::parse($invoice->subscription->start_date)->format('M d, Y') : 'N/A' }} 
                                - {{ $invoice->subscription->end_date ? \Carbon\Carbon::parse($invoice->subscription->end_date)->format('M d, Y') : 'N/A' }})
                            </div>
                        </td>
                        <td class="text-end align-middle">{{ format_price($invoice->amount) }}</td>
                    </tr>
                </tbody>
                <tfoot class="bg-light">
                    @if($invoice->tax_amount > 0)
                    <tr>
                        <td class="text-end">Subtotal:</td>
                        <td class="text-end">{{ format_price($invoice->subtotal_amount) }}</td>
                    </tr>
                    <tr>
                        <td class="text-end">
                            Tax ({{ $invoice->tax_name }}
                            @if($invoice->tax_type === 'percentage')
                                - {{ number_format($invoice->tax_rate, 2) }}%
                            @else
                                - Fixed Amount
                            @endif
                            @if($invoice->tax_inclusive)
                                <span style="color: #0d6efd;"> - Inclusive</span>
                            @endif
                            ):
                        </td>
                        <td class="text-end">{{ format_price($invoice->tax_amount) }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="text-end text-uppercase fw-semibold">Total</td>
                        <td class="text-end fw-bold">{{ format_price($invoice->amount) }}</td>
                    </tr>
                    @if($invoice->payments && $invoice->payments->where('status', 'completed')->count() > 0)
                    @php
                        $totalPaid = $invoice->payments->where('status', 'completed')->sum('amount');
                        $balance = $invoice->amount - $totalPaid;
                    @endphp
                    <tr>
                        <td class="text-end">Amount Paid:</td>
                        <td class="text-end" style="color: #198754;">{{ format_price($totalPaid) }}</td>
                    </tr>
                    <tr>
                        <td class="text-end fw-semibold">Balance Due:</td>
                        <td class="text-end fw-bold" style="color: {{ $balance > 0 ? '#dc3545' : '#198754' }};">{{ format_price($balance) }}</td>
                    </tr>
                    @endif
                </tfoot>
            </table>
        </div>

        <!-- Status -->
        <div class="row mb-4">
            <div class="col-12">
                <h6 class="text-uppercase text-muted mb-2">Status</h6>
                @if($invoice->status === 'paid')
                    <span class="badge bg-success">Paid</span>
                @elseif($invoice->status === 'pending')
                    <span class="badge bg-warning text-dark">Pending</span>
                @elseif($invoice->status === 'overdue')
                    <span class="badge bg-danger">Overdue</span>
                @else
                    <span class="badge bg-secondary">{{ ucfirst($invoice->status) }}</span>
                @endif
            </div>
        </div>

        @if($invoice->notes)
            <div class="mt-4">
                <h6 class="text-uppercase text-muted mb-2">Notes</h6>
                <div class="text-muted">{{ $invoice->notes }}</div>
            </div>
        @endif

        <!-- Footer -->
        <div class="footer text-center">
            <p class="mb-0">Thank you for your business!</p>
            @if($invoice->status !== 'paid')
                <p class="mb-0 text-muted">
                    Please process this payment by the due date: {{ \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') }}
                </p>
            @endif
        </div>
    </div>

    <script>
        // Automatically trigger print when the page loads
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
