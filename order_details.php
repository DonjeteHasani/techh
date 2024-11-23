<?php
session_start();
include 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['is_logged_in'])) {
    header("Location: login.php");
    exit;
}

// Get the order ID from the URL
$orderId = (int)$_GET['order_id'];

// Fetch order details including coupon information
$stmt = $pdo->prepare("
    SELECT o.*, c.code as coupon_code, c.discount_percentage 
    FROM orders o 
    LEFT JOIN coupons c ON o.coupon_id = c.id 
    WHERE o.id = ?
");
$stmt->execute([$orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

// Ensure the user can only view their own orders
if ($order['user_id'] !== $_SESSION['user_id']) {
    echo "Unauthorized access.";
    exit;
}

// Fetch items in the order
$itemsStmt = $pdo->prepare("
    SELECT 
        p.name, 
        oi.price_at_time as original_price, 
        oi.quantity 
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$itemsStmt->execute([$orderId]);
$items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Order Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center mb-4">Order Details - Order #<?php echo $order['id']; ?></h2>
    
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Order Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Order ID:</strong> #<?php echo $order['id']; ?></p>
                    <p><strong>Order Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?></p>
                    <p><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Shipping Address:</strong><br><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                    <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Order Items</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="text-end">Price</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td class="text-end">$<?php echo number_format($item['original_price'], 2); ?></td>
                                <td class="text-center"><?php echo $item['quantity']; ?></td>
                                <td class="text-end">$<?php echo number_format($item['original_price'] * $item['quantity'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="row justify-content-end">
                <div class="col-md-5">
                    <table class="table table-clear">
                        <tbody>
                            <tr>
                                <td class="text-end"><strong>Subtotal</strong></td>
                                <td class="text-end">$<?php echo number_format($order['original_amount'], 2); ?></td>
                            </tr>
                            
                            <?php if ($order['coupon_id']): ?>
                            <tr>
                                <td class="text-end">
                                    <strong>Discount</strong><br>
                                    <small class="text-muted">
                                        Coupon: <?php echo htmlspecialchars($order['coupon_code']); ?> 
                                        (<?php echo $order['discount_percentage']; ?>% off)
                                    </small>
                                </td>
                                <td class="text-end text-danger">
                                    -$<?php echo number_format($order['original_amount'] - $order['total_amount'], 2); ?>
                                </td>
                            </tr>
                            <?php endif; ?>

                            <tr>
                                <td class="text-end"><strong>Final Total</strong></td>
                                <td class="text-end"><strong>$<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>