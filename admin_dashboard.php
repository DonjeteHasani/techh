<?php
session_start();
include 'config.php';

// Security check
if (!isset($_SESSION['is_logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}


// Fetch total users
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

// Fetch total sales
$totalSales = $pdo->query("SELECT SUM(total_amount) FROM orders")->fetchColumn();

// Fetch total orders
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();

// Fetch most popular products (top 5)
$stmt = $pdo->query("SELECT products.name, SUM(order_items.quantity) AS total_quantity 
                     FROM order_items 
                     JOIN products ON order_items.product_id = products.id 
                     GROUP BY products.id 
                     ORDER BY total_quantity DESC 
                     LIMIT 5");
$popularProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #4361ee;
            --success: #2ec4b6;
            --info: #3a86ff;
            --warning: #ff9f1c;
            --danger: #ef476f;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f0f2f5;
            color: var(--dark);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            background: white;
            padding: 1rem 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .header h2 {
            color: var(--dark);
            font-size: 1.5rem;
            font-weight: 600;
        }

        .logout-btn {
            background: var(--danger);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            transition: opacity 0.2s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logout-btn:hover {
            opacity: 0.9;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
            color: var(--gray);
        }

        .stat-icon {
            font-size: 1.5rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .popular-products {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .popular-products h3 {
            margin-bottom: 1rem;
            color: var(--dark);
        }

        .product-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .product-item {
            background: var(--light);
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
        }

        .product-name {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .product-quantity {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .quick-links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }

        .quick-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            background: white;
            color: var(--dark);
            padding: 1rem;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.2s;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .quick-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .stat-value {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
   

    <div class="container">
        <header class="header">
            <h2>Admin Dashboard</h2>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </header>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <i class="fas fa-users stat-icon" style="color: var(--primary)"></i>
                    <span>Total Users</span>
                </div>
                <div class="stat-value"><?php echo number_format($totalUsers); ?></div>
                <div class="stat-label">Registered accounts</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <i class="fas fa-dollar-sign stat-icon" style="color: var(--success)"></i>
                    <span>Total Sales</span>
                </div>
                <div class="stat-value">$<?php echo number_format($totalSales, 2); ?></div>
                <div class="stat-label">Revenue generated</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <i class="fas fa-shopping-cart stat-icon" style="color: var(--info)"></i>
                    <span>Total Orders</span>
                </div>
                <div class="stat-value"><?php echo number_format($totalOrders); ?></div>
                <div class="stat-label">Processed orders</div>
            </div>
        </div>

        <div class="popular-products">
            <h3>Popular Products</h3>
            <div class="product-list">
                <?php foreach ($popularProducts as $product): ?>
                <div class="product-item">
                    <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                    <div class="product-quantity"><?php echo number_format($product['total_quantity']); ?> units sold</div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="quick-links">
            <a href="manage_products.php" class="quick-link">
                <i class="fas fa-box"></i>
                Manage Products
            </a>
            <a href="view_orders.php" class="quick-link">
                <i class="fas fa-list"></i>
                View Orders
            </a>
            <a href="manage_users.php" class="quick-link">
                <i class="fas fa-user-cog"></i>
                Manage Users
            </a>
            <a href="manage_coupons.php" class="quick-link">
                <i class="fas fa-ticket-alt"></i>
                Manage Coupons
            </a>
        </div>
    </div>
</body>
</html>