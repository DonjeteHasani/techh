<?php
include 'config.php';

// Low Stock Alerts
$lowStock = $pdo->query("
    SELECT name, stock 
    FROM products 
    WHERE stock < 10
")->fetchAll(PDO::FETCH_ASSOC);

// High-Demand Products
$highDemand = $pdo->query("
    SELECT products.name, SUM(order_items.quantity) AS total_quantity
    FROM order_items
    JOIN products ON order_items.product_id = products.id
    JOIN orders ON order_items.order_id = orders.id
    WHERE orders.order_date >= NOW() - INTERVAL 7 DAY
    GROUP BY products.id
    HAVING total_quantity > 50
")->fetchAll(PDO::FETCH_ASSOC);

// Output as JSON
header('Content-Type: application/json');
echo json_encode([
    'lowStock' => $lowStock,
    'highDemand' => $highDemand
]);
?>
