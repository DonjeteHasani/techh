<?php
include 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->rowCount() > 0) {
            $error = "Username or email already exists.";
        } else {
            // Store password as plain text (for testing purposes only)
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            if ($stmt->execute([$username, $email, $password])) {
                header("Location: login.php");
                exit;
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>

    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Create Account</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4A90E2;
            --error-color: #e74c3c;
            --success-color: #2ecc71;
            --background-color: #f5f6fa;
            --box-shadow: 0 8px 24px rgba(149, 157, 165, 0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }

        body {
            background-color: var(--background-color);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .register-container {
            background: white;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: var(--box-shadow);
            width: 100%;
            max-width: 450px;
        }

        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .register-header h1 {
            color: #2c3e50;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .register-header p {
            color: #7f8c8d;
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2c3e50;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .input-group {
            position: relative;
        }

        .input-group input {
            width: 100%;
            padding: 0.8rem 1rem;
            padding-left: 2.5rem;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .input-group i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #95a5a6;
        }

        .input-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
        }

        .password-requirements {
            margin-top: 0.5rem;
            font-size: 0.8rem;
            color: #7f8c8d;
        }

        .password-requirements ul {
            list-style: none;
            padding-left: 0.5rem;
        }

        .password-requirements li {
            margin-top: 0.25rem;
        }

        .password-requirements i {
            margin-right: 0.5rem;
            font-size: 0.7rem;
        }

        .alert {
            padding: 0.8rem 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            background-color: #fde8e8;
            color: var(--error-color);
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .register-button {
            width: 100%;
            padding: 0.8rem;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .register-button:hover {
            background-color: #357abd;
        }

        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .login-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .register-container {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h1>Create Account</h1>
            <p>Please fill in your information to get started</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST" id="registerForm">
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
                        placeholder="Choose a username"
                        minlength="3"
                        maxlength="30"
                        pattern="^[a-zA-Z0-9_-]+$"
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required 
                        autocomplete="email"
                        placeholder="Enter your email address"
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
                        autocomplete="new-password"
                        placeholder="Create a strong password"
                        minlength="8"
                    >
                </div>
                <div class="password-requirements">
                    <p>Password must contain:</p>
                    <ul>
                        <li><i class="fas fa-circle"></i>At least 8 characters</li>
                        <li><i class="fas fa-circle"></i>Letters and numbers</li>
                        <li><i class="fas fa-circle"></i>At least one special character</li>
                    </ul>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        required 
                        autocomplete="new-password"
                        placeholder="Confirm your password"
                    >
                </div>
            </div>

            <button type="submit" class="register-button">
                Create Account
            </button>

            <div class="login-link">
                Already have an account? <a href="login.php">Log in here</a>
            </div>
        </form>
    </div>

    <script>
        // Simple client-side validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            // Check if passwords match
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return;
            }

            // Check password strength
            const hasLetter = /[a-zA-Z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);
            
            if (!hasLetter || !hasNumber || !hasSpecial || password.length < 8) {
                e.preventDefault();
                alert('Password does not meet the requirements!');
                return;
            }
        });
    </script>
</body>
</html>