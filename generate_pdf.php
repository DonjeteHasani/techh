<?php
require_once 'tcpdf/tcpdf.php';
include 'config.php';

// Check if order_id is provided in the URL
if (!isset($_GET['order_id'])) {
    die("Invalid order.");
}

$orderId = (int)$_GET['order_id'];

// Fetch order and items (similar to your existing logic)
$stmt = $pdo->prepare("SELECT orders.*, users.username, users.email, 
                       coupons.code as coupon_code, coupons.discount_percentage 
                       FROM orders 
                       JOIN users ON orders.user_id = users.id 
                       LEFT JOIN coupons ON orders.coupon_id = coupons.id 
                       WHERE orders.id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

$stmtItems = $pdo->prepare("SELECT products.name, products.price, order_items.quantity 
                            FROM order_items 
                            JOIN products ON order_items.product_id = products.id 
                            WHERE order_items.order_id = ?");
$stmtItems->execute([$orderId]);
$items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

if (!$order) {
    die("Order not found.");
}

// Calculate subtotal
$subtotal = 0;
foreach ($items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

// Create new PDF document
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your Shop');
$pdf->SetTitle('Order Confirmation');
$pdf->SetMargins(15, 15, 15);
$pdf->AddPage();

// Content for PDF
$html = <<<EOD
<h2>Order Confirmation</h2>
<p>Thank you for your order! Below are the details:</p>

<h4>Order Summary (Order ID: {$order['id']})</h4>
<ul>
    <li><strong>Name:</strong> {$order['username']}</li>
    <li><strong>Email:</strong> {$order['email']}</li>
    <li><strong>Shipping Address:</strong> {$order['shipping_address']}</li>
    <li><strong>Payment Method:</strong> {$order['payment_method']}</li>
    <li><strong>Order Date:</strong> {$order['order_date']}</li>
</ul>

<h4>Items Purchased</h4>
<table border="1" cellpadding="5">
    <thead>
        <tr>
            <th>Product</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
EOD;

foreach ($items as $item) {
    $totalPrice = $item['price'] * $item['quantity'];
    $html .= <<<EOD
        <tr>
            <td>{$item['name']}</td>
            <td>\${$item['price']}</td>
            <td>{$item['quantity']}</td>
            <td>\${$totalPrice}</td>
        </tr>
EOD;
}

$html .= <<<EOD
    <tr>
        <td colspan="3"><strong>Subtotal</strong></td>
        <td>\${$subtotal}</td>
    </tr>
EOD;

if (isset($order['coupon_code']) && isset($order['discount_percentage'])) {
    $discountAmount = $subtotal * $order['discount_percentage'] / 100;
    $html .= <<<EOD
    <tr>
        <td colspan="3"><strong>Discount ({$order['discount_percentage']}%)</strong></td>
        <td>-\${$discountAmount}</td>
    </tr>
EOD;
}

$html .= <<<EOD
    <tr>
        <td colspan="3"><strong>Final Total</strong></td>
        <td><strong>\${$order['total_amount']}</strong></td>
    </tr>
    </tbody>
</table>
EOD;

// Add content to PDF
$pdf->writeHTML($html, true, false, true, false, '');

// Output PDF
$pdf->Output("Order_{$orderId}.pdf", 'D'); // 'D' forces download
?>
