<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting to PayFast...</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
            max-width: 500px;
            text-align: center;
        }
        
        .spinner {
            width: 60px;
            height: 60px;
            margin: 0 auto 30px;
            border: 6px solid #f3f3f3;
            border-top: 6px solid #EF4444;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        h1 {
            font-size: 24px;
            color: #1F2937;
            margin-bottom: 15px;
        }
        
        p {
            color: #6B7280;
            font-size: 14px;
            margin-bottom: 20px;
        }
        
        .logo {
            max-width: 150px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="spinner"></div>
        <h1>Redirecting to PayFast</h1>
        <p>Please wait while we redirect you to the secure payment page...</p>
        <p><small>If you are not redirected automatically, please click the button below.</small></p>
        
        <form id="payfast-form" action="{{ $action }}" method="POST">
            @foreach($data as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <button type="submit" style="
                background-color: #EF4444;
                color: white;
                border: none;
                padding: 12px 24px;
                border-radius: 6px;
                font-size: 14px;
                font-weight: 600;
                cursor: pointer;
                margin-top: 20px;
            ">
                Continue to PayFast
            </button>
        </form>
    </div>
    
    <script>
        // Auto-submit the form after a short delay
        setTimeout(function() {
            document.getElementById('payfast-form').submit();
        }, 1000);
    </script>
</body>
</html>
