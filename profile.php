<?php
session_start();
include 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['is_logged_in']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

// Fetch user details
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $user['password'];

    // Check if the password fields are set and match for updating password
    if (!empty($_POST['password']) && $_POST['password'] === $_POST['confirm_password']) {
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $updateMessage = "Profile and password updated successfully.";
    } elseif (!empty($_POST['password'])) {
        $errorMessage = "Passwords do not match. Please try again.";
    } else {
        $updateMessage = "Profile updated successfully.";
    }

    // Update user information if there's no error
    if (!isset($errorMessage)) {
        $updateStmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?");
        $updateStmt->execute([$name, $email, $password, $userId]);
        $_SESSION['username'] = $name; // Update session username
        $user['username'] = $name;
        $user['email'] = $email;
    }
}

// Fetch user orders with pagination
$ordersPerPage = 5;
$totalOrdersStmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
$totalOrdersStmt->execute([$userId]);
$totalOrders = $totalOrdersStmt->fetchColumn();
$totalPages = ceil($totalOrders / $ordersPerPage);
$page = isset($_GET['page']) ? max(1, min($totalPages, (int)$_GET['page'])) : 1;
$offset = ($page - 1) * $ordersPerPage;

$orderStmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC LIMIT ? OFFSET ?");
$orderStmt->bindParam(1, $userId, PDO::PARAM_INT);
$orderStmt->bindParam(2, $ordersPerPage, PDO::PARAM_INT);
$orderStmt->bindParam(3, $offset, PDO::PARAM_INT);
$orderStmt->execute();
$orders = $orderStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch wishlist items
$wishlistStmt = $pdo->prepare("
    SELECT p.* FROM products p 
    JOIN wishlist w ON p.id = w.product_id 
    WHERE w.user_id = ?
");
$wishlistStmt->execute([$userId]);
$wishlistItems = $wishlistStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile | Your Store Name</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --background-color: #ecf0f1;
            --text-color: #2c3e50;
            --border-radius: 12px;
            --box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        .profile-container {
            max-width: 1400px;
            margin: 3rem auto;
            padding: 0 2rem;
        }

        .profile-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 3rem 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 2rem;
        }

        .profile-nav {
            background: white;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
            box-shadow: var(--box-shadow);
        }

        .nav-pills .nav-link {
            color: var(--text-color);
            padding: 1rem 2rem;
            margin: 0 0.5rem;
            border-radius: var(--border-radius);
            transition: var(--transition);
            font-weight: 500;
        }

        .nav-pills .nav-link:hover {
            background-color: rgba(52, 152, 219, 0.1);
        }

        .nav-pills .nav-link.active {
            background-color: var(--secondary-color);
            color: white;
        }

        .content-section {
            background: white;
            padding: 2.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 2rem;
        }

        .form-control {
            border-radius: var(--border-radius);
            padding: 1rem;
            border: 2px solid #e0e0e0;
            transition: var(--transition);
        }

        .form-control:focus {
            box-shadow: none;
            border-color: var(--secondary-color);
        }

        .btn {
            padding: 0.8rem 2rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            transition: var(--transition);
        }

        .btn-primary {
            background-color: var(--secondary-color);
            border: none;
        }

        .btn-primary:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }

        .order-card {
            border: none;
            background: #f8f9fa;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
            padding: 1.5rem;
            transition: var(--transition);
        }

        .order-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--box-shadow);
        }

        .wishlist-item {
            background: #f8f9fa;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: var(--transition);
        }

        .wishlist-item:hover {
            transform: translateY(-3px);
            box-shadow: var(--box-shadow);
        }

        .alert {
            border-radius: var(--border-radius);
            padding: 1.2rem;
            margin-bottom: 2rem;
            border: none;
        }

        .badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
        }

        .pagination .page-link {
            border-radius: var(--border-radius);
            margin: 0 0.3rem;
            color: var(--secondary-color);
            border: none;
            padding: 0.8rem 1.2rem;
        }

        .pagination .page-item.active .page-link {
            background-color: var(--secondary-color);
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--secondary-color);
            margin-bottom: 1.5rem;
        }

        @media (max-width: 768px) {
            .profile-container {
                padding: 0 1rem;
                margin: 1.5rem auto;
            }

            .profile-header {
                padding: 2rem 1.5rem;
            }

            .nav-pills .nav-link {
                padding: 0.8rem 1.2rem;
                margin: 0.3rem;
            }
            
            .content-section {
                padding: 1.5rem;
            }

            .order-card, .wishlist-item {
                padding: 1rem;
            }
        }

        /* Animation effects */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .tab-pane {
            animation: fadeIn 0.5s ease-out;
        }
    </style>
</head>
<body>

