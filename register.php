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
            padding: 2rem;
        }

        .register-container {
            background: white;
            padding: 3rem;
            border-radius: 16px;
            box-shadow: var(--box-shadow);
            width: 100%;
            max-width: 500px;
            transition: transform 0.3s ease;
        }

        .register-container:hover {
            transform: translateY(-5px);
        }

        .register-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .register-header h1 {
            color: var(--text-primary);
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
        }

        .register-header p {
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

        .password-requirements {
            margin-top: 1rem;
            padding: 1rem;
            background-color: #f8f9fa;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            display: none;
        }

        .password-requirements p {
            color: var(--text-primary);
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .password-requirements ul {
            list-style: none;
            padding-left: 0;
            margin-bottom: 0;
        }

        .password-requirements li {
            color: var(--text-secondary);
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .password-requirements li:last-child {
            margin-bottom: 0;
        }

        .password-requirements i {
            font-size: 0.7rem;
            color: var(--text-secondary);
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

        .register-button {
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

        .register-button:hover {
            background-color: #3651d4;
            transform: translateY(-2px);
        }

        .register-button:active {
            transform: translateY(0);
        }

        .login-link {
            text-align: center;
            margin-top: 2rem;
            color: var(--text-secondary);
            font-size: 0.95rem;
        }

        .login-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            margin-left: 0.5rem;
            transition: all 0.3s ease;
        }

        .login-link a:hover {
            color: #3651d4;
            text-decoration: underline;
        }

        @media (max-width: 576px) {
            body {
                padding: 1rem;
            }

            .register-container {
                padding: 2rem;
            }

            .register-header h1 {
                font-size: 1.75rem;
            }

            .form-group {
                margin-bottom: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h1>Create Account</h1>
            <p>Join us today! Please fill in your information</p>
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
                    <p>Password Requirements:</p>
                    <ul>
                        <li><i class="fas fa-check-circle"></i>Minimum 8 characters</li>
                        <li><i class="fas fa-check-circle"></i>Contains letters and numbers</li>
                        <li><i class="fas fa-check-circle"></i>At least one special character</li>
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
                Already have an account?<a href="login.php">Log in here</a>
            </div>
        </form>
    </div>

    <script>
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
                alert('Password must contain at least 8 characters, including letters, numbers, and special characters!');
                return;
            }
        });

        // Dynamic password requirements validation
        const passwordInput = document.getElementById('password');
        const requirements = document.querySelectorAll('.password-requirements li i');
        const passwordRequirements = document.querySelector('.password-requirements');

        passwordInput.addEventListener('focus', function() {
            passwordRequirements.style.display = 'block';
        });

        passwordInput.addEventListener('blur', function() {
            passwordRequirements.style.display = 'none';
        });

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            
            // Requirement 1: Minimum 8 characters
            requirements[0].className = password.length >= 8 ? 'fas fa-check-circle text-success' : 'fas fa-times-circle text-danger';
            
            // Requirement 2: Letters and numbers
            requirements[1].className = (/[a-zA-Z]/.test(password) && /[0-9]/.test(password)) ? 'fas fa-check-circle text-success' : 'fas fa-times-circle text-danger';
            
            // Requirement 3: Special character
            requirements[2].className = /[!@#$%^&*(),.?":{}|<>]/.test(password) ? 'fas fa-check-circle text-success' : 'fas fa-times-circle text-danger';
        });
    </script>
</body>
</html>
</html>
