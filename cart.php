<?php
session_start();
include 'config.php';

// Get the cart from the session
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

// Handle item removal via AJAX
if(isset($_POST['action']) && $_POST['action'] == 'remove_item') {
    $productId = $_POST['product_id'];
    if(isset($cart[$productId])) {
        unset($cart[$productId]);
        $_SESSION['cart'] = $cart;
        echo json_encode(['success' => true, 'message' => 'Item removed successfully']);
        exit;
    }
    echo json_encode(['success' => false, 'message' => 'Item not found']);
    exit;
}

// Check if the cart is empty
if (empty($cart)) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <title>Your Cart</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            :root {
                --primary-color: #4f46e5;
                --primary-hover: #4338ca;
                --background-start: #f8fafc;
                --background-end: #f1f5f9;
            }

            body {
                min-height: 100vh;
                background: linear-gradient(135deg, var(--background-start) 0%, var(--background-end) 100%);
                font-family: 'Inter', system-ui, -apple-system, sans-serif;
                display: flex;
                align-items: center;
                padding: 2rem 0;
                margin: 0;
            }

            .empty-cart-container {
                background: white;
                border-radius: 24px;
                box-shadow: 0 10px 25px rgba(0,0,0,0.1);
                max-width: 800px;
                margin: 0 auto;
                padding: 4rem 3rem;
                text-align: center;
                position: relative;
                overflow: hidden;
            }

            .empty-cart-container::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 8px;
                background: linear-gradient(90deg, var(--primary-color), #818cf8);
            }

            .empty-cart-icon {
                background: #f3f4f9;
                width: 160px;
                height: 160px;
                border-radius: 80px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 3rem;
                position: relative;
                animation: pulseIcon 2s infinite;
            }

            @keyframes pulseIcon {
                0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(79, 70, 229, 0.4); }
                50% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(79, 70, 229, 0); }
                100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(79, 70, 229, 0); }
            }

            .empty-cart-icon svg {
                width: 80px;
                height: 80px;
                stroke: var(--primary-color);
                stroke-width: 1.5;
            }

            .empty-cart-title {
                font-size: 3rem;
                font-weight: 800;
                color: #1f2937;
                margin-bottom: 1.5rem;
                line-height: 1.2;
                letter-spacing: -0.025em;
            }

            .empty-cart-message {
                font-size: 1.25rem;
                color: #6b7280;
                margin-bottom: 3rem;
                max-width: 500px;
                margin-left: auto;
                margin-right: auto;
                line-height: 1.6;
            }

            .shop-now-btn {
                background: var(--primary-color);
                color: white;
                border: none;
                padding: 1.25rem 3rem;
                border-radius: 9999px;
                font-weight: 600;
                font-size: 1.25rem;
                transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                display: inline-flex;
                align-items: center;
                gap: 1rem;
                position: relative;
                overflow: hidden;
                text-decoration: none;
            }

            .shop-now-btn:hover {
                background: var(--primary-hover);
                transform: translateY(-3px);
                box-shadow: 0 8px 20px rgba(79, 70, 229, 0.4);
                color: white;
            }

            .shop-now-btn:active {
                transform: translateY(-1px);
            }

            .decorative-dots {
                position: absolute;
                width: 200px;
                height: 200px;
                opacity: 0.15;
                z-index: 0;
            }

            .dots-left {
                left: -100px;
                top: -100px;
                background: radial-gradient(circle, var(--primary-color) 2px, transparent 2.5px);
                background-size: 20px 20px;
                transform: rotate(-15deg);
            }

            .dots-right {
                right: -100px;
                bottom: -100px;
                background: radial-gradient(circle, var(--primary-color) 2px, transparent 2.5px);
                background-size: 20px 20px;
                transform: rotate(15deg);
            }

            .product-suggestions {
                margin-top: 4rem;
                padding-top: 3rem;
                border-top: 2px solid #e5e7eb;
            }

            .suggestion-title {
                font-size: 1.5rem;
                color: #374151;
                margin-bottom: 2rem;
                font-weight: 600;
            }

            .categories {
                display: flex;
                justify-content: center;
                gap: 1.5rem;
                flex-wrap: wrap;
            }

            .category-tag {
                background: #f3f4f9;
                color: var(--primary-color);
                padding: 0.75rem 1.5rem;
                border-radius: 9999px;
                font-size: 1rem;
                font-weight: 500;
                transition: all 0.3s;
                text-decoration: none;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .category-tag:hover {
                background: var(--primary-color);
                color: white;
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
            }

            @media (max-width: 768px) {
                body {
                    padding: 1.5rem;
                }

                .empty-cart-container {
                    padding: 3rem 1.5rem;
                    border-radius: 20px;
                }

                .empty-cart-icon {
                    width: 120px;
                    height: 120px;
                }

                .empty-cart-icon svg {
                    width: 60px;
                    height: 60px;
                }

                .empty-cart-title {
                    font-size: 2rem;
                }

                .empty-cart-message {
                    font-size: 1.125rem;
                }

                .shop-now-btn {
                    width: 100%;
                    justify-content: center;
                    padding: 1rem 2rem;
                }

                .categories {
                    gap: 1rem;
                }

                .category-tag {
                    padding: 0.5rem 1rem;
                    font-size: 0.875rem;
                }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="empty-cart-container">
                <div class="decorative-dots dots-left"></div>
                <div class="decorative-dots dots-right"></div>

                <div class="empty-cart-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                </div>
                
                <h1 class="empty-cart-title">Your Cart is Empty</h1>
                <p class="empty-cart-message">
                    Ready to start shopping? Explore our collection and find something special just for you!
                </p>
                
                <a href="products.php" class="shop-now-btn">
                    Browse Products
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </a>

                <div class="product-suggestions">
                    <h2 class="suggestion-title">Explore Categories</h2>
                    <div class="categories">
                        <a href="products.php?category=new" class="category-tag">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                            </svg>
                            New Arrivals
                        </a>
                        <a href="products.php?category=trending" class="category-tag">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M23 6l-9.5 9.5-5-5L1 18"></path>
                            </svg>
                            Trending Now
                        </a>
                        <a href="products.php?category=deals" class="category-tag">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="19" y1="5" x2="5" y2="19"></line>
                                <circle cx="6.5" cy="6.5" r="2.5"></circle>
                                <circle cx="17.5" cy="17.5" r="2.5"></circle>
                            </svg>
                            Special Deals
                        </a>
                        <a href="products.php?category=featured" class="category-tag">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                            </svg>
                            Featured
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
    exit;
}

// Prepare the product IDs for querying
$productIds = array_keys($cart);
$placeholders = implode(',', array_fill(0, count($productIds), '?'));
$stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
$stmt->execute($productIds);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Your Cart</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-hover: #4338ca;
            --danger-color: #ef4444;
            --danger-hover: #dc2626;
        }

        body {
            background: #f8fafc;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            padding: 2rem 0;
        }

        .cart-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .cart-header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e5e7eb;
        }

        .cart-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }

        .cart-table {
            margin-bottom: 2rem;
        }

        .cart-table th {
            font-weight: 600;
            color: #4b5563;
            padding: 1rem;
            background: #f9fafb;
        }

        .cart-item {
            transition: all 0.3s ease;
        }

        .cart-item:hover {
            background: #f9fafb;
        }

        .product-name {
            font-weight: 500;
            color: #1f2937;
        }

        .product-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .product-image:hover {
            transform: scale(1.05);
        }

        .product-price {
            font-weight: 600;
            color: #1f2937;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: #f9fafb;
            padding: 0.5rem;
            border-radius: 999px;
        }

        .quantity-btn {
            width: 36px;
            height: 36px;
            border: none;
            background: white;
            color: #4b5563;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .quantity-btn:hover:not(:disabled) {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        .quantity-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .quantity-badge {
            font-weight: 600;
            color: #1f2937;
            min-width: 24px;
            text-align: center;
        }

        .remove-item {
            width: 40px;
            height: 40px;
            border: none;
            background: #fee2e2;
            color: var(--danger-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .remove-item:hover {
            background: var(--danger-color);
            color: white;
            transform: rotate(90deg);
        }

        .cart-total {
            background: #f9fafb;
            padding: 2rem;
            border-radius: 12px;
            margin-top: 2rem;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .total-label {
            font-size: 1.25rem;
            font-weight: 600;
            color: #4b5563;
        }

        .total-amount {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .btn {
            padding: 1rem 2rem;
            font-weight: 600;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
        }

        .btn-success {
            background: #059669;
            border: none;
        }

        .btn-success:hover {
            background: #047857;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.2);
        }

        .loading-spinner {
            display: none;
            width: 24px;
            height: 24px;
            border: 3px solid #f3f4f6;
            border-top: 3px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .custom-toast {
            background: #059669;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            padding: 1rem 1.5rem;
            color: white;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .cart-container {
                padding: 1rem;
            }

            .cart-table {
                display: block;
                overflow-x: auto;
            }

            .btn {
                width: 100%;
                justify-content: center;
                margin-bottom: 0.5rem;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="cart-container">
        <div class="cart-header">
            <h1 class="cart-title">Shopping Cart</h1>
        </div>
        
        <table class="table cart-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Image</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $grandTotal = 0;
                foreach ($products as $product): 
                    $productId = $product['id'];
                    $quantity = $cart[$productId];
                    $totalPrice = $product['price'] * $quantity;
                    $grandTotal += $totalPrice;
                ?>
                    <tr class="cart-item" data-product-id="<?php echo $productId; ?>">
                        <td data-label="Product" class="product-name"><?php echo htmlspecialchars($product['name']); ?></td>
                        <td data-label="Image">
                           <img class="product-image" src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        </td>
                        <td data-label="Price" class="product-price">$<?php echo number_format($product['price'], 2); ?></td>
                        <td data-label="Quantity">
                            <div class="quantity-controls">
                                <button class="quantity-btn decrease-quantity" data-product-id="<?php echo $productId; ?>" <?php echo $quantity <= 1 ? 'disabled' : ''; ?>>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                    </svg>
                                </button>
                                <span class="quantity-badge"><?php echo $quantity; ?></span>
                                <button class="quantity-btn increase-quantity" data-product-id="<?php echo $productId; ?>">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                    </svg>
                                </button>
                            </div>
                        </td>
                        <td data-label="Total" class="product-price">$<?php echo number_format($totalPrice, 2); ?></td>
                        <td>
                            <button class="remove-item" data-product-id="<?php echo $productId; ?>">
                                <div class="loading-spinner"></div>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                </svg>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="cart-total">
            <div class="total-row">
                <span class="total-label">Total Amount</span>
                <span class="total-amount">$<?php echo number_format($grandTotal, 2); ?></span>
            </div>
            <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
                <a href="products.php" class="btn btn-primary">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    Continue Shopping
                </a>
                <a href="checkout.php" class="btn btn-success">
                    Proceed to Checkout
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle remove item
    document.querySelectorAll('.remove-item').forEach(button => {
        button.addEventListener('click', async function() {
            const productId = this.dataset.productId;
            const cartItem = this.closest('.cart-item');
            const spinner = this.querySelector('.loading-spinner');
            const icon = this.querySelector('svg');
            
            // Show loading state
            spinner.style.display = 'block';
            icon.style.display = 'none';
            
            try {
                const response = await fetch('cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=remove_item&product_id=${productId}`
                });

                const data = await response.json();
                
                if (data.success) {
                    // Add removing animation
                    cartItem.style.transform = 'translateX(-100%)';
                    cartItem.style.opacity = '0';
                    
                    // Show success toast
                    Toastify({
                        text: "Item removed from cart",
                        duration: 3000,
                        className: "custom-toast",
                        gravity: "top",
                        position: "right",
                    }).showToast();

                    // Remove item after animation
                    setTimeout(() => {
                        cartItem.remove();
                        updateCartTotal();
                        checkEmptyCart();
                    }, 300);
                }
            } catch (error) {
                console.error('Error:', error);
                Toastify({
                    text: "Failed to remove item",
                    duration: 3000,
                    className: "custom-toast",
                    style: { background: "var(--danger-color)" }
                }).showToast();
            } finally {
                spinner.style.display = 'none';
                icon.style.display = 'block';
            }
        });
    });

    function updateCartTotal() {
        const totals = Array.from(document.querySelectorAll('.cart-item td[data-label="Total"]'))
            .map(td => parseFloat(td.textContent.replace('$', '')));
        
        const grandTotal = totals.reduce((sum, total) => sum + total, 0);
        document.querySelector('.total-amount').textContent = `$${grandTotal.toFixed(2)}`;
    }

    function checkEmptyCart() {
        if (document.querySelectorAll('.cart-item').length === 0) {
            location.reload();
        }
    }
});
</script>
</body>
</html>