<div class="profile-container">
    <!-- Profile Header -->
    <div class="profile-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="display-4 mb-2">Welcome back, <?php echo htmlspecialchars($user['username']); ?>!</h1>
                <p class="lead mb-0">Member since <?php echo date('F Y', strtotime($user['created_at'])); ?></p>
            </div>
            <div class="col-md-4 text-md-right">
                <a href="logout.php" class="btn btn-light btn-lg">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>

    <!-- Navigation Pills -->
    <div class="profile-nav">
        <ul class="nav nav-pills nav-justified" id="profileTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="profile-tab" data-toggle="pill" href="#profile" role="tab">
                    <i class="fas fa-user-circle"></i> Profile
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="orders-tab" data-toggle="pill" href="#orders" role="tab">
                    <i class="fas fa-shopping-bag"></i> Orders
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="wishlist-tab" data-toggle="pill" href="#wishlist" role="tab">
                    <i class="fas fa-heart"></i> Wishlist
                </a>
            </li>
        </ul>
    </div>

    <!-- Tab Content -->
    <div class="tab-content" id="profileTabsContent">
        <!-- Profile Tab -->
        <div class="tab-pane fade show active" id="profile" role="tabpanel">
            <div class="content-section">
                <?php if (isset($updateMessage)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $updateMessage; ?>
                    </div>
                <?php elseif (isset($errorMessage)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $errorMessage; ?>
                    </div>
                <?php endif; ?>

                <form action="profile.php" method="POST" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-user"></i> Username</label>
                                <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-envelope"></i> Email</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-lock"></i> New Password</label>
                                <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current password">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-lock"></i> Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" placeholder="Confirm new password">
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <a href="index.php" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left"></i> Back to Shop
                        </a>
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Orders Tab -->
        <div class="tab-pane fade" id="orders" role="tabpanel">
            <div class="content-section">
                <h3 class="mb-4">Order History</h3>
                <?php if (count($orders) > 0): ?>
                    <?php foreach ($orders as $order): ?>
                        <div class="order-card">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <h5 class="mb-0">Order #<?php echo $order['id']; ?></h5>
                                </div>
                                <div class="col-md-3">
                                    <span class="text-muted">Total Amount:</span>
                                    <strong class="ml-2">$<?php echo number_format($order['total_amount'], 2); ?></strong>
                                </div>
                                <div class="col-md-3">
                                    <span class="badge badge-<?php echo $order['status'] === 'completed' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst(htmlspecialchars($order['status'])); ?>
                                    </span>
                                </div>
                                <div class="col-md-3 text-right">
                                    <a href="order_details.php?order_id=<?php echo $order['id']; ?>" class="btn btn-outline-primary">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <!-- Pagination -->
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>#orders"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-shopping-bag"></i>
                        <h4>No Orders Yet</h4>
                        <p class="text-muted">Start shopping to see your orders here</p>
                        <a href="index.php" class="btn btn-primary mt-3">Start Shopping</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Wishlist Tab -->
        <div class="tab-pane fade" id="wishlist" role="tabpanel">
            <div class="content-section">
                <h3 class="mb-4">My Wishlist</h3>
                <?php if (!empty($wishlistItems)): ?>
                    <?php foreach ($wishlistItems as $item): ?>
                        <div class="wishlist-item">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h5 class="mb-0">
                                        <a href="productdetail.php?id=<?php echo $item['id']; ?>" class="text-dark">
                                            <?php echo htmlspecialchars($item['name']); ?>
                                        </a>
                                    </h5>
                                </div>
                                <div class="col-md-3">
                                    <strong class="text-primary">$<?php echo number_format($item['price'], 2); ?></strong>
                                </div>
                                <div class="col-md-3 text-right">
                                    <form action="remove_from_wishlist.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                        <button type="submit" class="btn btn-outline-danger">
                                            <i class="fas fa-trash"></i> Remove
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-heart"></i>
                        <h4>Your Wishlist is Empty</h4>
                        <p class="text-muted">Save items you love to your wishlist</p>
                        <a href="index.php" class="btn btn-primary mt-3">Browse Products</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function() {
        // Show active tab based on URL hash
        let hash = window.location.hash;
        if (hash) {
            $('#profileTabs a[href="' + hash + '"]').tab('show');
        }

        // Update URL hash when tab is changed
        $('#profileTabs a').on('click', function (e) {
            e.preventDefault();
            $(this).tab('show');
            window.location.hash = $(this).attr('href');
        });

        // Password confirmation validation
        $('form').on('submit', function(e) {
            var password = $('input[name="password"]').val();
            var confirmPassword = $('input[name="confirm_password"]').val();
            
            if (password && password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match. Please try again.');
            }
        });

        // Form validation
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
    });
</script>
</body>
</html>
