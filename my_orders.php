<?php
session_start();
include 'config.php';

if (!isset($_SESSION['is_logged_in'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body{
            font-family: 'Inter', sans-serif;
        }
        .dashboard-header {
            background: linear-gradient(45deg, #1a237e, #283593);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
        }
        
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-radius: 0.75rem;
            margin-bottom: 2rem;
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid rgba(0, 0, 0, 0.125);
            padding: 1.5rem;
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            border-top: none;
            font-weight: 600;
            color: #495057;
        }

        .table-responsive {
            border-radius: 0.75rem;
        }

        .btn-view {
            background-color: #1a237e;
            color: white;
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            transition: all 0.3s;
        }

        .btn-view:hover {
            background-color: #283593;
            color: white;
            transform: translateY(-1px);
        }

        .empty-orders {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }

        @media (max-width: 768px) {
            .dashboard-header {
                padding: 2rem 0;
            }

            .card-body {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>

<div class="dashboard-header">
    <div class="container">
        <h1 class="mb-2"><i class="fas fa-shopping-bag me-3"></i>My Orders</h1>
        <p class="opacity-75 mb-0">Track and manage your order history</p>
    </div>
</div>

<div class="container">
    <?php if (count($orders) > 0): ?>
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-list me-2"></i>Order History</h4>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Total Amount</th>
                                <th>Shipping Address</th>
                                <th>Payment Method</th>
                                <th>Order Date</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>#<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></td>
                                    <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($order['shipping_address']); ?></td>
                                    <td><i class="fas fa-credit-card me-2"></i><?php echo htmlspecialchars($order['payment_method']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                    <td class="text-end">
                                        <a href="order_confirmation.php?order_id=<?php echo $order['id']; ?>" class="btn btn-view">
                                            <i class="fas fa-eye me-2"></i>View Details
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="empty-orders">
                <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                <h3>No Orders Yet</h3>
                <p class="mb-4">You haven't placed any orders yet. Start shopping to see your orders here!</p>
                <a href="products.php" class="btn btn-view">
                    <i class="fas fa-shopping-basket me-2"></i>Browse Products
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

