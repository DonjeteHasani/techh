<?php 
session_start(); 
include 'config.php';

// Ensure user is logged in 
if (!isset($_SESSION['is_logged_in'])) {
    header("Location: login.php");
    exit;
}

// Get order details from the form
$userId = $_SESSION['user_id'];
$shippingAddress = $_POST['address'];
$paymentMethod = $_POST['payment'];
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

// Redirect if cart is empty
if (empty($cart)) {
    header("Location: cart.php");
    exit;
}

// Fetch product details from database
$productIds = array_keys($cart);
$placeholders = implode(',', array_fill(0, count($productIds), '?'));
$stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
$stmt->execute($productIds);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate subtotal
$subtotal = 0;
foreach ($products as $product) {
    $productId = $product['id'];
    $quantity = $cart[$productId];
    $subtotal += $product['price'] * $quantity;
}

// Initialize variables for discount and final amount
$finalAmount = $subtotal;
$couponId = null;
$discountAmount = 0;

// Apply coupon discount if one is set in session
if (isset($_SESSION['applied_coupon'])) {
    $coupon = $_SESSION['applied_coupon'];
    $discountPercentage = $coupon['discount_percentage'];
    $discountAmount = $subtotal * ($discountPercentage / 100);
    $finalAmount = $subtotal - $discountAmount;
    $couponId = $coupon['id'];
}

// Validate and process payment-specific details
if ($paymentMethod === 'Credit Card') {
    $ccName = $_POST['cc_name'] ?? '';
    $ccNumber = $_POST['cc_number'] ?? '';
    $ccExpiry = $_POST['cc_expiry'] ?? '';
    $ccCVC = $_POST['cc_cvc'] ?? '';
    if (!$ccName || !$ccNumber || !$ccExpiry || !$ccCVC) {
        $_SESSION['error'] = "Incomplete credit card details.";
        header("Location: checkout.php");
        exit;
    }
    $paymentDetails = "Name: $ccName, Card: $ccNumber, Expiry: $ccExpiry, CVC: $ccCVC";
} elseif ($paymentMethod === 'PayPal') {
    $paypalEmail = $_POST['paypal_email'] ?? '';
    if (!$paypalEmail) {
        $_SESSION['error'] = "PayPal email is required.";
        header("Location: checkout.php");
        exit;
    }
    $paymentDetails = "PayPal Email: $paypalEmail";
} elseif ($paymentMethod === 'Bank Transfer') {
    $bankName = $_POST['bank_name'] ?? '';
    $accountNumber = $_POST['account_number'] ?? '';
    $accountHolder = $_POST['account_holder'] ?? '';
    if (!$bankName || !$accountNumber || !$accountHolder) {
        $_SESSION['error'] = "Incomplete bank transfer details.";
        header("Location: checkout.php");
        exit;
    }
    $paymentDetails = "Bank: $bankName, Account: $accountNumber, Holder: $accountHolder";
} else {
    $paymentDetails = "N/A";
}

try {
    // Start transaction
    $pdo->beginTransaction();

    // Insert order details
    $orderStmt = $pdo->prepare("
        INSERT INTO orders (
            user_id, 
            total_amount, 
            original_amount,
            coupon_id,
            shipping_address, 
            payment_method, 
            order_date
        ) 
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $orderStmt->execute([
        $userId, 
        $finalAmount,
        $subtotal,
        $couponId,
        $shippingAddress, 
        $paymentMethod
    ]);
    
    $orderId = $pdo->lastInsertId();

    // Insert order items
    $orderItemStmt = $pdo->prepare("
        INSERT INTO order_items (
            order_id, 
            product_id, 
            quantity,
            price_at_time
        ) 
        VALUES (?, ?, ?, ?)
    ");

    foreach ($products as $product) {
        $productId = $product['id'];
        $quantity = $cart[$productId];
        $orderItemStmt->execute([
            $orderId, 
            $productId, 
            $quantity,
            $product['price']
        ]);
    }

    // Reduce product stock
    $updateStockStmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
    foreach ($products as $product) {
        $productId = $product['id'];
        $quantity = $cart[$productId];
        $updateStockStmt->execute([$quantity, $productId]);
    }

    // Commit transaction
    $pdo->commit();

    // Clear cart and coupon sessions
    unset($_SESSION['cart']);
    unset($_SESSION['applied_coupon']);

    // Redirect to order confirmation
    header("Location: order_confirmation.php?order_id=" . $orderId);
    exit;

} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    $_SESSION['error'] = "An error occurred while processing your order. Please try again.";
    header("Location: checkout.php");
    exit;
}
