<?php
session_start();
include 'config.php';

// Check if the user is an admin
if (!isset($_SESSION['is_logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = $_POST['order_id'];
    $newStatus = $_POST['status'];

    $updateStmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $updateStmt->execute([$newStatus, $orderId]);
    header("Location: view_orders.php");
    exit;
}

// Pagination setup
$ordersPerPage = 10;  // Number of orders per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);  // Ensure the page number is at least 1
$offset = ($page - 1) * $ordersPerPage;

// Fetch the orders for the current page
$stmt = $pdo->prepare("SELECT * FROM orders ORDER BY order_date DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $ordersPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch the total number of orders to calculate total pages
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalPages = ceil($totalOrders / $ordersPerPage);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --success-color: #4bb543;
            --warning-color: #ffa900;
            --danger-color: #dc3545;
            --info-color: #0dcaf0;
        }

        body {
            background-color: #f8f9fa;
            color: #333;
            font-family: 'Inter', sans-serif;
            
        }

        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 15px 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .stats-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .table-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            padding: 1rem;
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 500;
            font-size: 0.875rem;
        }

        .status-Processing {
            background-color: rgba(255, 169, 0, 0.1);
            color: var(--warning-color);
        }

        .status-Shipped {
            background-color: rgba(13, 202, 240, 0.1);
            color: var(--info-color);
        }

        .status-Delivered {
            background-color: rgba(75, 181, 67, 0.1);
            color: var(--success-color);
        }

        .status-Canceled {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
        }

        .status-select {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 0.5rem;
            width: 140px;
            background-color: white;
        }

        .pagination {
            margin-top: 2rem;
        }

        .pagination .page-link {
            border: none;
            color: var(--primary-color);
            padding: 0.5rem 1rem;
            border-radius: 6px;
            margin: 0 0.25rem;
        }

        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            color: white;
        }

        .order-details {
            font-size: 0.875rem;
        }

        .search-box {
            border-radius: 50px;
            padding: 0.75rem 1.5rem;
            border: 1px solid #dee2e6;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <div class="container">
            <h1 class="mb-0">
                <i class="fas fa-shopping-cart me-2"></i>Orders Management
            </h1>
            <p class="mt-2 mb-0">Monitor and manage your customer orders</p>
        </div>
    </div>

    <div class="container mb-5">
        <!-- Order Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card card text-center p-3">
                    <div class="text-primary mb-2">
                        <i class="fas fa-clipboard-list fa-2x"></i>
                    </div>
                    <h3 class="mb-1"><?php echo $totalOrders; ?></h3>
                    <p class="text-muted mb-0">Total Orders</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card card text-center p-3">
                    <div class="text-warning mb-2">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                    <h3 class="mb-1">
                        <?php
                        $processingOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'Processing'")->fetchColumn();
                        echo $processingOrders;
                        ?>
                    </h3>
                    <p class="text-muted mb-0">Processing</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card card text-center p-3">
                    <div class="text-info mb-2">
                        <i class="fas fa-shipping-fast fa-2x"></i>
                    </div>
                    <h3 class="mb-1">
                        <?php
                        $shippedOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'Shipped'")->fetchColumn();
                        echo $shippedOrders;
                        ?>
                    </h3>
                    <p class="text-muted mb-0">Shipped</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card card text-center p-3">
                    <div class="text-success mb-2">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                    <h3 class="mb-1">
                        <?php
                        $deliveredOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'Delivered'")->fetchColumn();
                        echo $deliveredOrders;
                        ?>
                    </h3>
                    <p class="text-muted mb-0">Delivered</p>
                </div>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="table-card card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>
                                        <strong>#<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></strong>
                                    </td>
                                    <td>
                                        <div class="order-details">
                                            ID: <?php echo $order['user_id']; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>$<?php echo number_format($order['total_amount'], 2); ?></strong>
                                    </td>
                                    <td>
                                        <div class="order-details">
                                            <?php 
                                            $date = new DateTime($order['order_date']);
                                            echo $date->format('M d, Y');
                                            ?>
                                            <br>
                                            <small class="text-muted">
                                                <?php echo $date->format('h:i A'); ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $order['status']; ?>">
                                            <?php echo $order['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form action="view_orders.php" method="POST" class="status-update-form">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <select name="status" class="form-select status-select" onchange="this.form.submit()">
                                                <option value="Processing" <?php echo $order['status'] === 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                                <option value="Shipped" <?php echo $order['status'] === 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                <option value="Delivered" <?php echo $order['status'] === 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                <option value="Canceled" <?php echo $order['status'] === 'Canceled' ? 'selected' : ''; ?>>Canceled</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="admin_dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
