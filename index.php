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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Custom styles - matching index.php */
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --accent-color: #dbeafe;
        }
         .navbar {
            background-color: white !important;
            box-shadow: var(--card-shadow);
            padding: 1rem 0;
        }

        .navbar-brand {
            font-weight: 700;
            color: var(--primary-color) !important;
            font-size: 1.5rem;
        }

        .nav-link {
            font-weight: 500;
            color: #374151 !important;
            transition: color 0.2s;
            padding: 0.5rem 1rem !important;
            margin: 0 0.25rem;
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
        }

        .cart-badge .badge {
            position: absolute;
            top: 0;
            right: -5px;
            background-color: var(--primary-color);
        }

        .page-header {
            background: linear-gradient(135deg, var(--accent-color) 0%, #ffffff 100%);
            padding: 2rem 0;
            margin-bottom: 3rem;
            border-radius: 0 0 2rem 2rem;
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: bold;
            color: #1a365d;
            margin-bottom: 1rem;
        }

        .filter-section {
            background-color: #f8fafc;
            padding: 1.5rem;
            border-radius: 1rem;
            margin-bottom: 2rem;
        }

        .filter-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1a365d;
            margin-bottom: 1rem;
        }

        .product-card {
            border: none;
            border-radius: 1rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 2rem;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .product-card .card-img-top {
            height: 200px;
            object-fit: cover;
            border-radius: 1rem 1rem 0 0;
        }

        .product-card .card-body {
            padding: 1.5rem;
        }

        .product-card .card-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .product-price {
            color: var(--primary-color);
            font-size: 1.25rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }

        .btn-view-product {
            width: 100%;
            background-color: var(--primary-color);
            border: none;
            padding: 0.8rem;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }

        .btn-view-product:hover {
            background-color: var(--secondary-color);
        }

        .pagination {
            margin-top: 2rem;
            justify-content: center;
        }

        .page-link {
            color: var(--primary-color);
            border: none;
            padding: 0.5rem 1rem;
            margin: 0 0.25rem;
            border-radius: 0.5rem;
        }

        .page-link:hover {
            background-color: var(--accent-color);
            color: var(--primary-color);
        }

        .page-item.active .page-link {
            background-color: var(--primary-color);
            color: white;
        }

        footer {
            background-color: #f8fafc;
            padding: 2rem 0;
            margin-top: 4rem;
        }

        .footer-content {
            color: #475569;
        }
        

/* General Services Section Styling */
.services-section {
    margin-top: 60px;
    margin-bottom: 60px;
    padding: 20px;
    background-color: #fafafa; /* Optional: Add a light background for separation */
    text-align: center;
}

.services-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.services-title {
    font-size: 2.5rem;
    margin-bottom: 15px;
    font-weight: 600;
    color: #599ee9;
}

.services-description {
    font-size: 1.2rem;
    margin-bottom: 40px;
    color: #333;
}

/* Services List Styling */
.services-list {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 20px; /* Space between cards */
    max-width: 1200px;
    width: 100%;
}

.service-card {
    width: 300px;
    padding: 20px;
    border-radius: 10px;
    background: #fff;
    box-shadow: 0px 15px 25px rgba(0, 0, 0, 0.2);
    text-align: center;
    transition: transform 0.3s ease, background 0.3s ease;
}

.service-card i {
    margin: 20px 0;
    color: #ff5724;
    font-size: 38px;
}

.service-title {
    margin-bottom: 12px;
    font-weight: 600;
    color: #333;
}

.service-description {
    color: #6c757d;
}

.service-card:hover {
    transform: translateY(-5px);
    background: linear-gradient(45deg, rgba(255, 28, 8, 0.7), rgba(255, 0, 82, 0.7));
    color: #fff;
}

.service-card:hover i,
.service-card:hover h2,
.service-card:hover p {
    color: #fff;
}


   
    </style>
</head>
<body>
    <!-- Navigation Bar -->
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

    <!-- Page Header -->
    <header class="page-header">
        <div class="container">
            <h1 class="page-title">Our Products</h1>
            <p class="text-secondary">Discover our wide range of premium tech products and accessories.</p>
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
        <div class="row">
            <?php foreach ($products as $product): ?>
                <div class="col-md-3">
                    <div class="product-card card">
                        <img src="<?php echo htmlspecialchars($product['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <p class="product-price">$<?php echo number_format($product['price'], 2); ?></p>
                            <a href="productdetail.php?id=<?php echo $product['id']; ?>" class="btn btn-primary btn-view-product">View Details</a>
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
        <div class="services-section">
    <div class="services-container">
        <h1 class="services-title">Our Services</h1>
        <p class="services-description">
            Our company offers a variety of services to meet your needs. Here are some of the key services we provide:
        </p>

        <div class="services-list">
            <div class="service-card">
                <i class='bx bxs-purchase-tag-alt'></i>
                <h2 class="service-title">Selling Product</h2>
                <p class="service-description">
                    We offer the latest technology products to meet your requirements.
                </p>
            </div>
            <div class="service-card">
                <i class='bx bx-wrench'></i>
                <h2 class="service-title">Repair</h2>
                <p class="service-description">
                    If you have issues with your tech devices, we provide repair services.
                </p>
            </div>
            <div class="service-card">
                <i class='bx bxs-devices'></i>
                <h2 class="service-title">Swap</h2>
                <p class="service-description">
                    We provide a 1-2 year warranty on products and service them free of charge if needed.
                </p>
            </div>
        </div> <!-- Close services-list -->
    </div> <!-- Close services-container -->
</div> <!-- Close services-section -->


    </main>


    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5 class="mb-3">About TechAI</h5>
                    <p class="footer-content">Your trusted destination for premium tech products and accessories.</p>
                </div>
                <div class="col-md-4">
                    <h5 class="mb-3">Quick Links</h5>
                    <ul class="list-unstyled footer-content">
                        <li><a href="products.php" class="text-decoration-none text-secondary">Products</a></li>
                        <li><a href="#" class="text-decoration-none text-secondary">About Us</a></li>
                        <li><a href="#" class="text-decoration-none text-secondary">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5 class="mb-3">Connect With Us</h5>
                    <div class="footer-content">
                        <a href="https://www.facebook.com" class="text-secondary me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://www.x.com" class="text-secondary me-3"><i class="fab fa-twitter"></i></a>
                        <a href="https://www.instagram.com" class="text-secondary me-3"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <div class="text-center mt-4">
                <p class="footer-content mb-0">&copy; <?php echo date("Y"); ?> TechAI. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

 <!-- Tawk.to Chatbot Integration -->
    <!--Start of Tawk.to Script-->
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
    <!--End of Tawk.to Script-->

   
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // JavaScript for handling filter changes
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

            window.location.search = params.toString();
        }
    </script>
</body>
</html>