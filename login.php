<?php
include 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Directly compare plain-text password (for testing purposes only)
    if ($user && $password === $user['password']) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['is_logged_in'] = true;

        header("Location: " . ($user['role'] === 'admin' ? "admin_dashboard.php" : "index.php"));
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Welcome Back</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
            --primary-color: #4361ee;
            --error-color: #ef233c;
            --success-color: #2ecc71;
            --background-color: #f8f9fa;
            --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --text-primary: #2b2d42;
            --text-secondary: #6c757d;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: white;
            padding: 3rem;
            border-radius: 16px;
            box-shadow: var(--box-shadow);
            width: 100%;
            max-width: 450px;
            margin: 1rem;
            transition: transform 0.3s ease;
        }

        .login-container:hover {
            transform: translateY(-5px);
        }

        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .login-header h1 {
            color: var(--text-primary);
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
        }

        .login-header p {
            color: var(--text-secondary);
            font-size: 1rem;
        }

        .form-group {
            margin-bottom: 1.75rem;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.75rem;
            color: var(--text-primary);
            font-weight: 600;
            font-size: 0.95rem;
        }

        .input-group {
            position: relative;
            transition: all 0.3s ease;
        }

        .input-group input {
            width: 100%;
            padding: 1rem 1.25rem;
            padding-left: 3rem;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: #f8f9fa;
        }

        .input-group i {
            position: absolute;
            left: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .input-group:focus-within i {
            color: var(--primary-color);
        }

        .input-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            background-color: white;
            box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.1);
        }

        .alert {
            padding: 1rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            background-color: #fff5f5;
            border: 1px solid #fed7d7;
            color: var(--error-color);
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .login-button {
            width: 100%;
            padding: 1rem;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .login-button:hover {
            background-color: #3651d4;
            transform: translateY(-2px);
        }

        .login-button:active {
            transform: translateY(0);
        }

        .signup-link {
            text-align: center;
            margin-top: 2rem;
            color: var(--text-secondary);
            font-size: 0.95rem;
        }

        .signup-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            margin-left: 0.5rem;
            transition: all 0.3s ease;
        }

        .signup-link a:hover {
            color: #3651d4;
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 2rem;
                margin: 1rem;
            }

            .login-header h1 {
                font-size: 1.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Welcome Back</h1>
            <p>Please enter your credentials to continue</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        required 
                        autocomplete="username"
                        placeholder="Enter your username"
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        autocomplete="current-password"
                        placeholder="Enter your password"
                    >
                </div>
            </div>

            <button type="submit" class="login-button">
                Log In
            </button>

            <div class="signup-link">
                Don't have an account?<a href="register.php">Sign up here</a>
            </div>
        </form>
    </div>
</body>
</html>

