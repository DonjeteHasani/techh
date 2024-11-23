<?php
function getSimilarProducts($productId, $limit = 5) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT * FROM products 
        WHERE category_id = (SELECT category_id FROM products WHERE id = ?)
        AND id != ? 
        LIMIT ?
    ");
    $stmt->execute([$productId, $productId, $limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getRecommendationsBasedOnHistory($userId, $limit = 5) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT DISTINCT p.*
        FROM products p
        JOIN order_items oi ON p.id = oi.product_id
        WHERE oi.order_id IN (
            SELECT o.id 
            FROM orders o 
            WHERE o.user_id = ?
        )
        LIMIT ?
    ");
    $stmt->execute([$userId, $limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
