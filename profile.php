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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #f8f9fa;
            --border-radius: 10px;
            --box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        body {
            background-color: #f4f6f8;
            color: #333;
        }

        .profile-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .profile-header {
            background: white;
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 2rem;
        }

        .profile-nav {
            background: white;
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
            box-shadow: var(--box-shadow);
        }

        .nav-pills .nav-link {
            color: #666;
            padding: 0.8rem 1.5rem;
            margin: 0 0.5rem;
            border-radius: var(--border-radius);
        }

        .nav-pills .nav-link.active {
            background-color: var(--primary-color);
            color: white;
        }

        .content-section {
            background: white;
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 2rem;
        }

        .form-control {
            border-radius: var(--border-radius);
            padding: 0.8rem;
            border: 1px solid #ddd;
        }

        .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(74, 144, 226, 0.25);
            border-color: var(--primary-color);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 0.8rem 2rem;
            border-radius: var(--border-radius);
            font-weight: 600;
        }

        .order-card {
            border: 1px solid #eee;
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
            transition: transform 0.2s;
        }

        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--box-shadow);
        }

        .wishlist-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border: 1px solid #eee;
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
        }

        .alert {
            border-radius: var(--border-radius);
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 768px) {
            .nav-pills .nav-link {
                padding: 0.5rem 1rem;
                margin: 0.25rem;
            }
            
            .content-section {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>

<div class="profile-container">
    <!-- Profile Header -->
    <div class="profile-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="mb-0">Welcome, <?php echo htmlspecialchars($user['username']); ?></h1>
                <p class="text-muted mb-0">Member since <?php echo date('F Y', strtotime($user['created_at'])); ?></p>
            </div>
            <div class="col-md-4 text-md-right">
                <a href="logout.php" class="btn btn-outline-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>

    <!-- Navigation Pills -->
    <div class="profile-nav">
        <ul class="nav nav-pills" id="profileTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="profile-tab" data-toggle="pill" href="#profile" role="tab">
                    <i class="fas fa-user"></i> Profile
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

                <form action="profile.php" method="POST">
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
                    <div class="text-right">
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="tab-pane fade" id="orders" role="tabpanel">
            <div class="content-section">
                <h3 class="mb-4">Order History</h3>
                <?php if (count($orders) > 0): ?>
                    <?php foreach ($orders as $order): ?>
                        <div class="order-card p-3">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <strong>Order #<?php echo $order['id']; ?></strong>
                                </div>
                                <div class="col-md-3">
                                    <span class="text-muted">Amount:</span>
                                    <strong>$<?php echo number_format($order['total_amount'], 2); ?></strong>
                                </div>
                                <div class="col-md-3">
                                    <span class="badge badge-<?php echo $order['status'] === 'completed' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst(htmlspecialchars($order['status'])); ?>
                                    </span>
                                </div>
                                <div class="col-md-3 text-right">
                                    <a href="order_details.php?order_id=<?php echo $order['id']; ?>" class="btn btn-info btn-sm">
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
                    <div class="text-center py-5">
                        <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                        <p>You haven't placed any orders yet.</p>
                        <a href="index.php" class="btn btn-primary">Start Shopping</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Modified Wishlist Tab -->
        <div class="tab-pane fade" id="wishlist" role="tabpanel">
            <div class="content-section">
                <h3 class="mb-4">My Wishlist</h3>
                <?php if (!empty($wishlistItems)): ?>
                    <?php foreach ($wishlistItems as $item): ?>
                        <div class="wishlist-item">
                            <div class="row align-items-center w-100">
                                <div class="col-md-6">
                                    <h5 class="mb-0">
                                        <a href="productdetail.php?id=<?php echo $item['id']; ?>" class="text-dark">
                                            <?php echo htmlspecialchars($item['name']); ?>
                                        </a>
                                    </h5>
                                </div>
                                <div class="col-md-3">
                                    <strong>$<?php echo number_format($item['price'], 2); ?></strong>
                                </div>
                                <div class="col-md-3 text-right">
                                    <form action="remove_from_wishlist.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            <i class="fas fa-trash"></i> Remove
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-heart fa-3x text-muted mb-3"></i>
                        <p>Your wishlist is empty.</p>
                        <a href="index.php" class="btn btn-primary">Browse Products</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modified JavaScript -->
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
    });
</script>
</body>
</html>