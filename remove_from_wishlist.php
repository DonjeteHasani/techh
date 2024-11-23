<?php
session_start();
include 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['is_logged_in'])) {
    header("Location: login.php");
    exit;
}

// Get the user ID and product ID
$userId = $_SESSION['user_id'];
$productId = $_POST['product_id'];

// Remove the product from the wishlist
$deleteStmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
$deleteStmt->execute([$userId, $productId]);

header("Location: profile.php?message=removed_from_wishlist");
exit;
