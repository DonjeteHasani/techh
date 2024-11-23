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
$usersPerPage = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $usersPerPage;

// Fetch users with pagination
$stmt = $pdo->prepare("SELECT id, username, email, role FROM users ORDER BY id DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $usersPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total users for pagination
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;
            --danger-color: #dc2626;
            --success-color: #16a34a;
            --background-color: #f1f5f9;
            --card-background: #ffffff;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --border-color: #e2e8f0;
        }

        body {
            background-color: var(--background-color);
            color: var(--text-primary);
            font-family: 'Inter', sans-serif;
        }

        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-color), #1d4ed8);
            color: white;
            padding: 2.5rem 0;
            margin-bottom: 2rem;
        }

        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .card {
            background: var(--card-background);
            border: none;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            margin-bottom: 2rem;
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            background-color: #f8fafc;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            padding: 1rem;
            border-bottom: 2px solid var(--border-color);
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
        }

        .avatar-circle {
            width: 40px;
            height: 40px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1rem;
        }

        .role-select {
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            padding: 0.5rem 2rem 0.5rem 1rem;
            font-size: 0.875rem;
            background-color: #f8fafc;
            transition: all 0.2s;
        }

        .role-select:hover {
            border-color: var(--primary-color);
        }

        .btn-action {
            padding: 0.5rem;
            border-radius: 0.5rem;
            transition: all 0.2s;
        }

        .btn-delete {
            color: var(--danger-color);
            background-color: #fee2e2;
            border: none;
        }

        .btn-delete:hover {
            background-color: var(--danger-color);
            color: white;
        }

        .pagination {
            gap: 0.25rem;
        }

        .page-link {
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            margin: 0 0.25rem;
        }

        .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .empty-state {
            padding: 4rem 2rem;
            text-align: center;
            color: var(--text-secondary);
        }

        .empty-state i {
            font-size: 3rem;
            color: var(--text-secondary);
            margin-bottom: 1rem;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            background-color: rgba(255, 255, 255, 0.1);
            transition: all 0.2s;
        }

        .back-button:hover {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
        }

        @media (max-width: 768px) {
            .dashboard-header {
                padding: 1.5rem 0;
            }

            .dashboard-container {
                padding: 0 1rem;
            }

            .card {
                border-radius: 0.75rem;
            }

            .table td, .table th {
                padding: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <div class="dashboard-container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-2">User Management</h1>
                    <p class="mb-0 opacity-75">Manage and monitor user accounts</p>
                </div>
                <a href="admin_dashboard.php" class="back-button">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Dashboard</span>
                </a>
            </div>
        </div>
    </div>

    <div class="dashboard-container">
        <?php if (count($users) > 0): ?>
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th class="text-center">Role</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle">
                                                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                            </div>
                                            <div class="ms-3">
                                                <div class="fw-medium"><?php echo htmlspecialchars($user['username']); ?></div>
                                                <div class="text-muted small">ID: <?php echo $user['id']; ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td class="text-center">
                                        <form method="POST" action="manage_users.php" class="d-inline-block">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <select name="role" class="role-select" onchange="this.form.submit()">
                                                <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                                                <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                            </select>
                                            <input type="hidden" name="update_role" value="1">
                                        </form>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <form method="POST" action="manage_users.php" class="d-inline-block">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" name="delete_user" class="btn btn-action btn-delete" 
                                                        onclick="return confirm('Are you sure you want to delete this user?');">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="badge bg-light text-secondary">Current User</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <h3>No Users Found</h3>
                    <p class="text-muted">There are currently no users in the system.</p>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($totalPages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>">
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
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>">
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
        // Enhanced role select animation
        const roleSelects = document.querySelectorAll('.role-select');
        roleSelects.forEach(select => {
            select.addEventListener('change', function() {
                this.style.opacity = '0.5';
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.closest('form').submit();
                }, 200);
            });
        });

        // Enhanced delete confirmation
        const deleteForms = document.querySelectorAll('form[name="delete_user"]');
        deleteForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const username = this.closest('tr').querySelector('.fw-medium').textContent;
                if (confirm(`Are you sure you want to delete the user "${username}"? This action cannot be undone.`)) {
                    this.submit();
                }
            });
        });
    });
    </script>
</body>
</html>
