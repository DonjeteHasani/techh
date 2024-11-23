<?php
session_start();
include 'config.php';
include 'recommendation_utils.php';

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']); // Define a variable to use in JavaScript

// Check if product ID is provided in the URL
if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit;
}

$productId = (int)$_GET['id'];

// Fetch product details from the database
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: products.php");
    exit;
}

// Fetch product reviews with average rating
$reviewsStmt = $pdo->prepare("SELECT reviews.rating, reviews.comment, reviews.review_date, users.username 
                              FROM reviews 
                              JOIN users ON reviews.user_id = users.id 
                              WHERE reviews.product_id = ? 
                              ORDER BY reviews.review_date DESC");
$reviewsStmt->execute([$productId]);
$reviews = $reviewsStmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate average rating
$avgRatingStmt = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as review_count FROM reviews WHERE product_id = ?");
$avgRatingStmt->execute([$productId]);
$ratingData = $avgRatingStmt->fetch(PDO::FETCH_ASSOC);
$averageRating = round($ratingData['avg_rating'], 1);
$reviewCount = $ratingData['review_count'];

// Fetch related products
$relatedProducts = getSimilarProducts($productId, $pdo, 4);

$recommendations = [];
if (isset($_SESSION['user_id'])) {
    $recommendations = getRecommendationsBasedOnHistory($_SESSION['user_id'], $pdo, 4);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo htmlspecialchars($product['name']); ?> - Product Details</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --accent-color: #dbeafe;
            --text-color: #374151;
            --light-bg: #f9fafb;
            --border-radius: 12px;
            --box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--text-color);
            background-color: var(--light-bg);
        }

        .navbar {
            background-color: white !important;
            box-shadow: var(--box-shadow);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-brand {
            font-weight: 700;
            color: var(--primary-color) !important;
            font-size: 1.5rem;
        }

        .nav-link {
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem !important;
            border-radius: var(--border-radius);
        }

        .nav-link:hover {
            background-color: var(--accent-color);
            color: var(--primary-color) !important;
        }

        .product-container {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 2rem;
            margin-top: 2rem;
        }

        .product-image {
            width: 100%;
            height: auto;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            transition: transform 0.3s ease;
        }

        .product-image:hover {
            transform: scale(1.02);
        }

        .product-name {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 1rem;
        }

        .product-price {
            font-size: 2rem;
            color: var(--primary-color);
            font-weight: 700;
            margin: 1rem 0;
        }

        .rating-stars {
            color: #ffc107;
            font-size: 1.2rem;
            margin-right: 0.5rem;
        }

        .stock-status {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            font-weight: 500;
            margin: 1rem 0;
        }

        .in-stock {
            background-color: #dcfce7;
            color: #166534;
        }

        .out-of-stock {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .quantity-selector {
            width: 120px;
            height: 45px;
            border-radius: var(--border-radius);
            border: 2px solid #e5e7eb;
            margin-right: 1rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border: none;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }

        .btn-outline-secondary {
            border: 2px solid #e5e7eb;
            color: var(--text-color);
        }

        .btn-outline-secondary:hover {
            background-color: var(--accent-color);
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .similar-products {
            margin-top: 4rem;
            padding: 2rem;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        .similar-products .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            transition: transform 0.3s ease;
        }

        .similar-products .card:hover {
            transform: translateY(-5px);
        }

        .similar-products .card-img-top {
            border-radius: var(--border-radius) var(--border-radius) 0 0;
            height: 200px;
            object-fit: cover;
        }

        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
        }

        .toast {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        footer {
            background-color: white;
            padding: 2rem 0;
            margin-top: 4rem;
            box-shadow: 0 -1px 0 0 rgba(0, 0, 0, 0.1);
        }

        .product-description {
            line-height: 1.8;
            color: #6b7280;
            margin: 1.5rem 0;
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        @media (max-width: 768px) {
            .product-container {
                padding: 1rem;
            }
            
            .product-name {
                font-size: 2rem;
            }
            
            .product-price {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-store me-2"></i>TechAI
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php"><i class="fas fa-home me-1"></i>Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="products.php"><i class="fas fa-box me-1"></i>Products</a>
                </li>
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <li class="nav-item"><a class="nav-link" href="login.php"><i class="fas fa-sign-in-alt me-1"></i>Login</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="cart.php"><i class="fas fa-shopping-cart me-1"></i>Cart</a></li>
                    <li class="nav-item"><a class="nav-link" href="profile.php"><i class="fas fa-user me-1"></i>Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i>Logout</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="toast-container"></div>

<div class="container product-container">
    <div class="row g-4">
        <div class="col-lg-6">
            <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
        </div>
        <div class="col-lg-6">
            <h1 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h1>

            <div class="mb-3">
                <span class="rating-stars">
                    <?php
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $averageRating) {
                            echo '<i class="fas fa-star"></i>';
                        } elseif ($i - $averageRating < 1) {
                            echo '<i class="fas fa-star-half-alt"></i>';
                        } else {
                            echo '<i class="far fa-star"></i>';
                        }
                    }
                    ?>
                </span>
                <span class="text-muted"><?php echo $averageRating; ?> (<?php echo $reviewCount; ?> reviews)</span>
            </div>

            <p class="product-price">$<?php echo number_format($product['price'], 2); ?></p>

            <div class="stock-status <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                <i class="fas <?php echo $product['stock'] > 0 ? 'fa-check-circle' : 'fa-times-circle'; ?> me-2"></i>
                <?php echo $product['stock'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
            </div>

            <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>

            <form id="addToCartForm" class="action-buttons">
                <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                <div class="d-flex align-items-center">
                    <select name="quantity" class="form-select quantity-selector">
                        <?php for ($i = 1; $i <= min(10, $product['stock']); $i++): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                    <button type="submit" class="btn btn-primary flex-grow-1" <?php echo $product['stock'] > 0 ? '' : 'disabled'; ?>>
                        <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                    </button>
                </div>
                <button type="button" class="btn btn-outline-secondary w-100 add-to-wishlist" data-product-id="<?php echo $product['id']; ?>">
                    <i class="far fa-heart me-2"></i>Add to Wishlist
                </button>
            </form>
        </div>
    </div>
</div>

<div class="container similar-products">
    <h3 class="mb-4">Similar Products You May Like</h3>
    <div class="row g-4">
        <?php if (!empty($relatedProducts)): ?>
            <?php foreach ($relatedProducts as $relatedProduct): ?>
                <div class="col-md-3">
                    <div class="card h-100">
                        <img src="<?php echo htmlspecialchars($relatedProduct['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($relatedProduct['name']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($relatedProduct['name']); ?></h5>
                            <p class="product-price h5">$<?php echo number_format($relatedProduct['price'], 2); ?></p>
                            <a href="productdetail.php?id=<?php echo $relatedProduct['id']; ?>" class="btn btn-primary w-100">
                                <i class="fas fa-eye me-2"></i>View Details
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-muted">No similar products found.</p>
        <?php endif; ?>
    </div>
</div>

<footer class="text-center">
    <div class="container">
        <p class="mb-0">&copy; <?php echo date("Y"); ?> TechAI. All Rights Reserved.</p>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const isLoggedIn = <?php echo json_encode($isLoggedIn); ?>;

    function showToast(message, type = 'success') {
        const toastHtml = `
            <div class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3000">
                <div class="toast-header bg-${type} text-white">
                    <strong class="me-auto">Notification</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">${message}</div>
            </div>`;
        
        $('.toast-container').append(toastHtml);
        const toastElement = $('.toast').last();
        const toast = new bootstrap.Toast(toastElement);
        toast.show();
        
        toastElement.on('hidden.bs.toast', function () { $(this).remove(); });
    }

    $('#addToCartForm').on('submit', function(e) {
        e.preventDefault();
        if (!isLoggedIn) {
            showToast('Please log in to add items to your cart.', 'danger');
            return;
        }

        const formData = $(this).serialize();
        $.ajax({
            url: 'add_to_cart.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    showToast(response.message, 'success');
                } else {
                    showToast(response.message, 'danger');
                }
            },
            error: function() {
                showToast('An error occurred. Please try again.', 'danger');
            }
        });
    });

    $('.add-to-wishlist').on('click', function() {
        if (!isLoggedIn) {
            showToast('Please log in to add items to your wishlist.', 'danger');
            return;
        }

        const productId = $(this).data('product-id');
        $.ajax({
            url: 'add_to_wishlist.php',
            type: 'POST',
            data: { product_id: productId },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    showToast(response.message, 'success');
                } else {
                    showToast(response.message, 'danger');
                }
            },
            error: function() {
                showToast('An error occurred. Please try again.', 'danger');
            }
        });
    });
</script>
</body>
</html>

</script>
</body>
</html>

