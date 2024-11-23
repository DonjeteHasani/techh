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
// Fetch sales data
$salesData = $pdo->query("
    SELECT DATE(order_date) AS sale_date, SUM(total_amount) AS daily_sales
    FROM orders
    GROUP BY DATE(order_date)
    ORDER BY sale_date ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Calculate moving averages
$movingAverageDays = 5;
$salesForecast = [];
for ($i = $movingAverageDays; $i < count($salesData); $i++) {
    $sum = 0;
    for ($j = $i - $movingAverageDays; $j < $i; $j++) {
        $sum += $salesData[$j]['daily_sales'];
    }
    $salesForecast[] = round($sum / $movingAverageDays, 2);
}

// Fetch top trending products
$popularProducts = $pdo->query("
    SELECT products.name, SUM(order_items.quantity) AS total_quantity
    FROM order_items
    JOIN products ON order_items.product_id = products.id
    JOIN orders ON order_items.order_id = orders.id
    WHERE orders.order_date >= NOW() - INTERVAL 90 DAY
    GROUP BY products.id
    ORDER BY total_quantity DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
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
        /* Forecast Section */
.forecast {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.forecast h3 {
    margin-bottom: 1rem;
    color: var(--dark);
}

.forecast-list {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.forecast-item {
    background: var(--light);
    padding: 1rem;
    border-radius: 8px;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s;
}

.forecast-item:hover {
    transform: translateY(-3px);
}

.forecast-item h4 {
    font-size: 1.2rem;
    color: var(--primary);
    margin-bottom: 0.5rem;
}

/* Alerts Section */
.alerts {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-top: 2rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.alerts h3 {
    margin-bottom: 1rem;
    color: var(--dark);
}

.alert-item {
    background: var(--light);
    border-left: 5px solid var(--warning);
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.alert-item i {
    font-size: 1.5rem;
    color: var(--warning);
}

.alert-item .alert-text {
    flex-grow: 1;
    color: var(--dark);
}

.no-alerts {
    text-align: center;
    color: var(--gray);
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
        <!-- Alerts Section -->
     <div class="alerts" id="alerts">
    <h3>Alerts</h3>
    <p class="no-alerts">Loading alerts...</p>
</div>
<br>
<br>

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
        <div class="forecast">
    <h3>Sales Forecast for Next 5 Days</h3>
    <?php if (!empty($salesForecast)): ?>
        <div class="forecast-list">
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <div class="forecast-item">
                    <h4>Day <?php echo $i; ?></h4>
                    <p>$<?php echo $salesForecast[count($salesForecast) - 1]; ?></p>
                </div>
            <?php endfor; ?>
        </div>
    <?php else: ?>
        <p class="no-alerts">Not enough data to calculate a forecast.</p>
    <?php endif; ?>
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
<script>
        // Fetch alerts every 10 seconds
        setInterval(() => {
    fetch('fetch_alerts.php')
        .then(response => response.json())
        .then(data => {
            console.log(data); // Debugging: Check the response structure

            let alertHTML = '';

            // Low Stock Alerts
            if (data.lowStock.length > 0) {
                alertHTML += '<h3>Low Stock Alerts</h3>';
                data.lowStock.forEach(item => {
                    alertHTML += `
                        <div class="alert-item">
                            <i class="fas fa-box-open"></i>
                            <div class="alert-text">${item.name} - ${item.stock} units left</div>
                        </div>`;
                });
            }

            // High-Demand Alerts
            if (data.highDemand.length > 0) {
                alertHTML += '<h3>High-Demand Products</h3>';
                data.highDemand.forEach(item => {
                    alertHTML += `
                        <div class="alert-item">
                            <i class="fas fa-chart-line"></i>
                            <div class="alert-text">${item.name} - ${item.total_quantity} units sold this week</div>
                        </div>`;
                });
            }

            // No Alerts
            if (alertHTML === '') {
                alertHTML = '<p class="no-alerts">No alerts at the moment.</p>';
            }

            document.getElementById('alerts').innerHTML = alertHTML;
        })
        .catch(error => {
            console.error('Error fetching alerts:', error);
            document.getElementById('alerts').innerHTML = '<p class="no-alerts">Error loading alerts.</p>';
        });
}, 10000);


    </script>
</html>
