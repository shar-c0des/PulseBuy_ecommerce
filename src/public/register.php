<?php
include_once '../../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $username = htmlspecialchars(trim($_POST['username']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $role = ($_POST['role'] === 'seller') ? 'seller' : 'buyer';
    
    // Validation
    $errors = [];
    if (empty($username)) $errors[] = "Username is required";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
    if (empty($password) || strlen($password) < 6) $errors[] = "Password must be at least 6 characters";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match";
    
    // Check for existing user
 $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
        $stmt->execute([':username' => $username, ':email' => $email]);
        
        if ($stmt->fetch()) {
            $errors[] = "Username or email already exists";
        }
        
        if (!empty($errors)) {
            $error_message = urlencode(implode(" | ", $errors));
            header("Location: register.php?error=$error_message");
            exit;
        }
        
    
    // Handle errors
    if (!empty($errors)) {
        $error_message = urlencode(implode(" | ", $errors));
        header("Location: register.php?error=$error_message");
        exit;
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
   // Insert into database (using PDO prepared statements)
try {
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password_hash, role, created_at) 
        VALUES (:username, :email, :password, :role, NOW())
    ");
    
    $stmt->execute([
        ':username' => $username,
        ':email' => $email,
        ':password' => $hashed_password,
        ':role' => $role
    ]);
    
    header("Location: login.php?success=1");
    exit;
} catch (PDOException $e) {
    header("Location: register.php?error=Database error: " . $e->getMessage());
    exit;
}

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PulseBuy - Create Account</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --primary-blue: #0050b5;
            --accent-yellow: #ffd166;
            --pink-accent: #ff6b6b;
            --light-gray: #f8f9fa;
            --medium-gray: #e9ecef;
            --dark-gray: #495057;
            --white: #ffffff;
        }

        body {
            background-color: var(--white);
            color: var(--dark-gray);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 900px;
        }

        .logo {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo-icon {
            font-size: 48px;
            color: var(--primary-blue);
        }

        .logo-text {
            font-size: 36px;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 10px;
        }

        .pulse-text {
            color: var(--dark-gray);
        }

        .buy-text {
            color: var(--accent-yellow);
        }

        .tagline {
            color: var(--dark-gray);
            font-size: 16px;
            margin-top: 5px;
            font-weight: 500;
        }

        .tabs {
            display: flex;
            margin-bottom: 30px;
            border-bottom: 2px solid var(--medium-gray);
        }

        .tab {
            flex: 1;
            text-align: center;
            padding: 18px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            color: var(--dark-gray);
            border-bottom: 3px solid transparent;
        }

        .tab.active {
            color: var(--primary-blue);
            border-bottom: 3px solid var(--pink-accent);
        }

        .form-card {
            border: 2px solid var(--pink-accent);
            border-radius: 12px;
            padding: 40px;
            background: var(--white);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.05);
            display: none;
        }

        .form-card.active {
            display: block;
        }

        .form-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .heading {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--dark-gray);
            position: relative;
            display: inline-block;
        }

        .heading:after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: var(--accent-yellow);
            border-radius: 10px;
        }

        .subheading {
            color: var(--dark-gray);
            font-size: 16px;
            margin-top: 25px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 22px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-size: 16px;
            font-weight: 500;
            color: var(--text-dark);
        }

        .form-label .required {
            color: var(--pink-accent);
        }

        .form-input {
            width: 100%;
            padding: 16px;
            border: 1px solid var(--medium-gray);
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: var(--white);
        }

        .form-input:focus {
            border-color: var(--primary-blue);
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 80, 181, 0.1);
        }

        .form-input::placeholder {
            color: #adb5bd;
        }

        .terms-container {
            margin: 25px 0 30px;
        }

        .terms-agreement {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .terms-text {
            font-size: 14px;
            color: var(--dark-gray);
        }

        .terms-link {
            color: var(--primary-blue);
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .terms-link:hover {
            text-decoration: underline;
            color: var(--pink-accent);
        }

        .btn {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        .btn-register {
            background: var(--primary-blue);
            color: var(--white);
            margin-bottom: 25px;
        }

        .btn-register:hover {
            background: #003d93;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 80, 181, 0.2);
        }

        .login-container {
            text-align: center;
            font-size: 14px;
            color: var(--dark-gray);
            margin-bottom: 15px;
        }

        .login-link {
            color: var(--primary-blue);
            font-weight: 600;
            text-decoration: none;
            margin-left: 5px;
            transition: all 0.3s ease;
        }

        .login-link:hover {
            color: var(--pink-accent);
            text-decoration: underline;
        }

        /* Business-specific fields */
        .business-section {
            margin: 30px 0;
            padding: 30px;
            border-radius: 10px;
            background: var(--light-gray);
        }

        .business-section h3 {
            font-size: 22px;
            margin-bottom: 20px;
            color: var(--primary-blue);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .business-icon {
            color: var(--pink-accent);
        }

        .help-text {
            font-size: 14px;
            color: var(--dark-gray);
            margin-top: 5px;
            font-style: italic;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-card {
                padding: 30px 20px;
            }
            
            .logo-text {
                font-size: 32px;
            }
            
            .heading {
                font-size: 24px;
            }
            
            .tabs {
                flex-direction: column;
            }
            
            .business-section {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <div class="logo-icon">
                <i class="fas fa-bolt"></i>
            </div>
            <div class="logo-text">
                <span class="pulse-text">Pulse</span>
                <span class="buy-text">Buy</span>
            </div>
            <div class="tagline">South Africa's #1 Marketplace</div>
        </div>
        
        <div class="tabs">
            <div class="tab active" data-target="buyer-form">Buyer Registration</div>
            <div class="tab" data-target="seller-form">Seller Registration</div>
        </div>

        <!-- Buyer Registration Form -->
        <div class="form-card active" id="buyer-form">
            <div class="form-header">
                <h2 class="heading">Create Buyer Account</h2>
                <p class="subheading">Join millions of happy shoppers on PulseBuy</p>
            </div>
            
            <form id="buyer-register-form" action="register.php" method="POST">
                <input type="hidden" name="role" value="buyer">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="first-name">First Name <span class="required">*</span></label>
                        <input type="text" id="first-name" name="first_name" class="form-input" placeholder="Your first name" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="last-name">Last Name <span class="required">*</span></label>
                        <input type="text" id="last-name" name="last_name" class="form-input" placeholder="Your last name" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="username">Username <span class="required">*</span></label>
                        <input type="text" id="username" name="username" class="form-input" placeholder="Choose a username" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="email">Email <span class="required">*</span></label>
                        <input type="email" id="email" name="email" class="form-input" placeholder="you@example.com" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-input" placeholder="+27 (0) __ ___ ____">
                        <div class="help-text">For delivery updates</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="password">Password <span class="required">*</span></label>
                        <input type="password" id="password" name="password" class="form-input" placeholder="Create a strong password" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="confirm-password">Confirm Password <span class="required">*</span></label>
                        <input type="password" id="confirm-password" name="confirm_password" class="form-input" placeholder="Retype your password" required>
                    </div>
                </div>
                
                <div class="terms-container">
                    <div class="terms-agreement">
                        <input type="checkbox" id="terms-agree" name="terms_agree" required>
                        <div class="terms-text">
                            I agree to the <a href="#" class="terms-link">Terms of Service</a> and 
                            <a href="#" class="terms-link">Privacy Policy</a>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-register">
                    <i class="fas fa-user"></i> Create Buyer Account
                </button>
                
                <div class="login-container">
                    <span>Already have an account?</span>
                    <a href="login.php" class="login-link">Log In</a>
                </div>
            </form>
        </div>

        <!-- Seller Registration Form -->
        <div class="form-card" id="seller-form">
            <div class="form-header">
                <h2 class="heading">Create Seller Account</h2>
                <p class="subheading">Reach millions of customers on PulseBuy</p>
            </div>
            
            <form id="seller-register-form" action="register.php" method="POST">
                <input type="hidden" name="role" value="seller">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="seller-first-name">First Name <span class="required">*</span></label>
                        <input type="text" id="seller-first-name" name="first_name" class="form-input" placeholder="Owner first name" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="seller-last-name">Last Name <span class="required">*</span></label>
                        <input type="text" id="seller-last-name" name="last_name" class="form-input" placeholder="Owner last name" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="business-name">Business Name <span class="required">*</span></label>
                        <input type="text" id="business-name" name="business_name" class="form-input" placeholder="Company Name" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="business-type">Business Type <span class="required">*</span></label>
                        <select class="form-input" id="business-type" name="business_type" required>
                            <option value="">Select your business type</option>
                            <option value="individual">Individual/Sole Proprietor</option>
                            <option value="partnership">Partnership</option>
                            <option value="cc">Close Corporation (CC)</option>
                            <option value="pty">Private Company (Pty Ltd)</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="seller-email">Email <span class="required">*</span></label>
                        <input type="email" id="seller-email" name="email" class="form-input" placeholder="business@email.com" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="seller-phone">Business Phone <span class="required">*</span></label>
                        <input type="tel" id="seller-phone" name="phone" class="form-input" placeholder="+27 (0) __ ___ ____" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="seller-password">Password <span class="required">*</span></label>
                        <input type="password" id="seller-password" name="password" class="form-input" placeholder="Create a strong password" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="seller-confirm-password">Confirm Password <span class="required">*</span></label>
                        <input type="password" id="seller-confirm-password" name="confirm_password" class="form-input" placeholder="Retype your password" required>
                    </div>
                </div>
                
                <!-- Business Information Section -->
                <div class="business-section">
                    <h3><i class="fas fa-building business-icon"></i> Business Information</h3>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label" for="company-reg">Company Registration #</label>
                            <input type="text" id="company-reg" name="company_reg" class="form-input" placeholder="Registration number">
                            <div class="help-text">CC/Company registration number</div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="tax-id">Tax ID/VAT Number</label>
                            <input type="text" id="tax-id" name="tax_id" class="form-input" placeholder="Tax identification">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="industry">Industry Category <span class="required">*</span></label>
                            <select class="form-input" id="industry" name="industry" required>
                                <option value="">Select industry category</option>
                                <option value="fashion">Fashion & Accessories</option>
                                <option value="electronics">Electronics</option>
                                <option value="home">Home & Garden</option>
                                <option value="beauty">Beauty & Health</option>
                                <option value="food">Food & Groceries</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="products">Products You'll Sell</label>
                            <input type="text" id="products" name="products" class="form-input" placeholder="Product categories">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="store-name">Preferred Store Name <span class="required">*</span></label>
                        <input type="text" id="store-name" name="store_name" class="form-input" placeholder="Your store name on PulseBuy" required>
                        <div class="help-text">This will be visible to buyers</div>
                    </div>
                </div>
                
                <div class="terms-container">
                    <div class="terms-agreement">
                        <input type="checkbox" id="seller-terms-agree" name="terms_agree" required>
                        <div class="terms-text">
                            I agree to PulseBuy's <a href="#" class="terms-link">Seller Agreement</a>, 
                            <a href="#" class="terms-link">Terms of Service</a>, and 
                            <a href="#" class="terms-link">Privacy Policy</a>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-register">
                    <i class="fas fa-store"></i> Create Seller Account
                </button>
                
                <div class="login-container">
                    <span>Already have a seller account?</span>
                    <a href="login.php" class="login-link">Log In</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab functionality
            const tabs = document.querySelectorAll('.tab');
            const forms = document.querySelectorAll('.form-card');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    tabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    
                    const target = this.getAttribute('data-target');
                    forms.forEach(form => {
                        form.classList.remove('active');
                        if(form.id === target) {
                            form.classList.add('active');
                        }
                    });
                });
            });
            
            // Show error messages
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('error')) {
                alert(decodeURIComponent(urlParams.get('error')));
            }
            
            // Show success message
            if (urlParams.has('success')) {
                alert('Registration successful! You can now log in.');
            }
        });
    </script>
</body>
</html>