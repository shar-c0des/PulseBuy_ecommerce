<?php
ob_start();
session_start();
require_once '../../config/db.php';

$error = '';
$message = '';

// Process login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['loginEmail']);
    $password = trim($_POST['loginPassword']);

    try {
        // Fetch user from database
        $stmt = $pdo->prepare("SELECT id, username, email, password_hash, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify password and role-based redirection
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect based on role
            switch ($_SESSION['role']) {
                case 'buyer':
                    header("Location: buyer_welcome.php");
                    exit;
                case 'seller':
                    header("Location: seller_welcome.php");
                    exit;
                case 'admin':
                    header("Location: admin_dashboard.php");
                    exit;
                default:
                    header("Location: index.php");
                    exit;
            }
        } else {
            $error = "Invalid email or password";
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

// Process signup - FIXED
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
    $username = trim($_POST['signupName']); // Using username column
    $email = trim($_POST['signupEmail']);
    $password = trim($_POST['signupPassword']);
    $confirmPassword = trim($_POST['signupConfirmPassword']);
    $phone = trim($_POST['signupPhone']);
    $role = trim($_POST['userRole']);

    // Validate inputs
    if (empty($username) || empty($email) || empty($password) || empty($phone)) {
        $error = "All fields are required";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters";
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = "Email already registered";
            } else {
                // Hash password
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user - CORRECTED
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, phone, role) 
                                     VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$username, $email, $passwordHash, $phone, $role]);
                
                $userId = $pdo->lastInsertId();
                
                // Automatically log in new user - FIXED
                $_SESSION['user_id'] = $userId;
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
                $_SESSION['role'] = $role;
                
                // Redirect to welcome page
                 header("Location: {$role}_welcome.php");
            exit;
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

