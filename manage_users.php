<?php
session_start();
include 'config.php';

// Check if the user is an admin
if (!isset($_SESSION['is_logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Handle delete user request
if (isset($_POST['delete_user'])) {
    $userId = $_POST['user_id'];
    $deleteStmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $deleteStmt->execute([$userId]);
    header("Location: manage_users.php");
    exit;
}

// Handle update user role request
if (isset($_POST['update_role'])) {
    $userId = $_POST['user_id'];
    $newRole = $_POST['role'];
    $updateStmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
    $updateStmt->execute([$newRole, $userId]);
    header("Location: manage_users.php");
    exit;
}

// Pagination setup
$usersPerPage = 10; // Number of users per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1); // Ensure the page number is at least 1
$offset = ($page - 1) * $usersPerPage;

// Fetch users with pagination
$stmt = $pdo->prepare("SELECT id, username, email, role FROM users ORDER BY id DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $usersPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get the total number of users for pagination calculation
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalPages = ceil($totalUsers / $usersPerPage);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <title>User Management Dashboard</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4a90e2;
            --danger-color: #dc3545;
            --success-color: #28a745;
        }

        body {
            background-color: #f5f7fa;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }

        .table {
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .table td {
            vertical-align: middle;
            font-size: 0.95rem;
        }

        .role-badge {
            padding: 0.35rem 0.75rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .role-badge.admin {
            background-color: #ffe5e5;
            color: #dc3545;
        }

        .role-badge.user {
            background-color: #e3f2fd;
            color: #0d6efd;
        }

        .btn-action {
            padding: 0.375rem 0.75rem;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .btn-delete {
            background-color: #fff;
            border: 1px solid var(--danger-color);
            color: var(--danger-color);
        }

        .btn-delete:hover {
            background-color: var(--danger-color);
            color: white;
        }

        .role-select {
            border: 1px solid #ced4da;
            border-radius: 6px;
            padding: 0.375rem 2rem 0.375rem 0.75rem;
            font-size: 0.95rem;
            background-position: right 0.5rem center;
        }

        .pagination {
            margin-top: 2rem;
        }

        .page-link {
            padding: 0.5rem 1rem;
            color: var(--primary-color);
            border: 1px solid #dee2e6;
        }

        .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #6c757d;
            text-decoration: none;
            transition: color 0.2s;
        }

        .back-button:hover {
            color: #495057;
        }

        @media (max-width: 768px) {
            .dashboard-container {
                padding: 1rem;
                margin: 1rem;
            }

            .table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <div class="page-header">
        <h2 class="h4 mb-0">User Management</h2>
        <a href="admin_dashboard.php" class="back-button">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Dashboard</span>
        </a>
    </div>

    <?php if (count($users) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">User</th>
                        <th>Email</th>
                        <th class="text-center">Role</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                    </div>
                                    <div class="ms-3">
                                        <div class="fw-medium"><?php echo htmlspecialchars($user['username']); ?></div>
                                        <div class="text-muted small">ID: <?php echo $user['id']; ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="align-middle"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td class="text-center align-middle">
                                <form method="POST" action="manage_users.php" class="d-inline-block">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <select name="role" class="role-select form-select form-select-sm" onchange="this.form.submit()" style="width: auto;">
                                        <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                                        <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                    </select>
                                    <input type="hidden" name="update_role" value="1">
                                </form>
                            </td>
                            <td class="text-end pe-3">
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <form method="POST" action="manage_users.php" class="d-inline-block">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" name="delete_user" class="btn btn-action btn-delete" 
                                                onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted">Current User</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-users fa-3x mb-3"></i>
            <h3>No Users Found</h3>
            <p class="text-muted">There are currently no users in the system.</p>
        </div>
    <?php endif; ?>

    <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                <?php endif; ?>

                <?php
                $start = max(1, $page - 2);
                $end = min($totalPages, $start + 4);
                if ($end - $start < 4) {
                    $start = max(1, $end - 4);
                }
                ?>

                <?php for ($i = $start; $i <= $end; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add smooth transitions for role changes
    const roleSelects = document.querySelectorAll('.role-select');
    roleSelects.forEach(select => {
        select.addEventListener('change', function() {
            this.style.opacity = '0.5';
            this.closest('form').submit();
        });
    });

    // Enhance delete confirmation
    const deleteForms = document.querySelectorAll('form[name="delete_user"]');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const username = this.closest('tr').querySelector('.fw-medium').textContent;
            return confirm(`Are you sure you want to delete the user "${username}"? This action cannot be undone.`);
        });
    });
});
</script>
</body>
</html>