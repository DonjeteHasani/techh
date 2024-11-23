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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4f46e5;
            --danger-color: #ef4444;
            --success-color: #10b981;
            --warning-color: #f59e0b;
        }

        body {
            background-color: #f8fafc;
            font-family: 'Inter', sans-serif;
        }

        .dashboard-container {
            max-width: 1400px;
            margin: 3rem auto;
            padding: 2.5rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .page-header h2 {
            font-size: 1.875rem;
            font-weight: 700;
            color: #1e293b;
        }

        .back-button {
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            border: 2px solid #e2e8f0;
            font-weight: 600;
        }

        .back-button:hover {
            background-color: #f1f5f9;
            transform: translateY(-2px);
        }

        .coupon-form {
            background: #f8fafc;
            padding: 2rem;
            border-radius: 16px;
            margin-bottom: 3rem;
            border: 2px solid #e2e8f0;
        }

        .form-control {
            border-radius: 12px;
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #4338ca;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .table {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            border: 2px solid #e2e8f0;
        }

        .table th {
            background-color: #f8fafc;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.875rem;
            letter-spacing: 0.05em;
            padding: 1.25rem;
            color: #475569;
        }

        .table td {
            padding: 1.25rem;
            vertical-align: middle;
            border-bottom: 1px solid #e2e8f0;
        }

        .coupon-code {
            font-family: 'JetBrains Mono', monospace;
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--primary-color);
            background: #f1f5f9;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            letter-spacing: 0.05em;
        }

        .discount-badge {
            background-color: #ecfdf5;
            color: var(--success-color);
            padding: 0.5rem 1rem;
            border-radius: 999px;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .expiry-date {
            color: #64748b;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .badge {
            padding: 0.5rem 1rem;
            border-radius: 999px;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .badge.bg-danger {
            background-color: #fef2f2 !important;
            color: var(--danger-color);
        }

        .badge.bg-warning {
            background-color: #fffbeb !important;
            color: var(--warning-color);
        }

        .badge.bg-success {
            background-color: #ecfdf5 !important;
            color: var(--success-color);
        }

        .btn-delete {
            color: var(--danger-color);
            background-color: #fff;
            border: 2px solid var(--danger-color);
            padding: 0.5rem 1rem;
            border-radius: 10px;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .btn-delete:hover {
            background-color: var(--danger-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.2);
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #64748b;
            background: #f8fafc;
            border-radius: 16px;
            border: 2px dashed #e2e8f0;
        }

        .empty-state i {
            color: #94a3b8;
            margin-bottom: 1.5rem;
        }

        .empty-state h3 {
            font-weight: 600;
            margin-bottom: 1rem;
            color: #1e293b;
        }

        @media (max-width: 768px) {
            .dashboard-container {
                margin: 1rem;
                padding: 1.5rem;
            }

            .page-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }

            .coupon-form {
                padding: 1.5rem;
            }

            .form-row > div {
                margin-bottom: 1.5rem;
            }

            .table td, .table th {
                padding: 1rem;
            }

            .coupon-code {
                font-size: 1rem;
                padding: 0.375rem 0.75rem;
            }
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .dashboard-container {
            animation: fadeIn 0.5s ease-out;
        }

        .table tr {
            animation: fadeIn 0.3s ease-out;
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <div class="page-header">
        <h2>Coupon Management</h2>
        <a href="admin_dashboard.php" class="btn back-button">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>

    <!-- Add Coupon Form -->
    <div class="coupon-form">
        <h5 class="mb-4 fw-bold">Create New Coupon</h5>
        <form action="manage_coupons.php" method="POST">
            <div class="row g-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Coupon Code</label>
                    <input type="text" name="code" class="form-control" placeholder="e.g., SUMMER2024" 
                           maxlength="20" required pattern="[A-Za-z0-9]+" 
                           title="Only letters and numbers allowed"
                           oninput="this.value = this.value.toUpperCase()">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Discount Percentage</label>
                    <div class="input-group">
                        <input type="number" name="discount" class="form-control" 
                               placeholder="Enter value" min="0" max="100" required>
                        <span class="input-group-text">%</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Expiration Date</label>
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
                                    <span class="badge bg-warning">Expires Soon</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Active</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <form method="POST" action="manage_coupons.php" class="d-inline-block">
                                    <input type="hidden" name="coupon_id" value="<?php echo $coupon['id']; ?>">
                                    <button type="submit" name="delete_coupon" class="btn btn-delete">
                                        <i class="fas fa-trash-alt me-1"></i>Delete
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
            <i class="fas fa-ticket-alt fa-3x"></i>
            <h3>No Coupons Found</h3>
            <p class="text-muted mb-0">Create your first coupon using the form above.</p>
        </div>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enhanced delete confirmation with custom modal
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

    // Enhanced input validation for coupon code
    const codeInput = document.querySelector('input[name="code"]');
    if (codeInput) {
        codeInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^A-Za-z0-9]/g, '');
        });
    }

    // Enhanced date input with validation
    const dateInput = document.querySelector('input[name="expiration_date"]');
    if (dateInput) {
        const today = new Date().toISOString().split('T')[0];
        dateInput.setAttribute('min', today);
    }

    // Add hover effect to table rows
    const tableRows = document.querySelectorAll('tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f8fafc';
            this.style.transition = 'background-color 0.3s ease';
        });
        row.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });
});
</script>
</body>
</html>
