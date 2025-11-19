<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Received - Thank You</title>
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
            padding: 50px;
            max-width: 600px;
            width: 100%;
            text-align: center;
        }
        
        .checkmark {
            width: 80px;
            height: 80px;
            margin: 0 auto 30px;
            border-radius: 50%;
            background-color: #10B981;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: scaleIn 0.5s ease-out;
        }
        
        .checkmark svg {
            width: 50px;
            height: 50px;
            stroke: white;
            stroke-width: 3;
            stroke-linecap: round;
            stroke-linejoin: round;
            fill: none;
            animation: drawCheck 0.5s ease-out 0.2s forwards;
            stroke-dasharray: 100;
            stroke-dashoffset: 100;
        }
        
        @keyframes scaleIn {
            0% { transform: scale(0); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        @keyframes drawCheck {
            to { stroke-dashoffset: 0; }
        }
        
        h1 {
            font-size: 32px;
            color: #1F2937;
            margin-bottom: 15px;
        }
        
        .subtitle {
            font-size: 18px;
            color: #10B981;
            margin-bottom: 30px;
            font-weight: 600;
        }
        
        .message {
            font-size: 16px;
            color: #6B7280;
            margin-bottom: 30px;
            line-height: 1.8;
        }
        
        .info-box {
            background-color: #F3F4F6;
            padding: 20px;
            border-radius: 8px;
            margin: 30px 0;
            border-left: 4px solid #10B981;
        }
        
        .info-box p {
            margin: 10px 0;
            color: #374151;
            font-size: 14px;
        }
        
        .info-box strong {
            color: #1F2937;
        }
        
        .btn {
            display: inline-block;
            padding: 14px 28px;
            margin: 10px 5px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 15px;
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
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }
        
        .btn-secondary {
            background-color: #6B7280;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #4B5563;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(107, 114, 128, 0.4);
        }
        
        .icon-list {
            text-align: left;
            margin: 30px 0;
        }
        
        .icon-list-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #F9FAFB;
            border-radius: 6px;
        }
        
        .icon-list-item svg {
            width: 24px;
            height: 24px;
            min-width: 24px;
            margin-right: 12px;
            color: #10B981;
        }
        
        .icon-list-item span {
            color: #4B5563;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="checkmark">
            <svg viewBox="0 0 52 52">
                <polyline points="14 27 22 35 38 19"/>
            </svg>
        </div>
        
        <h1>Thank You!</h1>
        <p class="subtitle">Your payment has been submitted</p>
        
        <div class="message">
            <p>Your payment is being processed by PayFast. You will receive a confirmation email shortly once your payment has been verified.</p>
        </div>
        
        <div class="info-box">
            <p><strong>What happens next?</strong></p>
            <div class="icon-list">
                <div class="icon-list-item">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Your payment is being verified (usually takes a few seconds)</span>
                </div>
                <div class="icon-list-item">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span>You'll receive a payment confirmation email</span>
                </div>
                <div class="icon-list-item">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Your booking will be confirmed automatically</span>
                </div>
            </div>
        </div>
        
        <div style="margin-top: 40px;">
            <a href="{{ route('tenant.guest-portal.index') }}" class="btn btn-primary">
                <span style="margin-right: 8px;">🏠</span> Return to Home
            </a>
        </div>
        
        <p style="margin-top: 30px; font-size: 13px; color: #9CA3AF;">
            If you have any questions, please contact us at 
            <a href="mailto:{{ tenant_support_email() }}" style="color: #3B82F6;">{{ tenant_support_email() }}</a>
        </p>
    </div>
</body>
</html>
