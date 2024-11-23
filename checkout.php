<?php
session_start();
include 'config.php';

// Ensure the user is logged in before checkout
if (!isset($_SESSION['is_logged_in'])) {
    header("Location: login.php");
    exit;
}

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

// Redirect to cart if it's empty
if (empty($cart)) {
    header("Location: cart.php");
    exit;
}

// Initialize variables
$couponDiscount = 0;
$couponMessage = '';
$couponCode = '';

// Handle coupon application
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_coupon'])) {
    $couponCode = strtoupper(trim($_POST['coupon_code']));
    
    // Check if coupon exists and is valid
    $stmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ?");
    $stmt->execute([$couponCode]);
    $coupon = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($coupon) {
        $expiryDate = strtotime($coupon['expiration_date']);
        if ($expiryDate > time()) {
            $_SESSION['applied_coupon'] = $coupon;
            $couponDiscount = $coupon['discount_percentage'];
            $couponMessage = '<div class="alert alert-success">Coupon applied successfully! ' . $couponDiscount . '% discount</div>';
        } else {
            $couponMessage = '<div class="alert alert-danger">This coupon has expired!</div>';
            unset($_SESSION['applied_coupon']);
        }
    } else {
        $couponMessage = '<div class="alert alert-danger">Invalid coupon code!</div>';
        unset($_SESSION['applied_coupon']);
    }
}

// Fetch product details from the database
$productIds = array_keys($cart);
$placeholders = implode(',', array_fill(0, count($productIds), '?'));
$stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
$stmt->execute($productIds);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals
$grandTotal = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Checkout</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .payment-form-section {
            display: none;
        }
        .coupon-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .discount-row {
            background-color: #e8f4f8;
        }
        .final-total-row {
            background-color: #f8f9fa;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2>Checkout</h2>
    
    <!-- Coupon Form -->
    <div class="coupon-form">
        <h5>Have a coupon?</h5>
        <form method="POST" class="row g-3">
            <div class="col-auto">
                <input type="text" name="coupon_code" class="form-control" 
                       placeholder="Enter coupon code" 
                       value="<?php echo htmlspecialchars($couponCode); ?>">
            </div>
            <div class="col-auto">
                <button type="submit" name="apply_coupon" class="btn btn-primary">Apply Coupon</button>
            </div>
        </form>
        <?php if ($couponMessage): ?>
            <?php echo $couponMessage; ?>
        <?php endif; ?>
    </div>

    <!-- Order Summary -->
    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product):
                $productId = $product['id'];
                $quantity = $cart[$productId];
                $totalPrice = $product['price'] * $quantity;
                $grandTotal += $totalPrice;
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                    <td>$<?php echo number_format($product['price'], 2); ?></td>
                    <td><?php echo $quantity; ?></td>
                    <td>$<?php echo number_format($totalPrice, 2); ?></td>
                </tr>
            <?php endforeach; ?>
            
            <tr>
                <td colspan="3"><strong>Subtotal</strong></td>
                <td>$<?php echo number_format($grandTotal, 2); ?></td>
            </tr>
            
            <?php if (isset($_SESSION['applied_coupon'])): ?>
                <tr class="discount-row">
                    <td colspan="3">
                        <strong>Discount (<?php echo $_SESSION['applied_coupon']['discount_percentage']; ?>% off)</strong>
                    </td>
                    <td>-$<?php echo number_format(($grandTotal * $_SESSION['applied_coupon']['discount_percentage'] / 100), 2); ?></td>
                </tr>
                <tr class="final-total-row">
                    <td colspan="3"><strong>Final Total</strong></td>
                    <td>
                        <strong>
                            $<?php echo number_format($grandTotal - ($grandTotal * $_SESSION['applied_coupon']['discount_percentage'] / 100), 2); ?>
                        </strong>
                    </td>
                </tr>
            <?php else: ?>
                <tr class="final-total-row">
                    <td colspan="3"><strong>Final Total</strong></td>
                    <td><strong>$<?php echo number_format($grandTotal, 2); ?></strong></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Checkout Form -->
    <form action="process_order.php" method="POST">
        <?php if (isset($_SESSION['applied_coupon'])): ?>
            <input type="hidden" name="coupon_id" value="<?php echo $_SESSION['applied_coupon']['id']; ?>">
        <?php endif; ?>
        
        <div class="mb-3">
            <label for="address" class="form-label">Shipping Address</label>
            <textarea name="address" id="address" class="form-control" required></textarea>
        </div>
        
        <div class="mb-3">
            <label for="payment" class="form-label">Payment Method</label>
            <select name="payment" id="payment" class="form-control" required>
                <option value="" disabled selected>Select a Payment Method</option>
                <option value="Credit Card">Credit Card</option>
                <option value="PayPal">PayPal</option>
                <option value="Cash">Cash</option>
                <option value="Bank Transfer">Bank Transfer</option>
            </select>
        </div>
        
        <!-- Credit Card Form -->
        <div id="creditCardForm" class="payment-form-section">
            <h5>Credit Card Details</h5>
            <div class="mb-3">
                <label for="ccName" class="form-label">Name on Card</label>
                <input type="text" name="cc_name" id="ccName" class="form-control">
            </div>
            <div class="mb-3">
                <label for="ccNumber" class="form-label">Card Number</label>
                <input type="text" name="cc_number" id="ccNumber" class="form-control">
            </div>
            <div class="mb-3">
                <label for="ccExpiry" class="form-label">Expiry Date</label>
                <input type="text" name="cc_expiry" id="ccExpiry" class="form-control" placeholder="MM/YY">
            </div>
            <div class="mb-3">
                <label for="ccCVC" class="form-label">CVC</label>
                <input type="text" name="cc_cvc" id="ccCVC" class="form-control">
            </div>
        </div>
        
        <!-- PayPal Form -->
        <div id="paypalForm" class="payment-form-section">
            <h5>PayPal Details</h5>
            <div class="mb-3">
                <label for="paypalEmail" class="form-label">PayPal Email</label>
                <input type="email" name="paypal_email" id="paypalEmail" class="form-control">
            </div>
        </div>
        
        <!-- Bank Transfer Form -->
        <div id="bankTransferForm" class="payment-form-section">
            <h5>Bank Transfer Details</h5>
            <div class="mb-3">
                <label for="bankName" class="form-label">Bank Name</label>
                <input type="text" name="bank_name" id="bankName" class="form-control">
            </div>
            <div class="mb-3">
                <label for="accountNumber" class="form-label">Account Number</label>
                <input type="text" name="account_number" id="accountNumber" class="form-control">
            </div>
            <div class="mb-3">
                <label for="accountHolder" class="form-label">Account Holder Name</label>
                <input type="text" name="account_holder" id="accountHolder" class="form-control">
            </div>
        </div>

        <button type="submit" class="btn btn-success btn-lg mt-4">Place Order</button>
    </form>
</div>

<script>
    const paymentSelect = document.getElementById('payment');
    const creditCardForm = document.getElementById('creditCardForm');
    const paypalForm = document.getElementById('paypalForm');
    const bankTransferForm = document.getElementById('bankTransferForm');

    paymentSelect.addEventListener('change', function () {
        creditCardForm.style.display = 'none';
        paypalForm.style.display = 'none';
        bankTransferForm.style.display = 'none';

        if (this.value === 'Credit Card') {
            creditCardForm.style.display = 'block';
        } else if (this.value === 'PayPal') {
            paypalForm.style.display = 'block';
        } else if (this.value === 'Bank Transfer') {
            bankTransferForm.style.display = 'block';
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
