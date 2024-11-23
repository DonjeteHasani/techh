<?php
session_start();
include 'config.php';

// Check if the user is an admin
if (!isset($_SESSION['is_logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Get product ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch all categories for the dropdown
$categoryStmt = $pdo->prepare("SELECT id, name FROM categories");
$categoryStmt->execute();
$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = trim($_POST['name']);
        $price = (float)$_POST['price'];
        $image = trim($_POST['image']);
        $description = trim($_POST['description']);
        $category_id = (int)$_POST['category_id'];

        $stmt = $pdo->prepare("UPDATE products SET name = ?, price = ?, image = ?, description = ?, category_id = ? WHERE id = ?");
        $result = $stmt->execute([$name, $price, $image, $description, $category_id, $id]);

        if ($result) {
            $_SESSION['success_message'] = "Product updated successfully!";
            header("Location: manage_products.php");
            exit;
        }
    } catch (PDOException $e) {
        $error_message = "Error updating product: " . $e->getMessage();
    }
}

// Fetch current product data
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

// If product doesn't exist, redirect back to manage products
if (!$product) {
    header("Location: manage_products.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - <?php echo htmlspecialchars($product['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --success-color: #4bb543;
            --danger-color: #dc3545;
            --warning-color: #ffa900;
        }

        body {
            background-color: #f8f9fa;
            color: #333;
            font-family: 'Inter', sans-serif;
        }

        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 15px 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
        }

        .form-control {
            border-radius: 8px;
            padding: 0.75rem;
            border: 1px solid #dee2e6;
        }

        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.25);
            border-color: var(--primary-color);
        }

        .btn {
            padding: 0.5rem 1.5rem;
            border-radius: 8px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border: none;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
        }

        .product-preview {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
            background-color: white;
        }

        .preview-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-bottom: 2px solid #f0f0f0;
        }

        .invalid-feedback {
            font-size: 0.875rem;
            color: var(--danger-color);
        }

        .alert {
            border-radius: 8px;
            border: none;
        }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <div class="container">
            <h1 class="mb-0">
                <i class="fas fa-edit me-2"></i>Edit Product
            </h1>
            <p class="mt-2 mb-0">
                Update details for: <?php echo htmlspecialchars($product['name']); ?>
            </p>
        </div>
    </div>

    <div class="container mb-5">
        <div class="row">
            <!-- Edit Form -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_message; ?>
                            </div>
                        <?php endif; ?>

                        <form action="edit_product.php?id=<?php echo $id; ?>" method="POST" id="editProductForm">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name" class="form-label">Product Name</label>
                                        <input type="text" 
                                               name="name" 
                                               class="form-control" 
                                               id="name" 
                                               value="<?php echo htmlspecialchars($product['name']); ?>" 
                                               required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="price" class="form-label">Price</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" 
                                                   step="0.01" 
                                                   name="price" 
                                                   class="form-control" 
                                                   id="price" 
                                                   value="<?php echo number_format($product['price'], 2); ?>" 
                                                   required>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                        <div class="form-group">
                           <label for="image" class="form-label">Image Path</label>
                           <input type="text" name="image" class="form-control" id="image" 
                                               placeholder="e.g., img/foto1.png" required>
                        </div>
                    </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="category_id" class="form-label">Category</label>
                                        <select name="category_id" class="form-control" id="category_id" required>
                                            <option value="">Select a Category</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?php echo $category['id']; ?>" 
                                                    <?php echo $category['id'] == $product['category_id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea name="description" 
                                                  class="form-control" 
                                                  id="description" 
                                                  rows="4" 
                                                  required><?php echo htmlspecialchars($product['description']); ?></textarea>
                                    </div>
                                </div>

                                <div class="col-12 mt-4">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Save Changes
                                        </button>
                                        <a href="manage_products.php" class="btn btn-secondary">
                                            <i class="fas fa-times me-2"></i>Cancel
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Preview Card -->
            <div class="col-lg-4">
                <div class="product-preview">
                    <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                         class="preview-image" 
                         id="imagePreview">
                    <div class="p-3">
                        <h5 class="mb-2" id="namePreview"><?php echo htmlspecialchars($product['name']); ?></h5>
                        <p class="text-primary mb-2" id="pricePreview">$<?php echo number_format($product['price'], 2); ?></p>
                        <p class="text-muted mb-0" id="descriptionPreview"><?php echo htmlspecialchars($product['description']); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Live preview updates
        document.getElementById('name').addEventListener('input', function(e) {
            document.getElementById('namePreview').textContent = e.target.value;
        });

        document.getElementById('price').addEventListener('input', function(e) {
            document.getElementById('pricePreview').textContent = '$' + parseFloat(e.target.value).toFixed(2);
        });

        document.getElementById('image').addEventListener('input', function(e) {
            document.getElementById('imagePreview').src = e.target.value;
        });

        document.getElementById('description').addEventListener('input', function(e) {
            document.getElementById('descriptionPreview').textContent = e.target.value;
        });

        // Form validation
        document.getElementById('editProductForm').addEventListener('submit', function(e) {
            const price = parseFloat(document.getElementById('price').value);
            if (price <= 0) {
                e.preventDefault();
                alert('Price must be greater than 0');
            }
        });

        // Handle image load errors
        document.getElementById('imagePreview').addEventListener('error', function() {
            this.src = 'placeholder-image.jpg'; // Replace with your placeholder image
        });
    </script>
</body>
</html>
