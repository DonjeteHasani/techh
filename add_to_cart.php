<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true || $_SESSION['role'] !== 'user') {
    echo json_encode(["status" => "error", "message" => "You must be logged in to add items to the cart."]);
    exit;
}

// Ensure product_id is provided via POST and validate it
if (!isset($_POST['product_id']) || !is_numeric($_POST['product_id'])) {
    echo json_encode(["status" => "error", "message" => "Invalid product ID."]);
    exit;
}

$productId = (int)$_POST['product_id'];
$quantity = 1; // Default quantity to add

// Initialize cart session if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Update quantity if product already in cart
if (isset($_SESSION['cart'][$productId])) {
    $_SESSION['cart'][$productId] += $quantity;
} else {
    // Validate that the product exists in the database
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode(["status" => "error", "message" => "Product not found."]);
        exit;
    }

    // Add the product to the cart with default quantity
    $_SESSION['cart'][$productId] = $quantity;
}

echo json_encode(["status" => "success", "message" => "Product added to cart successfully!"]);
?>


