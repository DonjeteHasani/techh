<?php
session_start();
include 'config.php';

// Ensure the user is logged in
if (!isset($_SESSION['is_logged_in'])) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to add to wishlist.']);
    exit;
}

// Get the user ID and product ID
$userId = $_SESSION['user_id'];
$productId = $_POST['product_id'];

// Check if the product is already in the wishlist
$checkStmt = $pdo->prepare("SELECT * FROM wishlist WHERE user_id = ? AND product_id = ?");
$checkStmt->execute([$userId, $productId]);
$exists = $checkStmt->fetch();

if ($exists) {
    // If product is already in wishlist, respond with a message
    echo json_encode(['status' => 'error', 'message' => 'This product is already in your wishlist.']);
    exit;
}

// Add the product to the wishlist
$insertStmt = $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
$insertStmt->execute([$userId, $productId]);

echo json_encode(['status' => 'success', 'message' => 'Product added to wishlist successfully.']);
exit;
