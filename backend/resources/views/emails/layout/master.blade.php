<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Rental Management System' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f4f4f4;
        }
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        .email-header {
            background: linear-gradient(135deg, #2563EB 0%, #1E40AF 100%);
            padding: 30px 20px;
            text-align: center;
        }
        .email-header h1 {
            color: #ffffff;
            font-size: 24px;
            font-weight: 600;
            margin: 0;
        }
        .email-body {
            padding: 40px 30px;
        }
        .email-body h2 {
            color: #1E40AF;
            font-size: 20px;
            margin-bottom: 20px;
        }
        .email-body p {
            color: #555555;
            margin-bottom: 15px;
            font-size: 15px;
        }
        .button {
            display: inline-block;
            padding: 14px 30px;
            background-color: #2563EB;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 20px 0;
            transition: background-color 0.3s;
        }
        .button:hover {
            background-color: #1E40AF;
        }
        .info-box {
            background-color: #F0F9FF;
            border-left: 4px solid #2563EB;
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .warning-box {
            background-color: #FEF3C7;
            border-left: 4px solid #F59E0B;
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .success-box {
            background-color: #ECFDF5;
            border-left: 4px solid #10B981;
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .email-footer {
            background-color: #F8FAFC;
            padding: 30px 20px;
            text-align: center;
            border-top: 1px solid #E5E7EB;
        }
        .email-footer p {
            color: #6B7280;
            font-size: 13px;
            margin-bottom: 10px;
        }
        .email-footer a {
            color: #2563EB;
            text-decoration: none;
        }
        .divider {
            height: 1px;
            background-color: #E5E7EB;
            margin: 30px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #E5E7EB;
        }
        table th {
            background-color: #F8FAFC;
            font-weight: 600;
            color: #374151;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <!-- Header -->
        <div class="email-header">
            <h1>{{ config('app.name') }}</h1>
        </div>

        <!-- Body -->
        <div class="email-body">
            @yield('content')
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p><strong>{{ config('app.name') }}</strong></p>
            <p>
                Need help? Contact us at 
                <a href="mailto:support@rms.com">support@rms.com</a>
            </p>
            <p style="margin-top: 20px; font-size: 12px;">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
