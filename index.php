<?php

session_start();
include 'config.php';

// Pagination setup
$items_per_page = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Filter and sort parameters
$priceFilter = isset($_GET['priceFilter']) ? $_GET['priceFilter'] : '';
$sortFilter = isset($_GET['sortFilter']) ? $_GET['sortFilter'] : 'latest';

// Base query
$query = "SELECT * FROM products WHERE 1";

// Apply price range filter
if ($priceFilter) {
    if ($priceFilter === "0-100") {
        $query .= " AND price BETWEEN 0 AND 100";
    } elseif ($priceFilter === "101-500") {
        $query .= " AND price BETWEEN 101 AND 500";
    } elseif ($priceFilter === "501-1000") {
        $query .= " AND price BETWEEN 501 AND 1000";
    } elseif ($priceFilter === "1001+") {
        $query .= " AND price > 1001";
    }
}

// Apply sorting
if ($sortFilter === "price-low") {
    $query .= " ORDER BY price ASC";
} elseif ($sortFilter === "price-high") {
    $query .= " ORDER BY price DESC";
} elseif ($sortFilter === "name") {
    $query .= " ORDER BY name ASC";
}

// Pagination
$query .= " LIMIT :offset, :items_per_page";

// Prepare and execute query
$stmt = $pdo->prepare($query);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':items_per_page', $items_per_page, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total products for pagination
$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_pages = ceil($total_products / $items_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - TechAI</title>
    
    <!-- Stylesheets -->
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
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --accent-color: #dbeafe;
            --text-primary: #1a365d;
            --text-secondary: #475569;
            --bg-light: #f8fafc;
            --transition: all 0.3s ease;
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
            --radius-sm: 0.5rem;
            --radius-md: 1rem;
            --radius-lg: 2rem;
        }

        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: var(--text-primary);
            background-color: #ffffff;
        }

        /* Enhanced Navbar */
        .navbar {
        padding: 1rem 0;
        background: white !important;
        box-shadow: var(--card-shadow);
    }

    .navbar-brand {
        font-weight: 700;
        font-size: 1.5rem;
        color: var(--primary-color) !important;
    }

    .nav-link {
        font-weight: 500;
        padding: 0.5rem 1rem !important;
        color: var(--text-color) !important;
        transition: all 0.2s ease;
    }

    .nav-link:hover {
        color: var(--primary-color) !important;
    }

        /* Enhanced Header */
        .page-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 4rem 0;
            margin-bottom: 4rem;
            border-radius: 0 0 var(--radius-lg) var(--radius-lg);
            text-align: center;
        }

        .page-title {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }

        /* Enhanced Filter Section */
        .filter-section {
            background: white;
            padding: 2rem;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-md);
            margin-bottom: 3rem;
        }

        .filter-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 1.25rem;
        }

        .form-select {
            padding: 0.75rem;
            border-radius: var(--radius-sm);
            border: 2px solid var(--accent-color);
            transition: var(--transition);
        }

        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(37, 99, 235, 0.25);
        }

        /* Enhanced Product Cards */
        .product-card {
            border: none;
            border-radius: var(--radius-md);
            overflow: hidden;
            box-shadow: var(--shadow-md);
            transition: var(--transition);
            background: white;
        }

        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-lg);
        }

        .product-card .card-img-top {
            height: 250px;
            object-fit: cover;
            transition: var(--transition);
        }

        .product-card:hover .card-img-top {
            transform: scale(1.05);
        }

        .product-card .card-body {
            padding: 2rem;
        }

        .product-card .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .product-price {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }

        .btn-view-product {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
            padding: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-radius: var(--radius-sm);
            transition: var(--transition);
        }

        .btn-view-product:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(37, 99, 235, 0.4);
        }

        /* Enhanced Pagination */
        .pagination {
            margin-top: 3rem;
            gap: 0.5rem;
        }

        .page-link {
            padding: 0.75rem 1.25rem;
            border-radius: var(--radius-sm);
            font-weight: 600;
            transition: var(--transition);
        }

        .page-item.active .page-link {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
        }

        /* Enhanced Services Section */
        .services-section {
            background: linear-gradient(135deg, var(--bg-light) 0%, white 100%);
            padding: 5rem 0;
            margin-top: 5rem;
        }

        .services-title {
            font-size: 3rem;
            font-weight: 800;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 2rem;
        }

        .services-description {
            font-size: 1.25rem;
            color: var(--text-secondary);
            max-width: 800px;
            margin: 0 auto 4rem;
        }

        .service-card {
            background: white;
            border-radius: var(--radius-md);
            padding: 2.5rem;
            box-shadow: var(--shadow-md);
            transition: var(--transition);
        }

        .service-card i {
            font-size: 3rem;
            margin-bottom: 1.5rem;
            color: var(--primary-color);
            transition: var(--transition);
        }

        .service-card:hover {
            transform: translateY(-10px);
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
        }

        .service-card:hover i,
        .service-card:hover h2,
        .service-card:hover p {
            color: white;
        }

        /* Enhanced Footer */
        footer {
            background: var(--bg-light);
            padding: 4rem 0 2rem;
            margin-top: 5rem;
        }

        .footer-content {
            color: var(--text-secondary);
        }

        .footer-content a {
            transition: var(--transition);
        }

        .footer-content a:hover {
            color: var(--primary-color) !important;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .page-header {
                padding: 3rem 0;
            }

            .page-title {
                font-size: 2.5rem;
            }

            .services-title {
                font-size: 2.5rem;
            }

            .product-card .card-img-top {
                height: 200px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
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

    <!-- Page Header -->
    <header class="page-header">
        <div class="container">
            <h1 class="page-title">Discover Our Products</h1>
            <p class="lead text-white-50">Explore our curated collection of premium tech products and accessories</p>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container">
        <!-- Filters Section -->
        <div class="filter-section">
            <div class="row">
                <div class="col-md-6">
                    <h3 class="filter-title">Price Range</h3>
                    <div class="form-group">
                        <select class="form-select" id="priceFilter">
                            <option value="">All Prices</option>
                            <option value="0-100">$0 - $100</option>
                            <option value="101-500">$101 - $500</option>
                            <option value="501-1000">$501 - $1000</option>
                            <option value="1001+">$1001+</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <h3 class="filter-title">Sort By</h3>
                    <div class="form-group">
                        <select class="form-select" id="sortFilter">
                            <option value="latest">Latest</option>
                            <option value="price-low">Price: Low to High</option>
                            <option value="price-high">Price: High to Low</option>
                            <option value="name">Name</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="row g-4">
            <?php foreach ($products as $product): ?>
                <div class="col-md-3">
                    <div class="product-card card h-100">
                        <img src="<?php echo htmlspecialchars($product['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <p class="product-price">$<?php echo number_format($product['price'], 2); ?></p>
                            <a href="productdetail.php?id=<?php echo $product['id']; ?>" class="btn btn-primary btn-view-product mt-auto">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Product pagination">
            <ul class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>

        <!-- Services Section -->
        <div class="services-section">
            <div class="container">
                <h1 class="services-title text-center">Our Services</h1>
                <p class="services-description text-center">
                    Experience excellence with our comprehensive range of tech services designed to meet your every need
                </p>

                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="service-card text-center">
                            <i class='bx bxs-purchase-tag-alt'></i>
                            <h2 class="service-title">Premium Products</h2>
                            <p class="service-description">
                                Discover the latest and most innovative technology products carefully selected for you
                            </p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="service-card text-center">
                            <i class='bx bx-wrench'></i>
                            <h2 class="service-title">Expert Repair</h2>
                            <p class="service-description">
                                Professional repair services for all your tech devices by certified technicians
                            </p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="service-card text-center">
                            <i class='bx bxs-devices'></i>
                            <h2 class="service-title">Device Swap</h2>
                            <p class="service-description">
                                Comprehensive warranty coverage with free servicing for up to 2 years
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row gy-4">
                <div class="col-md-4">
                    <h5 class="fw-bold mb-3">About TechAI</h5>
                    <p class="footer-content">Your premier destination for cutting-edge tech products and exceptional service.</p>
                </div>
                <div class="col-md-4">
                    <h5 class="fw-bold mb-3">Quick Links</h5>
                    <ul class="list-unstyled footer-content">
                        <li class="mb-2"><a href="products.php" class="text-decoration-none text-secondary">Products</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none text-secondary">About Us</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none text-secondary">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5 class="fw-bold mb-3">Connect With Us</h5>
                    <div class="footer-content">
                        <a href="https://www.facebook.com" class="text-secondary me-3"><i class="fab fa-facebook-f fa-lg"></i></a>
                        <a href="https://www.x.com" class="text-secondary me-3"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="https://www.instagram.com" class="text-secondary me-3"><i class="fab fa-instagram fa-lg"></i></a>
                    </div>
                </div>
            </div>
            <div class="text-center mt-4">
                <p class="footer-content mb-0">&copy; <?php echo date("Y"); ?> TechAI. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Tawk.to Chatbot Integration -->
    <script type="text/javascript">
    var Tawk_API = Tawk_API || {}, Tawk_LoadStart = new Date();
    (function(){
        var s1 = document.createElement("script"), s0 = document.getElementsByTagName("script")[0];
        s1.async = true;
        s1.src = 'https://embed.tawk.to/674126e94304e3196ae74915/1idb8009d';
        s1.charset = 'UTF-8';
        s1.setAttribute('crossorigin', '*');
        s0.parentNode.insertBefore(s1, s0);
    })();
    </script>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Enhanced filter handling with smooth transitions
        document.getElementById('priceFilter').addEventListener('change', function() {
            applyFilters();
        });

        document.getElementById('sortFilter').addEventListener('change', function() {
            applyFilters();
        });

        function applyFilters() {
            const priceFilter = document.getElementById('priceFilter').value;
            const sortFilter = document.getElementById('sortFilter').value;

            const params = new URLSearchParams(window.location.search);
            if (priceFilter) {
                params.set('priceFilter', priceFilter);
            } else {
                params.delete('priceFilter');
            }

            if (sortFilter) {
                params.set('sortFilter', sortFilter);
            } else {
                params.delete('sortFilter');
            }

            // Add loading state
            document.body.style.cursor = 'wait';
            const products = document.querySelector('.row.g-4');
            products.style.opacity = '0.5';
            products.style.transition = 'opacity 0.3s ease';

            // Navigate to new URL
            window.location.search = params.toString();
        }

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    </script>
</body>
</html>
