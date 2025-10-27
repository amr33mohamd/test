<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProxyScrape Pricing Calculator</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 20px;
            padding: 50px;
            max-width: 800px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 2.5em;
        }
        .subtitle {
            color: #667eea;
            font-size: 1.2em;
            margin-bottom: 30px;
            font-weight: 600;
        }
        .timer {
            background: #fff3cd;
            border: 2px solid #ffc107;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 30px;
        }
        .timer strong {
            color: #d32f2f;
            font-size: 1.3em;
        }
        .task-info {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin: 25px 0;
            border-left: 4px solid #667eea;
        }
        .task-info h3 {
            color: #333;
            margin-bottom: 15px;
        }
        .task-info ul {
            list-style: none;
            padding-left: 0;
        }
        .task-info li {
            padding: 8px 0;
            color: #555;
        }
        .task-info li:before {
            content: "✓ ";
            color: #4CAF50;
            font-weight: bold;
            margin-right: 8px;
        }
        .code-block {
            background: #2d3748;
            color: #68d391;
            padding: 20px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            margin: 20px 0;
            overflow-x: auto;
        }
        .code-block code {
            color: #68d391;
        }
        .file-path {
            background: #2d3748;
            color: #68d391;
            padding: 3px 8px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
        }
        p {
            color: #666;
            line-height: 1.8;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>💰 ProxyScrape Pricing Calculator</h1>
        <p class="subtitle">Interview Task - 45 Minutes</p>

        <div class="timer">
            <strong>⏱️ Duration: 45 minutes</strong>
        </div>

        <p>
            Build a usage-based pricing calculator with <strong>tiered pricing</strong> and <strong>stacked discounts</strong>.
        </p>

        <div class="task-info">
            <h3>Your Mission:</h3>
            <ul>
                <li>Implement tiered pricing (3 plans)</li>
                <li>Apply loyalty discounts (5% or 10%)</li>
                <li>Apply volume discounts (2% per 100GB, max 10%)</li>
                <li>Stack discounts correctly (sequential, not additive)</li>
                <li>Compare plans and recommend cheapest</li>
            </ul>
        </div>

        <div class="task-info" style="border-left-color: #4CAF50;">
            <h3>Quick Start:</h3>
            <ul>
                <li>Open <span class="file-path">app/Services/PricingCalculator.php</span></li>
                <li>Implement the <strong>5 methods</strong> marked with TODO</li>
                <li>Run tests: <span class="file-path">vendor/bin/phpunit</span></li>
                <li>All 13 tests should pass ✅</li>
            </ul>
        </div>

        <div class="code-block">
# Setup<br>
composer install<br>
php artisan key:generate<br>
<br>
# Test your implementation<br>
<code>vendor/bin/phpunit</code><br>
<br>
# Expected output:<br>
# OK (13 tests, 42 assertions) ✅
        </div>

        <div class="task-info" style="border-left-color: #ffc107;">
            <h3>Example Calculation:</h3>
            <p style="color: #333; font-family: 'Courier New', monospace; line-height: 1.6;">
                150 GB Enterprise, 120 GB last month<br><br>
                Base: (100×$4) + (50×$3) = <strong>$550</strong><br>
                Loyalty 10%: $550 × 0.9 = <strong>$495</strong><br>
                Volume 2%: $495 × 0.98 = <strong>$485.10</strong> ✓
            </p>
        </div>

        <p style="text-align: center; margin-top: 40px; font-size: 1.2em; color: #667eea; font-weight: 600;">
            Good luck! 🚀
        </p>
    </div>
</body>
</html>
