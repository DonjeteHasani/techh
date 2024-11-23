<?php
session_start();
include 'config.php';

// Check if the user is an admin
if (!isset($_SESSION['is_logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Add a new coupon
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_coupon'])) {
    $code = strtoupper(trim($_POST['code']));
    $discount = (int)$_POST['discount'];
    $expiration = $_POST['expiration_date'];

    $stmt = $pdo->prepare("INSERT INTO coupons (code, discount_percentage, expiration_date) VALUES (?, ?, ?)");
    $stmt->execute([$code, $discount, $expiration]);
    header("Location: manage_coupons.php");
    exit;
}

// Delete a coupon
if (isset($_POST['delete_coupon'])) {
    $couponId = $_POST['coupon_id'];
    $deleteStmt = $pdo->prepare("DELETE FROM coupons WHERE id = ?");
    $deleteStmt->execute([$couponId]);
    header("Location: manage_coupons.php");
    exit;
}

// Fetch all coupons
$couponsStmt = $pdo->query("SELECT * FROM coupons ORDER BY expiration_date ASC");
$coupons = $couponsStmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <title>Coupon Management Dashboard</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #6366f1;
            --danger-color: #dc3545;
            --success-color: #10b981;
        }

        body {
            background-color: #f3f4f6;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .coupon-form {
            background: #f9fafb;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            border: 1px solid #e5e7eb;
        }

        .form-control {
            border-radius: 8px;
            padding: 0.625rem 1rem;
            border: 1px solid #d1d5db;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 0.625rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-primary:hover {
            background-color: #4f46e5;
            transform: translateY(-1px);
        }

        .table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }

        .table th {
            background-color: #f9fafb;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            padding: 1rem;
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
        }

        .coupon-code {
            font-family: monospace;
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary-color);
            background: #f3f4f6;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
        }

        .discount-badge {
            background-color: #ecfdf5;
            color: var(--success-color);
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-weight: 500;
        }

        .expiry-date {
            color: #6b7280;
        }

        .expiry-warning {
            color: var(--danger-color);
            font-weight: 500;
        }

        .btn-delete {
            color: var(--danger-color);
            background-color: #fff;
            border: 1px solid var(--danger-color);
            padding: 0.375rem 0.75rem;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .btn-delete:hover {
            background-color: var(--danger-color);
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6b7280;
        }

        @media (max-width: 768px) {
            .dashboard-container {
                margin: 1rem;
                padding: 1rem;
            }

            .coupon-form {
                padding: 1rem;
            }

            .form-row > div {
                margin-bottom: 1rem;
            }
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <div class="page-header">
        <h2 class="h4 mb-0">Coupon Management</h2>
        <a href="admin_dashboard.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>

    <!-- Add Coupon Form -->
    <div class="coupon-form">
        <h5 class="mb-3">Create New Coupon</h5>
        <form action="manage_coupons.php" method="POST">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Coupon Code</label>
                    <input type="text" name="code" class="form-control" placeholder="e.g., SUMMER2024" 
                           maxlength="20" required pattern="[A-Za-z0-9]+" 
                           title="Only letters and numbers allowed"
                           oninput="this.value = this.value.toUpperCase()">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Discount Percentage</label>
                    <div class="input-group">
                        <input type="number" name="discount" class="form-control" 
                               placeholder="Enter value" min="0" max="100" required>
                        <span class="input-group-text">%</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Expiration Date</label>
                    <input type="date" name="expiration_date" class="form-control" 
                           required min="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" name="add_coupon" class="btn btn-primary w-100">
                        <i class="fas fa-plus me-2"></i>Add Coupon
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Display Coupons -->
    <?php if (count($coupons) > 0): ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Coupon Code</th>
                        <th>Discount</th>
                        <th>Expiration</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($coupons as $coupon): 
                        $expiry_date = strtotime($coupon['expiration_date']);
                        $is_expired = $expiry_date < time();
                        $expires_soon = $expiry_date < strtotime('+7 days');
                    ?>
                        <tr>
                            <td>
                                <span class="coupon-code"><?php echo htmlspecialchars($coupon['code']); ?></span>
                            </td>
                            <td>
                                <span class="discount-badge">
                                    <?php echo $coupon['discount_percentage']; ?>% OFF
                                </span>
                            </td>
                            <td>
                                <span class="expiry-date">
                                    <i class="far fa-calendar-alt me-2"></i>
                                    <?php echo date('M d, Y', strtotime($coupon['expiration_date'])); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($is_expired): ?>
                                    <span class="badge bg-danger">Expired</span>
                                <?php elseif ($expires_soon): ?>
                                    <span class="badge bg-warning text-dark">Expires Soon</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Active</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <form method="POST" action="manage_coupons.php" class="d-inline-block">
                                    <input type="hidden" name="coupon_id" value="<?php echo $coupon['id']; ?>">
                                    <button type="submit" name="delete_coupon" class="btn btn-delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-ticket-alt fa-3x mb-3"></i>
            <h3>No Coupons Found</h3>
            <p class="text-muted">Create your first coupon using the form above.</p>
        </div>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enhance delete confirmation
    const deleteForms = document.querySelectorAll('form');
    deleteForms.forEach(form => {
        if (form.querySelector('button[name="delete_coupon"]')) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const code = this.closest('tr').querySelector('.coupon-code').textContent;
                if (confirm(`Are you sure you want to delete the coupon "${code}"? This action cannot be undone.`)) {
                    this.submit();
                }
            });
        }
    });

    // Add input validation for coupon code
    const codeInput = document.querySelector('input[name="code"]');
    if (codeInput) {
        codeInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^A-Za-z0-9]/g, '');
        });
    }

    // Enhanced date input
    const dateInput = document.querySelector('input[name="expiration_date"]');
    if (dateInput) {
        const today = new Date().toISOString().split('T')[0];
        dateInput.setAttribute('min', today);
    }
});
</script>
</body>
</html>