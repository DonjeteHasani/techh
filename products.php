<?php
session_start();
include 'config.php';

// Fetch categories from the database
$categoryStmt = $pdo->prepare("SELECT * FROM categories");
$categoryStmt->execute();
$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize parameters
$searchTerm = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';
$categoryId = isset($_GET['category']) ? intval($_GET['category']) : null;

// Pagination settings
$productsPerPage = 6; // Number of products per page
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1; // Current page, default to 1
$offset = ($currentPage - 1) * $productsPerPage; // Calculate offset for the query

// Count total products for pagination
$countQuery = "SELECT COUNT(*) as total FROM products WHERE (name LIKE ? OR description LIKE ?)";
$countParams = [$searchTerm, $searchTerm];
if ($categoryId) {
    $countQuery .= " AND category_id = ?";
    $countParams[] = $categoryId;
}
$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($countParams);
$totalProducts = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

// Calculate total pages
$totalPages = ceil($totalProducts / $productsPerPage);

// Fetch products with pagination
$query = "SELECT * FROM products WHERE (name LIKE ? OR description LIKE ?)";
$params = [$searchTerm, $searchTerm];
if ($categoryId) {
    $query .= " AND category_id = ?";
    $params[] = $categoryId;
}
$wishlist = [];
if (isset($_SESSION['is_logged_in'])) {
    $userId = $_SESSION['user_id'];
    $wishlistStmt = $pdo->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
    $wishlistStmt->execute([$userId]);
    $wishlist = $wishlistStmt->fetchAll(PDO::FETCH_COLUMN);
}

