<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Processing - Invoice {{ $bookingInvoice->invoice_number }}</title>
    <meta http-equiv="refresh" content="5">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
            max-width: 600px;
            width: 100%;
            text-align: center;
        }
        
        .spinner {
            width: 80px;
            height: 80px;
            margin: 0 auto 30px;
            border: 8px solid #f3f3f3;
            border-top: 8px solid #F59E0B;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .icon {
            font-size: 60px;
            color: #F59E0B;
            margin-bottom: 20px;
        }
        
        h1 {
            font-size: 28px;
            color: #1F2937;
            margin-bottom: 15px;
        }
        
        .message {
            font-size: 16px;
            color: #6B7280;
            margin-bottom: 30px;
            line-height: 1.8;
        }
        
        .invoice-details {
            background-color: #FEF3C7;
            padding: 20px;
            border-radius: 8px;
            margin: 30px 0;
            border-left: 4px solid #F59E0B;
        }
        
        .invoice-details strong {
            color: #92400E;
            display: block;
            margin-bottom: 5px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 10px 5px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background-color: #3B82F6;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #2563EB;
        }
        
        .btn-secondary {
            background-color: #6B7280;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #4B5563;
        }
        
        .alert {
            background-color: #FFFBEB;
            border: 1px solid #FCD34D;
            color: #92400E;
            padding: 15px;
            border-radius: 6px;
            margin-top: 20px;
            font-size: 13px;
        }
        
        .note {
            font-size: 12px;
            color: #9CA3AF;
            margin-top: 30px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="spinner"></div>
        
        <h1>Payment Processing</h1>
        
        <div class="message">
            <p>Your payment is being processed by PayFast. This usually takes a few seconds.</p>
            <p>Please wait while we confirm your payment...</p>
        </div>
        
        <div class="invoice-details">
            <strong>Invoice Number:</strong>
            <p>{{ $bookingInvoice->invoice_number }}</p>
            
            <strong>Amount Paid:</strong>
            <p>R {{ number_format($bookingInvoice->remaining_balance, 2) }}</p>
        </div>
        
        <div class="alert">
            <strong>⚠️ Important:</strong> Please do not close this page or navigate away. 
            This page will automatically refresh until your payment is confirmed.
        </div>
        
        <div style="margin-top: 40px;">
            <a href="{{ route('tenant.booking-invoices.show', $bookingInvoice->id) }}" class="btn btn-primary">
                Check Invoice Status
            </a>
            <a href="{{ route('tenant.guest-portal.index') }}" class="btn btn-secondary">
                Return to Home
            </a>
        </div>
        
        <p class="note">
            This page will automatically refresh every 5 seconds until payment is confirmed.
        </p>
    </div>
</body>
</html>
