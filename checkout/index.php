<?php
// Parse URL path for WooCommerce plugin format: /checkout/RESOURCE/TOTAL_CENTS/ORDER_ID/HASH
$request_uri = $_SERVER['REQUEST_URI'] ?? '';
$path_parts = explode('?', $request_uri)[0]; // Remove query string
$segments = explode('/', trim($path_parts, '/'));

// Extract path parameters if present
$resource = null;
$total_cents = null;
$path_order_id = null;
$hash = null;

if (count($segments) >= 5 && $segments[0] === 'checkout') {
    $resource = $segments[1] ?? null;
    $total_cents = $segments[2] ?? null;
    $path_order_id = $segments[3] ?? null;
    $hash = $segments[4] ?? null;
}

// Get URL parameters (GET params override path params)
$params = [
    'first_name' => $_GET['first_name'] ?? '',
    'last_name' => $_GET['last_name'] ?? '',
    'country' => $_GET['country'] ?? '',
    'state' => $_GET['state'] ?? '',
    'address' => $_GET['address'] ?? '',
    'city' => $_GET['city'] ?? '',
    'zip' => $_GET['zip'] ?? '',
    'email' => $_GET['email'] ?? '',
    'phone' => $_GET['phone'] ?? '',
    'total' => $_GET['total'] ?? $total_cents ?? '0',
    'order_id' => $_GET['id'] ?? $path_order_id ?? '',
    'item_name' => $_GET['item_name'] ?? 'Order #' . ($path_order_id ?? ''),
    'success' => $_GET['success'] ?? '',
    'failure' => $_GET['failure'] ?? '',
    'back' => $_GET['back'] ?? '#',
    'resource' => $resource,
    'hash' => $hash
];