// Error messaging
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'session_expired':
            $error = "Your session has expired. Please log in again.";
            break;
        case 'access_denied':
            $error = "Access denied. Please log in to continue.";
            break;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PulseBuy - Login & Signup</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700;900&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #0056E0;
            --accent-yellow: #FFC107;
            --secondary-blue: #1A75FF;
            --dark-text: #22252A;
            --medium-text: #525760;
            --light-text: #7B7E85;
            --lighter-text: #A5A8B0;
            --background: #F5F7FA;
            --card-bg: #FFFFFF;
            --border-color: #E5E9EF;
            --shadow-sm: 0 2px 6px rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.08);
            --success: #00C853;
            --white: #FFFFFF;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', 'Noto Sans KR', sans-serif;
        }
        
        body {
            background-color: var(--background);
            color: var(--dark-text);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        /* Header */
        .top-nav {
            background-color: var(--primary-blue);
            color: var(--white);
            padding: 15px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .logo-area {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .logo {
            display: flex;
            align-items: center;
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: var(--white);
            text-decoration: none;
        }
        
        .logo i {
            color: var(--accent-yellow);
            margin-right: 8px;
            font-size: 26px;
        }
        
        .logo span {
            color: var(--accent-yellow);
        }
        
        /* Auth Container */
        .auth-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .auth-title {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 15px;
            color: var(--primary-blue);
        }
        
        .auth-subtitle {
            font-size: 18px;
            color: var(--medium-text);
            max-width: 600px;
            margin: 0 auto;
        }
        
        /* Auth Tabs */
        .auth-tabs {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
            border-bottom: 1px solid var(--border-color);
            width: 100%;
            max-width: 500px;
        }
        
        .auth-tab {
            padding: 15px 30px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            position: relative;
            color: var(--medium-text);
            transition: all 0.3s;
        }
        
        .auth-tab.active {
            color: var(--primary-blue);
        }
        
        .auth-tab.active:after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--primary-blue);
            border-radius: 3px;
        }
        
        /* Auth Content */
        .auth-content {
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: var(--shadow-md);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            margin-bottom: 40px;
        }
        
        .role-selector {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .role-card {
            flex: 1;
            border: 2px solid var(--border-color);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .role-card.active {
            border-color: var(--primary-blue);
            background: rgba(0, 86, 224, 0.05);
        }
        
        .role-icon {
            width: 60px;
            height: 60px;
            background: rgba(0, 86, 224, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 24px;
            color: var(--primary-blue);
        }
        
        .role-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .role-desc {
            font-size: 14px;
            color: var(--medium-text);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-text);
        }
        
        .form-input {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(0, 86, 224, 0.1);
        }
        
        .password-container {
            position: relative;
        }
        
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--light-text);
        }
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .remember-me input {
            width: 18px;
            height: 18px;
        }
        
        .forgot-password {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 500;
        }
        
        .submit-btn {
            width: 100%;
            padding: 16px;
            background: var(--primary-blue);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 25px;
        }
        
        .submit-btn:hover {
            background: #0048b8;
        }
        
        .divider {
            position: relative;
            text-align: center;
            margin: 25px 0;
        }
        
        .divider:before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: var(--border-color);
            z-index: 1;
        }
        
        .divider-text {
            position: relative;
            display: inline-block;
            background: var(--card-bg);
            padding: 0 15px;
            z-index: 2;
            color: var(--medium-text);
        }
        
        .social-login {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .social-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            border: 1px solid var(--border-color);
            background: var(--card-bg);
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .social-btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-sm);
        }
        
        .facebook-btn {
            color: #1877F2;
        }
        
        .google-btn {
            color: #DB4437;
        }
        
        .naver-btn {
            color: #03C75A;
        }
        
        .kakao-btn {
            color: #FFCD00;
        }
        
        .auth-footer {
            text-align: center;
            margin-top: 20px;
        }
        
        .auth-link {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 600;
            margin-left: 5px;
        }
        
        /* Footer */
        .footer {
            background: var(--card-bg);
            padding: 40px 5%;
            border-top: 1px solid var(--border-color);
        }
        
        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 30px;
        }
        
        .footer-section {
            flex: 1;
            min-width: 200px;
        }
        
        .footer-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--dark-text);
        }
        
        .footer-links {
            list-style: none;
        }
        
        .footer-links li {
            padding: 8px 0;
        }
        
        .footer-links a {
            color: var(--medium-text);
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .footer-links a:hover {
            color: var(--primary-blue);
        }
        
        .footer-bottom {
            max-width: 1200px;
            margin: 30px auto 0;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
            text-align: center;
            color: var(--medium-text);
            font-size: 14px;
        }
        
        /* Notification */
        .notification {
            position: fixed;
            top: 30px;
            right: 30px;
            background: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
            border-radius: 10px;
            padding: 15px 25px;
            display: flex;
            align-items: center;
            gap: 15px;
            transform: translateX(100%);
            opacity: 0;
            transition: all 0.4s;
            z-index: 2000;
        }
        
        .notification.active {
            transform: translateX(0);
            opacity: 1;
        }
        
        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }
        
        .notification-success .notification-icon {
            background: var(--success);
        }
        
        .notification-error .notification-icon {
            background: #F44336;
        }
        
        .notification-info {
            flex: 1;
        }
        
        .notification-title {
            font-weight: 600;
            margin-bottom: 3px;
        }
        
        .error-text {
            color: #F44336;
            font-size: 14px;
            margin-top: 5px;
            display: block;
        }
        
        .input-error {
            border: 1px solid #F44336 !important;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .auth-container {
                padding: 0 15px;
            }
            
            .auth-content {
                padding: 30px;
            }
            
            .role-selector {
                flex-direction: column;
            }
            
            .auth-title {
                font-size: 28px;
            }
            
            .auth-subtitle {
                font-size: 16px;
            }
            
            .footer-container {
                flex-direction: column;
                gap: 20px;
            }
        }
        
        @media (max-width: 480px) {
            .auth-content {
                padding: 20px;
            }
            
            .auth-tab {
                padding: 12px 20px;
                font-size: 16px;
            }
            
            .submit-btn {
                padding: 14px;
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <!-- Display notifications -->
    <?php if ($error): ?>
        <div class="notification notification-error active">
            <div class="notification-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="notification-info">
                <div class="notification-title">Error</div>
                <div><?= htmlspecialchars($error) ?></div>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Top Navigation -->
    <nav class="top-nav">
        <div class="logo-area">
            <a href="index.php" class="logo">
                <i class="fas fa-bolt"></i> Pulse<span>Buy</span>
            </a>
        </div>
    </nav>
    
    <!-- Auth Container -->
    <div class="auth-container">
        <div class="auth-header">
            <h1 class="auth-title">Welcome to PulseBuy</h1>
            <p class="auth-subtitle">Sign in to your account or create a new one to start buying or selling</p>
        </div>
        
        <!-- Auth Tabs -->
        <div class="auth-tabs">
            <div class="auth-tab active" data-tab="login">Login</div>
            <div class="auth-tab" data-tab="signup">Sign Up</div>
        </div>
        
        <!-- Login Form -->
        <div class="auth-content" id="loginForm">
            <form id="loginFormContent" method="POST">
                <div class="form-group">
                    <label for="loginEmail" class="form-label">Email Address</label>
                    <input type="email" id="loginEmail" name="loginEmail" class="form-input" placeholder="Enter your email" required>
                </div>
                
                <div class="form-group">
                    <label for="loginPassword" class="form-label">Password</label>
                    <div class="password-container">
                        <input type="password" id="loginPassword" name="loginPassword" class="form-input" placeholder="Enter your password" required>
                        <span class="toggle-password" id="toggleLoginPassword">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>
                
                <div class="remember-forgot">
                    <div class="remember-me">
                        <input type="checkbox" id="rememberMe">
                        <label for="rememberMe">Remember me</label>
                    </div>
                    <a href="forgot_password.php" class="forgot-password">Forgot password?</a>
                </div>
                
                <button type="submit" name="login" class="submit-btn">Login to Account</button>
                
                <div class="divider">
                    <span class="divider-text">or continue with</span>
                </div>
                
                <div class="social-login">
                    <div class="social-btn facebook-btn">
                        <i class="fab fa-facebook-f"></i>
                    </div>
                    <div class="social-btn google-btn">
                        <i class="fab fa-google"></i>
                    </div>
                    <div class="social-btn naver-btn">
                        <i class="fas fa-n"></i>
                    </div>
                    <div class="social-btn kakao-btn">
                        <i class="fas fa-comment"></i>
                    </div>
                </div>
                
                <div class="auth-footer">
                    <p>Don't have an account? <a href="#" class="auth-link" data-tab="signup">Sign up</a></p>
                </div>
            </form>
        </div>
        
        <!-- Signup Form -->
        <div class="auth-content" id="signupForm" style="display: none;">
            <form id="signupFormContent" method="POST">
                <div class="role-selector">
                    <div class="role-card active" data-role="buyer">
                        <div class="role-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="role-title">Buyer</div>
                        <div class="role-desc">I want to purchase products</div>
                    </div>
                    <div class="role-card" data-role="seller">
                        <div class="role-icon">
                            <i class="fas fa-store"></i>
                        </div>
                        <div class="role-title">Seller</div>
                        <div class="role-desc">I want to sell products</div>
                    </div>
                </div>
                
                <input type="hidden" id="userRole" name="userRole" value="buyer">
                
                <div class="form-group">
                    <label for="signupName" class="form-label">Full Name</label>
                    <input type="text" id="signupName" name="signupName" class="form-input" placeholder="Enter your full name" required>
                </div>
                
                <div class="form-group">
                    <label for="signupEmail" class="form-label">Email Address</label>
                    <input type="email" id="signupEmail" name="signupEmail" class="form-input" placeholder="Enter your email" required>
                </div>
                
                <div class="form-group">
                    <label for="signupPassword" class="form-label">Password</label>
                    <div class="password-container">
                        <input type="password" id="signupPassword" name="signupPassword" class="form-input" placeholder="Create a password" required>
                        <span class="toggle-password" id="toggleSignupPassword">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    <small class="error-text">Must be at least 8 characters</small>
                </div>
                
                <div class="form-group">
                    <label for="signupConfirmPassword" class="form-label">Confirm Password</label>
                    <div class="password-container">
                        <input type="password" id="signupConfirmPassword" name="signupConfirmPassword" class="form-input" placeholder="Confirm your password" required>
                        <span class="toggle-password" id="toggleConfirmPassword">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="signupPhone" class="form-label">Phone Number</label>
                    <input type="tel" id="signupPhone" name="signupPhone" class="form-input" placeholder="Enter your phone number" required>
                </div>
                
                <div class="form-group">
                    <div class="remember-me">
                        <input type="checkbox" id="termsAgreement" required>
                        <label for="termsAgreement">I agree to the <a href="terms.php" class="forgot-password">Terms of Service</a> and <a href="privacy.php" class="forgot-password">Privacy Policy</a></label>
                    </div>
                </div>
                
                <button type="submit" name="signup" class="submit-btn">Create Account</button>
                
                <div class="divider">
                    <span class="divider-text">or sign up with</span>
                </div>
                
                <div class="social-login">
                    <div class="social-btn facebook-btn">
                        <i class="fab fa-facebook-f"></i>
                    </div>
                    <div class="social-btn google-btn">
                        <i class="fab fa-google"></i>
                    </div>
                    <div class="social-btn naver-btn">
                        <i class="fas fa-n"></i>
                    </div>
                    <div class="social-btn kakao-btn">
                        <i class="fas fa-comment"></i>
                    </div>
                </div>
                
                <div class="auth-footer">
                    <p>Already have an account? <a href="#" class="auth-link" data-tab="login">Log in</a></p>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-section">
                <h3 class="footer-title">Customer Service</h3>
                <ul class="footer-links">
                    <li><a href="help.php">Help Center</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                    <li><a href="returns.php">Return Policy</a></li>
                    <li><a href="shipping.php">Shipping Information</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3 class="footer-title">About PulseBuy</h3>
                <ul class="footer-links">
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="careers.php">Careers</a></li>
                    <li><a href="press.php">Press</a></li>
                    <li><a href="corporate.php">Corporate Information</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3 class="footer-title">Legal</h3>
                <ul class="footer-links">
                    <li><a href="terms.php">Terms of Service</a></li>
                    <li><a href="privacy.php">Privacy Policy</a></li>
                    <li><a href="ip.php">Intellectual Property</a></li>
                    <li><a href="cookies.php">Cookies Policy</a></li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; 2025 PulseBuy. All rights reserved.</p>
        </div>
    </footer>
    
    <script>
        // Tab Switching
        const authTabs = document.querySelectorAll('.auth-tab');
        const loginForm = document.getElementById('loginForm');
        const signupForm = document.getElementById('signupForm');
        
        authTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Update active tab
                authTabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                // Show correct form
                const tabName = this.getAttribute('data-tab');
                if (tabName === 'login') {
                    loginForm.style.display = 'block';
                    signupForm.style.display = 'none';
                } else {
                    loginForm.style.display = 'none';
                    signupForm.style.display = 'block';
                }
            });
        });
        
        // Role Selection
        const roleCards = document.querySelectorAll('.role-card');
        const userRoleInput = document.getElementById('userRole');
        
        roleCards.forEach(card => {
            card.addEventListener('click', function() {
                roleCards.forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                userRoleInput.value = this.getAttribute('data-role');
            });
        });
        
        // Password Visibility Toggle
        const togglePassword = (inputId, toggleId) => {
            const passwordInput = document.getElementById(inputId);
            const toggleBtn = document.getElementById(toggleId);
            
            toggleBtn.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // Toggle eye icon
                if (type === 'password') {
                    this.innerHTML = '<i class="fas fa-eye"></i>';
                } else {
                    this.innerHTML = '<i class="fas fa-eye-slash"></i>';
                }
            });
        };
        
        // Initialize password toggles
        togglePassword('loginPassword', 'toggleLoginPassword');
        togglePassword('signupPassword', 'toggleSignupPassword');
        togglePassword('signupConfirmPassword', 'toggleConfirmPassword');
        
        // Password Validation
        const passwordInput = document.getElementById('signupPassword');
        const confirmPasswordInput = document.getElementById('signupConfirmPassword');
        
        function validatePasswords() {
            if (passwordInput.value && passwordInput.value.length < 8) {
                passwordInput.classList.add('input-error');
                return false;
            }
            
            if (passwordInput.value !== confirmPasswordInput.value) {
                confirmPasswordInput.classList.add('input-error');
                return false;
            }
            
            passwordInput.classList.remove('input-error');
            confirmPasswordInput.classList.remove('input-error');
            return true;
        }
        
        passwordInput.addEventListener('input', validatePasswords);
        confirmPasswordInput.addEventListener('input', validatePasswords);
        
        // Form Submission Validation
        document.getElementById('signupFormContent').addEventListener('submit', function(e) {
            if (!validatePasswords()) {
                e.preventDefault();
                return false;
            }
            return true;
        });
        
        // Switch tab from auth links
        document.querySelectorAll('.auth-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const tabName = this.getAttribute('data-tab');
                
                authTabs.forEach(t => t.classList.remove('active'));
                document.querySelector(`.auth-tab[data-tab="${tabName}"]`).classList.add('active');
                
                if (tabName === 'login') {
                    loginForm.style.display = 'block';
                    signupForm.style.display = 'none';
                } else {
                    loginForm.style.display = 'none';
                    signupForm.style.display = 'block';
                }
                
                // Scroll to forms
                document.querySelector('.auth-container').scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
        
        // Auto-hide notifications after delay
        const notifications = document.querySelectorAll('.notification');
        notifications.forEach(notification => {
            setTimeout(() => {
                notification.style.display = 'none';
            }, 3000);
        });
    </script>
</body>
</html>