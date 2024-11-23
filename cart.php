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
            }

            .empty-cart-container {
                background: white;
                border-radius: 24px;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                max-width: 600px;
                margin: 0 auto;
                padding: 3rem 2rem;
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
                height: 6px;
                background: linear-gradient(90deg, var(--primary-color), #818cf8);
            }

            .empty-cart-icon {
                background: #f3f4f9;
                width: 120px;
                height: 120px;
                border-radius: 60px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 2rem;
                position: relative;
                animation: pulseIcon 2s infinite;
            }
 

            @keyframes pulseIcon {
                0% { transform: scale(1); }
                50% { transform: scale(1.05); }
                100% { transform: scale(1); }
            }

            .empty-cart-icon svg {
                width: 60px;
                height: 60px;
                stroke: var(--primary-color);
                stroke-width: 1.5;
            }

            .empty-cart-title {
                font-size: 2.25rem;
                font-weight: 700;
                color: #1f2937;
                margin-bottom: 1rem;
                line-height: 1.2;
            }

            .empty-cart-message {
                font-size: 1.125rem;
                color: #6b7280;
                margin-bottom: 2.5rem;
                max-width: 400px;
                margin-left: auto;
                margin-right: auto;
            }

            .shop-now-btn {
                background: var(--primary-color);
                color: white;
                border: none;
                padding: 1rem 2.5rem;
                border-radius: 9999px;
                font-weight: 600;
                font-size: 1.125rem;
                transition: all 0.3s ease;
                display: inline-flex;
                align-items: center;
                gap: 0.75rem;
                position: relative;
                overflow: hidden;
            }

            .shop-now-btn:hover {
                background: var(--primary-hover);
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
                color: white;
            }

            .shop-now-btn:active {
                transform: translateY(0);
            }

            .decorative-dots {
                position: absolute;
                width: 150px;
                height: 150px;
                opacity: 0.1;
                z-index: 0;
            }

            .dots-left {
                left: -75px;
                top: -75px;
                background: radial-gradient(circle, var(--primary-color) 2px, transparent 2.5px);
                background-size: 15px 15px;
                transform: rotate(-15deg);
            }

            .dots-right {
                right: -75px;
                bottom: -75px;
                background: radial-gradient(circle, var(--primary-color) 2px, transparent 2.5px);
                background-size: 15px 15px;
                transform: rotate(15deg);
            }

            .product-suggestions {
                margin-top: 3rem;
                padding-top: 2rem;
                border-top: 1px solid #e5e7eb;
            }

            .suggestion-title {
                font-size: 1.25rem;
                color: #4b5563;
                margin-bottom: 1.5rem;
                font-weight: 500;
            }

            .categories {
                display: flex;
                justify-content: center;
                gap: 1rem;
                flex-wrap: wrap;
            }

            .category-tag {
                background: #f3f4f9;
                color: var(--primary-color);
                padding: 0.5rem 1rem;
                border-radius: 9999px;
                font-size: 0.875rem;
                font-weight: 500;
                transition: all 0.2s;
            }
         


            .category-tag:hover {
                background: var(--primary-color);
                color: white;
                text-decoration: none;
            }

            @media (max-width: 640px) {
                body {
                    padding: 1rem;
                }

                .empty-cart-container {
                    padding: 2rem 1rem;
                    border-radius: 16px;
                }

                .empty-cart-icon {
                    width: 100px;
                    height: 100px;
                }

                .empty-cart-icon svg {
                    width: 50px;
                    height: 50px;
                }

                .empty-cart-title {
                    font-size: 1.75rem;
                }

                .empty-cart-message {
                    font-size: 1rem;
                }

                .shop-now-btn {
                    width: 100%;
                    justify-content: center;
                }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="empty-cart-container">
                <!-- Decorative elements -->
                <div class="decorative-dots dots-left"></div>
                <div class="decorative-dots dots-right"></div>

                <!-- Main content -->
                <div class="empty-cart-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                </div>
                
                <h1 class="empty-cart-title">Your Cart is Empty</h1>
                <p class="empty-cart-message">
                    Looks like you haven't added anything to your cart yet. 
                    Start exploring our amazing collection!
                </p>
                
                <a href="products.php" class="shop-now-btn">
                    Start Shopping
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </a>

                <!-- Popular categories section -->
                <div class="product-suggestions">
                    <h2 class="suggestion-title">Popular Categories</h2>
                    <div class="categories">
                        <a href="products.php?category=new" class="category-tag">New Arrivals</a>
                        <a href="products.php?category=trending" class="category-tag">Trending</a>
                        <a href="products.php?category=deals" class="category-tag">Best Deals</a>
                        <a href="products.php?category=featured" class="category-tag">Featured</a>
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
        /* Previous styles remain the same */
        
        /* New styles for remove functionality */
        .remove-item {
            border: none;
            background: none;
            color: #ef4444;
            padding: 0.5rem;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .remove-item:hover {
            background-color: #fee2e2;
            transform: scale(1.1);
        }

        .cart-item {
            transition: opacity 0.3s, transform 0.3s;
        }

        .cart-item.removing {
            opacity: 0;
            transform: translateX(-100%);
        }

        /* Toast Styles */
        .custom-toast {
            background-color: #059669;
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .quantity-btn {
            border: 1px solid #e5e7eb;
            background: white;
            color: #374151;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .quantity-btn:hover {
            background-color: #f3f4f6;
            transform: scale(1.1);
        }

        .quantity-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Loading Spinner */
        .loading-spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid #f3f4f6;
            border-top: 2px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Previous responsive styles remain the same */
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
                           <img class="product-image" src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width: 80px; height: auto; object-fit: cover; border-radius: 8px;">
                        </td>

                        <td data-label="Price" class="product-price">$<?php echo number_format($product['price'], 2); ?></td>
                        <td data-label="Quantity">
                            <div class="quantity-controls">
                                <button class="quantity-btn decrease-quantity" data-product-id="<?php echo $productId; ?>" <?php echo $quantity <= 1 ? 'disabled' : ''; ?>>
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                    </svg>
                                </button>
                                <span class="quantity-badge"><?php echo $quantity; ?></span>
                                <button class="quantity-btn increase-quantity" data-product-id="<?php echo $productId; ?>">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    <line x1="10" y1="11" x2="10" y2="17"></line>
                                    <line x1="14" y1="11" x2="14" y2="17"></line>
                                </svg>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="cart-total">
            <div class="total-row mb-4">
                <span class="total-label">Total Amount</span>
                <span class="total-amount">$<?php echo number_format($grandTotal, 2); ?></span>
            </div>
            <div class="d-flex flex-column flex-md-row justify-content-between gap-2">
                <a href="products.php" class="btn btn-primary">
                    <svg class="me-2" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    Continue Shopping
                </a>
                <a href="checkout.php" class="btn btn-success">
                    Proceed to Checkout
                    <svg class="ms-2" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                    cartItem.classList.add('removing');
                    
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
                // Show error toast
                Toastify({
                    text: "Failed to remove item",
                    duration: 3000,
                    className: "custom-toast",
                    style: { background: "#ef4444" }
                }).showToast();
            } finally {
                // Reset button state
                spinner.style.display = 'none';
                icon.style.display = 'block';
            }
        });
    });

    // Update cart total
    function updateCartTotal() {
        const totals = Array.from(document.querySelectorAll('.cart-item td[data-label="Total"]'))
            .map(td => parseFloat(td.textContent.replace('$', '')));
        
        const grandTotal = totals.reduce((sum, total) => sum + total, 0);
        document.querySelector('.total-amount').textContent = `$${grandTotal.toFixed(2)}`;
    }

    // Check if cart is empty
    function checkEmptyCart() {
        if (document.querySelectorAll('.cart-item').length === 0) {
            location.reload(); // Reload to show empty cart state
        }
    }
});
</script>
</body>
</html>