<?php
$orderId = $_GET['order_id'] ?? '';
$successUrl = $_GET['success'] ?? '';

// Security: Block direct access without parameters
if (empty($orderId) || empty($successUrl)) {
    http_response_code(404);
    die('<!DOCTYPE html>
<html>
<head>
    <title>404 Not Found</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f5f5f5; }
        h1 { color: #333; }
    </style>
</head>
<body>
    <h1>404 - Page Not Found</h1>
    <p>The page you are looking for does not exist.</p>
</body>
</html>');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
    <meta name="googlebot" content="noindex, nofollow">
    <link rel="icon" type="image/png" href="https://online-bill.click/images/encrypted.png">
    <title>Thank You - Order Confirmation</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #2d5aa0 0%, #1e3c72 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
            padding: 50px 40px;
            text-align: center;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: #4CAF50;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            animation: scaleIn 0.5s ease-out;
        }

        .success-icon svg {
            width: 50px;
            height: 50px;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }

        h1 {
            color: #2d5aa0;
            font-size: 32px;
            margin-bottom: 20px;
        }

        .message {
            color: #333;
            font-size: 18px;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .order-id {
            font-weight: 600;
            color: #2d5aa0;
            font-size: 20px;
        }

        .redirect-message {
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            margin-top: 30px;
            color: #666;
            font-size: 14px;
        }

        .countdown {
            font-size: 48px;
            font-weight: bold;
            color: #2d5aa0;
            margin: 20px 0;
        }

        .manual-link {
            margin-top: 20px;
        }

        .manual-link a {
            color: #2d5aa0;
            text-decoration: none;
            font-weight: 600;
        }

        .manual-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .container {
                padding: 40px 30px;
            }

            h1 {
                font-size: 26px;
            }

            .message {
                font-size: 16px;
            }

            .countdown {
                font-size: 36px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-icon">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z"
                      stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
            <h1>Thank you for your order!</h1>

            <div class="message">
                <p>Your order <span class="order-id">#<?= htmlspecialchars($orderId) ?></span> has been placed successfully.</p>
                <p style="margin-top: 15px;">Within 2 days you will receive a tracking number to track your order.</p>
            </div>

            <div class="redirect-message">
                <p>You will be redirected to the order page in</p>
                <div class="countdown" id="countdown">5</div>
                <p>seconds...</p>
            </div>

            <div class="manual-link">
                <p>Don't want to wait? <a href="<?= htmlspecialchars($successUrl) ?>" id="manual-link">Go now</a></p>
            </div>

    <script>
        // Countdown timer
        let seconds = 5;
        const countdownEl = document.getElementById('countdown');
        const redirectUrl = <?= json_encode($successUrl) ?>;

        const countdown = setInterval(() => {
            seconds--;
            countdownEl.textContent = seconds;

            if (seconds <= 0) {
                clearInterval(countdown);
                window.location.href = redirectUrl;
            }
        }, 1000);

        // Allow manual redirect
        document.getElementById('manual-link').addEventListener('click', (e) => {
            clearInterval(countdown);
        });
    </script>
</body>
</html>
