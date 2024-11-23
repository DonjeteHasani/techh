<?php
function getSimilarProducts($productId, $pdo, $limit = 5) {
    $stmt = $pdo->prepare("
        SELECT * FROM products 
        WHERE category_id = (SELECT category_id FROM products WHERE id = :product_id)
        AND id != :product_id 
        LIMIT :limit
    ");

    // Bind parameters explicitly
    $stmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getRecommendationsBasedOnHistory($userId, $pdo, $limit = 5) {
    $stmt = $pdo->prepare("
        SELECT DISTINCT p.*
        FROM products p
        JOIN order_items oi ON p.id = oi.product_id
        WHERE oi.order_id IN (
            SELECT o.id 
            FROM orders o 
            WHERE o.user_id = :user_id
        )
        LIMIT :limit
    ");

    // Bind parameters explicitly
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

