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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Inter', sans-serif;
        }
        .order-header {
            background: linear-gradient(45deg, #1a237e, #283593);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 1rem 1rem;
        }
        .card {
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            border-radius: 1rem;
            margin-bottom: 2rem;
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 2px solid #e9ecef;
            padding: 1.5rem;
            border-radius: 1rem 1rem 0 0 !important;
        }
        .table {
            margin-bottom: 0;
        }
        .table th {
            font-weight: 600;
            color: #495057;
            border-top: none;
        }
        .table td {
            vertical-align: middle;
        }
        .back-button {
            background-color: #1a237e;
            color: white;
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-block;
            margin-bottom: 1rem;
        }
        .back-button:hover {
            background-color: #283593;
            color: white;
            transform: translateX(-5px);
        }
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-weight: 500;
            background-color: #e3f2fd;
            color: #1565c0;
        }
        .order-total {
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1rem;
        }
        @media (max-width: 768px) {
            .order-header {
                padding: 2rem 0;
            }
            .card-body {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>

<div class="order-header">
    <div class="container">
        <a href="my_orders.php" class="back-button">
            <i class="fas fa-arrow-left me-2"></i>Back to Orders
        </a>
        <h1 class="mb-2">Order #<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></h1>
        <p class="opacity-75 mb-0">Placed on <?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?></p>
    </div>
</div>

<div class="container">
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Order Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-4">
                        <p class="text-muted mb-2">Order Status</p>
                        <span class="status-badge">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo htmlspecialchars($order['status']); ?>
                        </span>
                    </div>
                    <div class="mb-4">
                        <p class="text-muted mb-2">Payment Method</p>
                        <p class="mb-0">
                            <i class="fas fa-credit-card me-2"></i>
                            <?php echo htmlspecialchars($order['payment_method']); ?>
                        </p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-4">
                        <p class="text-muted mb-2">Shipping Address</p>
                        <p class="mb-0">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Order Items</h5>
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
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-box me-2 text-muted"></i>
                                        <?php echo htmlspecialchars($item['name']); ?>
                                    </div>
                                </td>
                                <td class="text-end">$<?php echo number_format($item['original_price'], 2); ?></td>
                                <td class="text-center"><?php echo $item['quantity']; ?></td>
                                <td class="text-end">$<?php echo number_format($item['original_price'] * $item['quantity'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="row justify-content-end mt-4">
                <div class="col-md-5">
                    <div class="order-total">
                        <table class="table table-clear mb-0">
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
                                            <i class="fas fa-tag me-1"></i>
                                            <?php echo htmlspecialchars($order['coupon_code']); ?> 
                                            (<?php echo $order['discount_percentage']; ?>% off)
                                        </small>
                                    </td>
                                    <td class="text-end text-danger">
                                        -$<?php echo number_format($order['original_amount'] - $order['total_amount'], 2); ?>
                                    </td>
                                </tr>
                                <?php endif; ?>

                                <tr class="border-top">
                                    <td class="text-end"><strong>Final Total</strong></td>
                                    <td class="text-end"><strong class="text-primary">$<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