// Append LIMIT and OFFSET directly to the query
$query .= " LIMIT $productsPerPage OFFSET $offset";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Products | MyShop</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    
     <!-- Social Media Meta Tags -->
     <meta property="og:title" content="TechAI - Premium Tech Products">
    <meta property="og:description" content="Discover our curated collection of premium tech products and accessories at TechAI">
    <meta property="og:image" content="https://techai.com/images/social-preview.jpg">
    <meta property="og:url" content="https://techai.com/products">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@techai">
    <meta name="twitter:title" content="TechAI - Premium Tech Products">
    <meta name="twitter:description" content="Discover our curated collection of premium tech products and accessories at TechAI">
    <meta name="twitter:image" content="https://techai.com/images/social-preview.jpg">
    <style>
        :root {
            --primary-color: #4F46E5;
            --secondary-color: #818CF8;
            --success-color: #10B981;
            --background-color: #F9FAFB;
            --text-color: #374151;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --hover-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: var(--background-color);
        }

        .navbar {
            padding: 1rem 0;
            background: white !important;
            box-shadow: var(--card-shadow);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--primary-color) !important;
            transition: transform 0.2s;
        }

        .navbar-brand:hover {
            transform: scale(1.05);
        }

        .nav-link {
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            color: var(--text-color) !important;
            transition: all 0.3s ease;
            border-radius: 0.5rem;
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
            background-color: rgba(79, 70, 229, 0.1);
        }

        .search-container {
            background-color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: var(--card-shadow);
        }

        .search-bar {
            max-width: 600px;
            margin: 0 auto;
        }

        .search-bar .form-control {
            border-radius: 9999px;
            padding: 1rem 1.5rem;
            border: 2px solid #E5E7EB;
            box-shadow: none;
            transition: all 0.3s;
            font-size: 1rem;
        }

        .search-bar .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
        }

        .search-bar .btn-primary {
            border-radius: 9999px;
            padding: 1rem 2rem;
            background-color: var(--primary-color);
            border: none;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .search-bar .btn-primary:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }

        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            background: white;
            position: relative;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: var(--hover-shadow);
        }

        .card-img-top {
            height: 250px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .card:hover .card-img-top {
            transform: scale(1.05);
        }

        .card-body {
            padding: 1.5rem;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.75rem;
        }

        .product-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--success-color);
            margin-bottom: 1rem;
        }

        .btn-add-to-cart, 
        .btn-view-details {
            width: 100%;
            padding: 0.75rem;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.75rem;
        }

        .btn-add-to-cart {
            background-color: var(--primary-color);
            border: none;
            color: white;
        }

        .btn-add-to-cart:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }

        .btn-view-details {
            background-color: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }

        .btn-view-details:hover {
            background-color: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        .add-to-wishlist {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
            z-index: 1;
        }

        .add-to-wishlist:hover {
            transform: scale(1.1);
        }

        .add-to-wishlist i {
            color: #FF4B4B;
            font-size: 1.2rem;
        }

        .add-to-wishlist.added {
            background-color: #FF4B4B;
        }

        .add-to-wishlist.added i {
            color: white;
        }

        .pagination {
            margin-top: 2rem;
        }

        .page-link {
            color: var(--primary-color);
            border: none;
            padding: 0.75rem 1rem;
            margin: 0 0.25rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .page-link:hover {
            background-color: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .list-group-item {
            border: none;
            margin-bottom: 0.5rem;
            border-radius: 0.5rem !important;
            transition: all 0.3s ease;
        }

        .list-group-item:hover {
            background-color: rgba(79, 70, 229, 0.1);
            transform: translateX(5px);
        }

        .list-group-item.active {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .list-group-item.active a {
            color: white !important;
            font-weight: 600;
        }

        .toast {
            background: white;
            border-radius: 1rem;
            box-shadow: var(--card-shadow);
            border: none;
        }

        .toast-body {
            padding: 1rem;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .search-bar {
                width: 90%;
            }

            .card-img-top {
                height: 200px;
            }

            .navbar-brand {
                font-size: 1.25rem;
            }
        }

        footer {
            background-color: white;
            padding: 2rem 0;
            margin-top: 4rem;
            box-shadow: 0 -1px 0 0 rgba(0, 0, 0, 0.1);
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 2rem;
            text-align: center;
            position: relative;
        }

        .section-title:after {
            content: '';
            display: block;
            width: 50px;
            height: 4px;
            background: var(--primary-color);
            margin: 1rem auto;
            border-radius: 2px;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-microchip me-2"></i>TechAI
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="products.php">
                            <i class="fas fa-box me-1"></i>Products
                        </a>
                    </li>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="fas fa-sign-in-alt me-1"></i>Login
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="cart.php">
                                <i class="fas fa-shopping-cart me-1"></i>Cart
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">
                                <i class="fas fa-user me-1"></i>Profile
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="fas fa-sign-out-alt me-1"></i>Logout
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Search Section -->
    <div class="search-container">
        <div class="container">
            <form method="GET" action="products.php" class="search-bar">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search for products..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search me-2"></i>Search</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Products Section -->
    <div class="container">
        <div class="row">
            <!-- Categories Sidebar -->
            <div class="col-md-3">
                <h4 class="mb-3">Categories</h4>
                <ul class="list-group">
                    <li class="list-group-item <?php echo !isset($_GET['category']) ? 'active' : ''; ?>">
                        <a href="products.php<?php echo isset($_GET['search']) ? '?search=' . htmlspecialchars($_GET['search']) : ''; ?>" class="text-decoration-none <?php echo !isset($_GET['category']) ? 'text-white' : ''; ?>">
                            Show All Products
                        </a>
                    </li>
                    <?php foreach ($categories as $category): ?>
                        <li class="list-group-item <?php echo isset($_GET['category']) && $_GET['category'] == $category['id'] ? 'active' : ''; ?>">
                            <a href="products.php?category=<?php echo $category['id']; ?><?php echo isset($_GET['search']) ? '&search=' . htmlspecialchars($_GET['search']) : ''; ?>" 
                               class="text-decoration-none <?php echo isset($_GET['category']) && $_GET['category'] == $category['id'] ? 'text-white' : ''; ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Products Grid -->
            <div class="col-md-9">
                <h2 class="section-title">Our Products</h2>
                <div class="row g-4">
                    <?php if ($products): ?>
                        <?php foreach ($products as $product): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100">
                                    <button type="button" 
                                        class="add-to-wishlist <?php echo in_array($product['id'], $wishlist) ? 'added' : ''; ?>" 
                                        data-product-id="<?php echo $product['id']; ?>">
                                        <i class="<?php echo in_array($product['id'], $wishlist) ? 'fas fa-heart' : 'far fa-heart'; ?>"></i>
                                    </button>
                                    <img src="<?php echo htmlspecialchars($product['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                        <p class="product-price">$<?php echo number_format($product['price'], 2); ?></p>
                                        <p class="card-text"><?php echo htmlspecialchars($product['description']); ?></p>
                                        <button class="btn btn-add-to-cart" data-product-id="<?php echo $product['id']; ?>">
                                            <i class="fas fa-cart-plus me-2"></i>Add to Cart
                                        </button>
                                        <a href="productdetail.php?id=<?php echo $product['id']; ?>" class="btn btn-view-details">
                                            <i class="fas fa-info-circle me-2"></i>View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle me-2"></i>
                                No products found for this category or search term.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="products.php?page=<?php echo $currentPage - 1; ?><?php echo isset($_GET['category']) ? '&category=' . htmlspecialchars($_GET['category']) : ''; ?><?php echo isset($_GET['search']) ? '&search=' . htmlspecialchars($_GET['search']) : ''; ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo $i == $currentPage ? 'active' : ''; ?>">
                                    <a class="page-link" href="products.php?page=<?php echo $i; ?><?php echo isset($_GET['category']) ? '&category=' . htmlspecialchars($_GET['category']) : ''; ?><?php echo isset($_GET['search']) ? '&search=' . htmlspecialchars($_GET['search']) : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="products.php?page=<?php echo $currentPage + 1; ?><?php echo isset($_GET['category']) ? '&category=' . htmlspecialchars($_GET['category']) : ''; ?><?php echo isset($_GET['search']) ? '&search=' . htmlspecialchars($_GET['search']) : ''; ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3"></div>

    <!-- Footer -->
    <footer class="text-center">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date("Y"); ?> TechAI. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            function showToast(message, type = 'success') {
                const toast = `
                    <div class="toast" role="alert">
                        <div class="toast-body d-flex align-items-center">
                            <i class="fas fa-${type === 'success' ? 'check-circle text-success' : 'exclamation-circle text-danger'} me-2"></i>
                            ${message}
                        </div>
                    </div>
                `;
                
                $('.toast-container').append(toast);
                const $toast = $('.toast').last();
                $toast.toast({ delay: 3000 }).toast('show');
                
                $toast.on('hidden.bs.toast', function() {
                    $(this).remove();
                });
            }

            $('.btn-add-to-cart').click(function() {
                const $btn = $(this);
                const productId = $btn.data('product-id');
                
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Adding...');
                
                $.ajax({
                    url: 'add_to_cart.php',
                    type: 'POST',
                    data: { product_id: productId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === "success") {
                            showToast(response.message, 'success');
                        } else {
                            showToast(response.message, 'error');
                            if (response.message === 'Please log in to add items to the cart.') {
                                setTimeout(() => {
                                    window.location.href = "login.php";
                                }, 2000);
                            }
                        }
                    },
                    error: function() {
                        showToast("An error occurred. Please try again.", 'error');
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html('<i class="fas fa-cart-plus me-2"></i>Add to Cart');
                    }
                });
            });

            $('.add-to-wishlist').click(function() {
                const $btn = $(this);
                const productId = $btn.data('product-id');
                const action = $btn.hasClass('added') ? 'remove' : 'add';

                $.ajax({
                    url: 'add_to_wishlist.php',
                    type: 'POST',
                    data: { product_id: productId, action: action },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            if (action === 'add') {
                                $btn.addClass('added').html('<i class="fas fa-heart"></i>');
                            } else {
                                $btn.removeClass('added').html('<i class="far fa-heart"></i>');
                            }
                            showToast(response.message, 'success');
                        } else {
                            showToast(response.message, 'error');
                        }
                    },
                    error: function() {
                        showToast('An error occurred. Please try again.', 'error');
                    }
                });
            });
        });
    </script>
</body>
</html>
