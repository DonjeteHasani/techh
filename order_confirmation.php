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
    <style>
        .discount-row {
            background-color: #e8f4f8;
        }
        .final-total-row {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --accent-color: #dbeafe;
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

        .nav-link {
            font-weight: 500;
            color: #374151 !important;
            transition: color 0.2s;
            padding: 0.5rem 1rem !important;
            margin: 0 0.25rem;
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-store me-2"></i>TechAI
            </a>
    </nav>

<div class="container mt-5">
    <h2 class="text-center">Thank You for Your Order! TechAI</h2>
    <p class="text-center">Your order has been placed successfully. Here are the details:</p>
    
    <h4>Order Summary (Order ID: <?php echo $order['id']; ?>)</h4>
    <ul class="list-unstyled">
        <li><strong>Name:</strong> <?php echo htmlspecialchars($order['username']); ?></li>
        <li><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></li>
        <li><strong>Shipping Address:</strong> <?php echo htmlspecialchars($order['shipping_address']); ?></li>
        <li><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></li>
        <li><strong>Order Date:</strong> <?php echo htmlspecialchars($order['order_date']); ?></li>
    </ul>
    
    <h4>Items Purchased</h4>
    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
            
            <tr>
                <td colspan="3"><strong>Subtotal</strong></td>
                <td>$<?php echo number_format($subtotal, 2); ?></td>
            </tr>
            
            <?php if (isset($order['coupon_code']) && isset($order['discount_percentage'])): ?>
                <tr class="discount-row">
                    <td colspan="3">
                        <strong>Discount (<?php echo $order['discount_percentage']; ?>% off)</strong>
                        <span class="text-muted">(Coupon: <?php echo htmlspecialchars($order['coupon_code']); ?>)</span>
                    </td>
                    <td>-$<?php echo number_format(($subtotal * $order['discount_percentage'] / 100), 2); ?></td>
                </tr>
            <?php endif; ?>
            
            <tr class="final-total-row">
                <td colspan="3"><strong>Final Total</strong></td>
                <td><strong>$<?php echo number_format($order['total_amount'], 2); ?></strong></td>
            </tr>
        </tbody>
    </table>
    <div class="text-center mt-4">
    <a href="generate_pdf.php?order_id=<?php echo $orderId; ?>" class="btn btn-secondary">
        <i class="fas fa-download"></i> Download as PDF
    </a>
    <a href="products.php" class="btn btn-primary">Continue Shopping</a>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>