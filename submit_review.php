<?php
session_start();
include 'config.php';

// Ensure the user is logged in
if (!isset($_SESSION['is_logged_in'])) {
    header("Location: login.php");
    exit;
}

// Get the user ID, product ID, rating, and comment
$userId = $_SESSION['user_id'];
$productId = $_POST['product_id'];
$rating = $_POST['rating'];
$comment = $_POST['comment'];

// Insert the review into the database
$stmt = $pdo->prepare("INSERT INTO reviews (user_id, product_id, rating, comment) VALUES (?, ?, ?, ?)");
$stmt->execute([$userId, $productId, $rating, $comment]);

// Redirect back to the product detail page
header("Location: productdetail.php?id=" . $productId);
exit;
