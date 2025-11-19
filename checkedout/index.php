<?php
// Get form data
$params = array_merge($_GET, $_POST);
$first_name = $params['first_name'] ?? 'Valued Customer';
$last_name = $params['last_name'] ?? '';
$order_id = $params['order_id'] ?? '';
$back = $params['back'] ?? '#';
$customer_name = trim($first_name . ' ' . $last_name);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Declined</title>
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
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
            padding: 40px;
            text-align: center;
        }

        h1 {
            color: #2d5aa0;
            font-size: 28px;
            margin-bottom: 20px;
            text-transform: uppercase;
        }

        .message {
            color: #333;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .message p {
            margin-bottom: 15px;
        }

        .customer-name {
            font-weight: 600;
            color: #2d5aa0;
        }

        .highlight {
            background: #fff3cd;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: 600;
        }

        .payment-button {
            display: inline-block;
            background: #0066cc;
            color: white;
            padding: 15px 40px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 16px;
            font-weight: 600;
            transition: background 0.3s;
            margin-bottom: 20px;
        }

        .payment-button:hover {
            background: #0052a3;
        }

        .payment-button svg {
            vertical-align: middle;
            margin-right: 8px;
        }

        .back-link {
            margin-top: 20px;
        }

        .back-link a {
            color: #2d5aa0;
            text-decoration: none;
            font-size: 14px;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        .footer {
            color: white;
            text-align: center;
            margin-top: 30px;
            font-size: 13px;
        }

        .note {
            background: #f8f9fa;
            border-left: 4px solid #2d5aa0;
            padding: 15px;
            margin: 20px 0;
            text-align: left;
            color: #555;
            font-size: 14px;
        }

        .note strong {
            color: #2d5aa0;
        }

        @media (max-width: 768px) {
            .container {
                padding: 30px 20px;
            }

            h1 {
                font-size: 22px;
            }

            .message {
                font-size: 14px;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 25px 15px;
            }

            h1 {
                font-size: 20px;
            }

            .payment-button {
                padding: 12px 30px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Payment Declined</h1>

        <div class="message">
            <p>Dear <span class="customer-name"><?= htmlspecialchars($customer_name) ?></span>,</p>

            <p>Our payment provider failed to charge your card.</p>
            <p>No money was deducted from it.</p>

            <p>Click on the button below to finalize your payment using Bitcoins via our second payment provider.</p>
            <p>You will be automatically redirected there in <span class="highlight">few seconds</span>.</p>
        </div>

        <a href="#" class="payment-button">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="white">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.31-8.86c-1.77-.45-2.34-.94-2.34-1.67 0-.84.79-1.43 2.1-1.43 1.38 0 1.9.66 1.94 1.64h1.71c-.05-1.34-.87-2.57-2.49-2.97V5H10.9v1.69c-1.51.32-2.72 1.3-2.72 2.81 0 1.79 1.49 2.69 3.66 3.21 1.95.46 2.34 1.15 2.34 1.87 0 .53-.39 1.39-2.1 1.39-1.6 0-2.23-.72-2.32-1.64H8.04c.1 1.7 1.36 2.66 2.86 2.97V19h2.34v-1.67c1.52-.29 2.72-1.16 2.73-2.77-.01-2.2-1.9-2.96-3.66-3.42z"/>
            </svg>
            Pay Using CoinPayments
        </a>

        <div class="note">
            <p><strong>Note:</strong> Our primary payment provider processes payments through traditional banking channels. If your payment was declined, using our alternative cryptocurrency payment method often provides faster processing and higher success rates.</p>
        </div>

        <div class="back-link">
            <a href="<?= htmlspecialchars($back) ?>">← Back to the shop</a>
        </div>
    </div>

    <div class="footer">
        © 2025 All rights reserved.
    </div>
</body>
</html>
