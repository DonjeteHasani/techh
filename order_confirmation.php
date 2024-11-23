<?php
session_start();
include 'config.php';

// Check if order_id is provided in the URL
if (!isset($_GET['order_id'])) {
    echo "Invalid order.";
    exit;
}

// First, check if the coupon_id column exists
$columnExists = false;
try {
    $checkStmt = $pdo->prepare("SHOW COLUMNS FROM orders LIKE 'coupon_id'");
    $checkStmt->execute();
    $columnExists = $checkStmt->rowCount() > 0;
} catch (PDOException $e) {
    // Column doesn't exist
    $columnExists = false;
}

// Prepare the SQL query based on whether the coupon_id column exists
if ($columnExists) {
    $sql = "SELECT orders.*, users.username, users.email, 
                   coupons.code as coupon_code, 
                   coupons.discount_percentage
            FROM orders 
            JOIN users ON orders.user_id = users.id 
            LEFT JOIN coupons ON orders.coupon_id = coupons.id 
            WHERE orders.id = ?";
} else {
    $sql = "SELECT orders.*, users.username, users.email 
            FROM orders 
            JOIN users ON orders.user_id = users.id 
            WHERE orders.id = ?";
}

// Fetch order details
$orderId = (int)$_GET['order_id'];
$stmt = $pdo->prepare($sql);
$stmt->execute([$orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo "Order not found.";
    exit;
}

// Fetch items in the order
$stmtItems = $pdo->prepare("SELECT products.name, products.price, order_items.quantity 
                           FROM order_items 
                           JOIN products ON order_items.product_id = products.id 
                           WHERE order_items.order_id = ?");
$stmtItems->execute([$orderId]);
$items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

// Calculate subtotal
$subtotal = 0;
foreach ($items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Order Confirmation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --accent-color: #dbeafe;
            --success-color: #10b981;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        body {
            background-color: #f8fafc;
            font-family: 'Inter', sans-serif;
        }

        .navbar {
            background-color: white !important;
            box-shadow: var(--card-shadow);
            padding: 1rem 0;
        }

        .navbar-brand {
            font-weight: 700;
            color: var(--primary-color) !important;
            font-size: 1.5rem;
        }

        .order-confirmation-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 2rem 2rem;
        }

        .order-success-icon {
            background-color: var(--success-color);
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }

        .card {
            background: white;
            border-radius: 1rem;
            border: none;
            box-shadow: var(--card-shadow);
            margin-bottom: 2rem;
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            font-weight: 600;
            color: #4b5563;
        }

        .discount-row {
            background-color: #f0fdf4;
        }

        .final-total-row {
            background-color: #f8fafc;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border: none;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: #64748b;
            border: none;
        }

        .btn-secondary:hover {
            background-color: #475569;
            transform: translateY(-2px);
        }

        .order-details {
            background-color: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: var(--card-shadow);
        }

        .order-details li {
            padding: 0.75rem 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .order-details li:last-child {
            border-bottom: none;
        }

        @media (max-width: 768px) {
            .order-confirmation-header {
                padding: 2rem 0;
                border-radius: 0 0 1rem 1rem;
            }

            .card {
                margin: 1rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-store me-2"></i>TechAI
            </a>
        </div>
    </nav>

    <div class="order-confirmation-header">
        <div class="container text-center">
            <div class="order-success-icon">
                <i class="fas fa-check fa-2x text-white"></i>
            </div>
            <h1 class="display-5 mb-2">Thank You for Your Order!</h1>
            <p class="lead opacity-75">Your order has been placed successfully.</p>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card order-details mb-4">
                    <h4 class="mb-4">Order Summary <span class="text-muted">#<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></span></h4>
                    <ul class="list-unstyled">
                        <li><strong>Name:</strong> <?php echo htmlspecialchars($order['username']); ?></li>
                        <li><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></li>
                        <li><strong>Shipping Address:</strong> <?php echo htmlspecialchars($order['shipping_address']); ?></li>
                        <li><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></li>
                        <li><strong>Order Date:</strong> <?php echo date('F j, Y', strtotime($order['order_date'])); ?></li>
                    </ul>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Items Purchased</h4>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                                            <td><?php echo $item['quantity']; ?></td>
                                            <td class="text-end">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Subtotal</strong></td>
                                        <td class="text-end">$<?php echo number_format($subtotal, 2); ?></td>
                                    </tr>
                                    
                                    <?php if (isset($order['coupon_code']) && isset($order['discount_percentage'])): ?>
                                        <tr class="discount-row">
                                            <td colspan="3" class="text-end">
                                                <strong>Discount (<?php echo $order['discount_percentage']; ?>% off)</strong>
                                                <span class="text-muted">(Coupon: <?php echo htmlspecialchars($order['coupon_code']); ?>)</span>
                                            </td>
                                            <td class="text-end text-success">-$<?php echo number_format(($subtotal * $order['discount_percentage'] / 100), 2); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                    
                                    <tr class="final-total-row">
                                        <td colspan="3" class="text-end"><strong>Final Total</strong></td>
                                        <td class="text-end"><strong>$<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4 mb-5">
                    <a href="generate_pdf.php?order_id=<?php echo $orderId; ?>" class="btn btn-secondary me-2">
                        <i class="fas fa-download me-2"></i>Download PDF
                    </a>
                    <a href="products.php" class="btn btn-primary">
                        <i class="fas fa-shopping-cart me-2"></i>Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