$total = floatval($params['total']) / 100;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Checkout Page</title>
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
            max-width: 900px;
            width: 100%;
            padding: 30px;
            margin: 20px 0;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
        }

        .header h1 {
            color: #2d5aa0;
            font-size: 24px;
            margin-bottom: 8px;
        }

        .header p {
            color: #666;
            font-size: 13px;
        }

        .order-summary {
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 25px;
        }

        .order-summary table {
            width: 100%;
            border-collapse: collapse;
        }

        .order-summary th {
            background: #e9ecef;
            padding: 10px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #dee2e6;
        }

        .order-summary td {
            padding: 10px;
            border-bottom: 1px solid #e0e0e0;
        }

        .total-row {
            background: #2d5aa0;
            color: white;
            font-weight: 600;
            text-align: right;
        }

        .total-row td {
            border-bottom: none;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        .form-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
        }

        .form-section h2 {
            color: #2d5aa0;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-size: 14px;
            font-weight: 500;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #2d5aa0;
            box-shadow: 0 0 0 3px rgba(45, 90, 160, 0.1);
        }

        .required::after {
            content: " *";
            color: red;
        }

        .payment-logos {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 15px;
        }

        .payment-logo {
            padding: 5px 12px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-weight: 600;
            font-size: 12px;
        }

        .payment-logo.visa {
            color: #1a1f71;
        }

        .payment-logo.mastercard {
            color: #eb001b;
        }

        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .phone-input {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 10px;
        }

        .phone-input select {
            width: 80px;
        }

        .submit-btn {
            width: 100%;
            padding: 15px;
            background: #2d5aa0;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 20px;
        }

        .submit-btn:hover {
            background: #1e3c72;
        }

        .submit-btn:active {
            transform: scale(0.98);
        }

        .submit-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }

        /* Payment Processing Overlay */
        .payment-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .payment-overlay.active {
            display: flex;
        }

        .payment-loader {
            background: white;
            border-radius: 12px;
            padding: 40px;
            text-align: center;
            max-width: 400px;
            width: 90%;
        }

        .loader-spinner {
            width: 60px;
            height: 60px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #2d5aa0;
            border-radius: 50%;
            margin: 0 auto 20px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loader-text {
            color: #333;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .loader-subtext {
            color: #666;
            font-size: 14px;
        }

        .loader-steps {
            margin-top: 20px;
            text-align: left;
        }

        .loader-step {
            display: flex;
            align-items: center;
            padding: 8px 0;
            color: #999;
            font-size: 14px;
        }

        .loader-step.active {
            color: #2d5aa0;
            font-weight: 600;
        }

        .loader-step.completed {
            color: #28a745;
        }

        .loader-step-icon {
            width: 20px;
            height: 20px;
            margin-right: 10px;
            border: 2px solid currentColor;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        .loader-step.completed .loader-step-icon::before {
            content: "✓";
        }

        .footer-note {
            text-align: center;
            margin-top: 15px;
            color: #666;
            font-size: 13px;
        }

        .back-link {
            text-align: center;
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
            margin-top: 20px;
            font-size: 13px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .header h1 {
                font-size: 20px;
            }

            .row {
                grid-template-columns: 1fr;
            }

            .phone-input {
                grid-template-columns: 1fr;
            }

            .phone-input select {
                width: 100%;
            }

            .order-summary {
                overflow-x: auto;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }

            .container {
                padding: 15px;
            }

            .form-section {
                padding: 15px;
            }

            .header h1 {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>SECURE CHECKOUT PAGE</h1>
            <p>Your data is safely encrypted and is safe</p>
        </div>

        <div class="order-summary">
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th style="text-align: right;">Price (USD)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?= htmlspecialchars($params['order_id'] ?: $params['item_name']) ?></td>
                        <td style="text-align: right;">$ <?= number_format($total, 2) ?></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td>Your order sum is:</td>
                        <td>$ <?= number_format($total, 2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <form id="checkout-form" method="POST" action="/process-payment.php">
            <div class="form-grid">
                <div class="form-section">
                    <h2>Billing Address</h2>

                    <div class="form-group">
                        <label class="required">First Name</label>
                        <input type="text" name="first_name" value="<?= htmlspecialchars($params['first_name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="required">Last name</label>
                        <input type="text" name="last_name" value="<?= htmlspecialchars($params['last_name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="required">State / Province</label>
                        <select name="state" required>
                            <option value="">Select State</option>
                            <?php
                            $states = [
                                'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas', 'CA' => 'California',
                                'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware', 'FL' => 'Florida', 'GA' => 'Georgia',
                                'HI' => 'Hawaii', 'ID' => 'Idaho', 'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa',
                                'KS' => 'Kansas', 'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine', 'MD' => 'Maryland',
                                'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi', 'MO' => 'Missouri',
                                'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada', 'NH' => 'New Hampshire', 'NJ' => 'New Jersey',
                                'NM' => 'New Mexico', 'NY' => 'New York', 'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio',
                                'OK' => 'Oklahoma', 'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina',
                                'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah', 'VT' => 'Vermont',
                                'VA' => 'Virginia', 'WA' => 'Washington', 'WV' => 'West Virginia', 'WI' => 'Wisconsin', 'WY' => 'Wyoming'
                            ];
                            foreach ($states as $code => $name) {
                                $selected = $params['state'] === $code ? 'selected' : '';
                                echo "<option value=\"$code\" $selected>$name</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="required">Country</label>
                        <select name="country" required>
                            <option value="">Select Country</option>
                            <?php
                            $countries = [
                                'US' => 'United States', 'GB' => 'United Kingdom', 'CA' => 'Canada', 'AU' => 'Australia',
                                'DE' => 'Germany', 'FR' => 'France', 'IT' => 'Italy', 'ES' => 'Spain', 'NL' => 'Netherlands',
                                'BE' => 'Belgium', 'AT' => 'Austria', 'CH' => 'Switzerland', 'IE' => 'Ireland', 'SE' => 'Sweden',
                                'NO' => 'Norway', 'DK' => 'Denmark', 'FI' => 'Finland', 'PL' => 'Poland', 'PT' => 'Portugal', 'GR' => 'Greece'
                            ];
                            foreach ($countries as $code => $name) {
                                $selected = $params['country'] === $code ? 'selected' : '';
                                echo "<option value=\"$code\" $selected>$name</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="required">Zip or postal code</label>
                        <input type="text" name="zip" value="<?= htmlspecialchars($params['zip']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="required">City</label>
                        <input type="text" name="city" value="<?= htmlspecialchars($params['city']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="required">Street address</label>
                        <input type="text" name="address" value="<?= htmlspecialchars($params['address']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="required">Phone</label>
                        <div class="phone-input">
                            <select name="phone_country">
                                <option value="+1">🇺🇸 +1</option>
                                <option value="+44">🇬🇧 +44</option>
                                <option value="+61">🇦🇺 +61</option>
                                <option value="+49">🇩🇪 +49</option>
                                <option value="+33">🇫🇷 +33</option>
                            </select>
                            <input type="tel" name="phone" value="<?= htmlspecialchars($params['phone']) ?>" placeholder="510-351-5800" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="required">E-mail</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($params['email']) ?>" required>
                    </div>
                </div>

                <div class="form-section">
                    <h2>Payment Information</h2>

                    <div class="payment-logos">
                        <div class="payment-logo visa">VISA</div>
                        <div class="payment-logo mastercard">MC</div>
                    </div>

                    <div class="form-group">
                        <label class="required">Credit card No</label>
                        <input type="text" name="card_number" id="card_number" placeholder="1234 5678 9012 3456" maxlength="19" required>
                    </div>

                    <div class="row">
                        <div class="form-group">
                            <label class="required">Expiration date</label>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                <select name="exp_month" required>
                                    <option value="">MM</option>
                                    <?php for($i = 1; $i <= 12; $i++): ?>
                                        <option value="<?= sprintf('%02d', $i) ?>"><?= sprintf('%02d', $i) ?></option>
                                    <?php endfor; ?>
                                </select>
                                <select name="exp_year" required>
                                    <option value="">YYYY</option>
                                    <?php for($i = 0; $i < 15; $i++): ?>
                                        <option value="<?= date('Y') + $i ?>"><?= date('Y') + $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="required">CVC2/CVV2</label>
                            <input type="text" name="cvv" id="cvv" placeholder="123" maxlength="4" required>
                        </div>
                    </div>

                    <div class="footer-note">
                        Please note: fields marked with <span style="color: red;">*</span> are required to be filled in to complete your order
                    </div>

                    <input type="hidden" name="order_id" value="<?= htmlspecialchars($params['order_id']) ?>">
                    <input type="hidden" name="total" value="<?= htmlspecialchars($params['total']) ?>">
                    <input type="hidden" name="success" value="<?= htmlspecialchars($params['success']) ?>">
                    <input type="hidden" name="failure" value="<?= htmlspecialchars($params['failure']) ?>">
                    <input type="hidden" name="back" value="<?= htmlspecialchars($params['back']) ?>">

                    <button type="submit" class="submit-btn">Pay Now</button>
                </div>
            </div>
        </form>

        <div class="back-link">
            <a href="<?= htmlspecialchars($params['back']) ?>">← Back to the shop</a>
        </div>
    </div>

    <div class="footer">
        © 2025 All rights reserved.
    </div>

    <!-- Payment Processing Overlay -->
    <div class="payment-overlay" id="paymentOverlay">
        <div class="payment-loader">
            <div class="loader-spinner"></div>
            <div class="loader-text">Processing Payment...</div>
            <div class="loader-subtext">Please do not close this window</div>
            <div class="loader-steps">
                <div class="loader-step" id="step1">
                    <div class="loader-step-icon"></div>
                    <span>Validating card details</span>
                </div>
                <div class="loader-step" id="step2">
                    <div class="loader-step-icon"></div>
                    <span>Contacting payment processor</span>
                </div>
                <div class="loader-step" id="step3">
                    <div class="loader-step-icon"></div>
                    <span>Finalizing transaction</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('card_number').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            e.target.value = formattedValue;
        });

        document.getElementById('card_number').addEventListener('keypress', function(e) {
            if (!/\d/.test(e.key) && e.key !== 'Backspace' && e.key !== 'Delete') {
                e.preventDefault();
            }
        });

        document.getElementById('cvv').addEventListener('keypress', function(e) {
            if (!/\d/.test(e.key) && e.key !== 'Backspace' && e.key !== 'Delete') {
                e.preventDefault();
            }
        });

        // Payment processing animation
        const form = document.getElementById('checkout-form');
        const overlay = document.getElementById('paymentOverlay');
        const submitBtn = form.querySelector('.submit-btn');

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            // Disable button
            submitBtn.disabled = true;
            submitBtn.textContent = 'Processing...';

            // Show overlay
            overlay.classList.add('active');

            // Animate steps
            const steps = [
                { id: 'step1', delay: 300 },
                { id: 'step2', delay: 1000 },
                { id: 'step3', delay: 1700 }
            ];

            steps.forEach(step => {
                setTimeout(() => {
                    document.getElementById(step.id).classList.add('active');
                }, step.delay);

                setTimeout(() => {
                    document.getElementById(step.id).classList.remove('active');
                    document.getElementById(step.id).classList.add('completed');
                }, step.delay + 500);
            });

            // Submit form after 2.5 seconds
            setTimeout(() => {
                form.submit();
            }, 2500);
        });
    </script>
</body>
</html>
