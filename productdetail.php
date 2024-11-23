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
// Fetch related products using recommendation_utils.php
$relatedProducts = getSimilarProducts($productId, $pdo, 4); // Fetch 4 similar products

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
    <style>
        .product-container { padding: 20px; margin-top: 20px; }
        .product-image { max-width: 100%; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .product-name { font-size: 2rem; font-weight: bold; margin-bottom: 15px; }
        .product-price { font-size: 1.75rem; color: #28a745; font-weight: bold; }
        .review-section { margin-top: 40px; padding: 20px; background-color: #f8f9fa; border-radius: 8px; }
        .rating-stars { color: #ffc107; font-size: 1.2rem; }
        .quantity-selector { width: 100px; margin-right: 10px; }
        .action-buttons { margin: 20px 0; }
        .toast-container { position: fixed; top: 20px; right: 20px; z-index: 1000; }
        .stock-status { display: inline-block; padding: 5px 10px; border-radius: 4px; margin-bottom: 15px; }
        .in-stock { background-color: #d4edda; color: #155724; }
        .out-of-stock { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

<!-- Toast Container for Notifications -->
<div class="toast-container"></div>

<!-- Product Details Section -->
<div class="container product-container">
    <div class="row">
        <div class="col-md-6">
            <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
        </div>
        <div class="col-md-6">
            <h1 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h1>

            <!-- Rating Summary -->
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
                <span class="ms-2"><?php echo $averageRating; ?> (<?php echo $reviewCount; ?> reviews)</span>
            </div>

            <p class="product-price">$<?php echo number_format($product['price'], 2); ?></p>

            <!-- Stock Status -->
            <div class="stock-status <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                <?php echo $product['stock'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
            </div>

            <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>

            <!-- Add to Cart Form -->
            <form id="addToCartForm" class="action-buttons">
                <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                <div class="d-flex align-items-center mb-3">
                    <select name="quantity" class="form-select quantity-selector me-2">
                        <?php for ($i = 1; $i <= min(10, $product['stock']); $i++): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                    <button type="submit" class="btn btn-primary" <?php echo $product['stock'] > 0 ? '' : 'disabled'; ?>>
                        <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                    </button>
                </div>
                <button type="button" class="btn btn-outline-secondary add-to-wishlist" data-product-id="<?php echo $product['id']; ?>">
                    <i class="far fa-heart me-2"></i>Add to Wishlist
                </button>
            </form>
            
        </div>
    </div>
</div>
<!-- Similar Products Section -->
<div class="container mt-5">
    <h3>Similar Products You May Like</h3>
    <div class="row">
        <?php if (!empty($relatedProducts)): ?>
            <?php foreach ($relatedProducts as $relatedProduct): ?>
                <div class="col-md-3">
                    <div class="card h-100">
                        <img src="<?php echo htmlspecialchars($relatedProduct['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($relatedProduct['name']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($relatedProduct['name']); ?></h5>
                            <p class="product-price">$<?php echo number_format($relatedProduct['price'], 2); ?></p>
                            <a href="productdetail.php?id=<?php echo $relatedProduct['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-eye me-2"></i>View Details
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No similar products found.</p>
        <?php endif; ?>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const isLoggedIn = <?php echo json_encode($isLoggedIn); ?>;

    function showToast(message, type = 'success') {
        const toastHtml = `<div class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3000">
            <div class="toast-header bg-${type} text-white">
                <strong class="me-auto">Notification</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">${message}</div>
        </div>`;
        
        $('.toast-container').append(toastHtml);
        const toastElement = $('.toast').last();
        const toast = new bootstrap.Toast(toastElement);
        toast.show();
        
        toastElement.on('hidden.bs.toast', function () { $(this).remove(); });
    }

    // Add to Cart Handler
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

    // Add to Wishlist Handler
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

