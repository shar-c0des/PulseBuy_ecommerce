<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: auth.php?error=access_denied");
    exit;
}

$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to PulseBuy - Seller</title>
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
        
        .user-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .user-link {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--white);
            text-decoration: none;
            font-weight: 500;
            padding: 6px 12px;
            border-radius: 5px;
            transition: background 0.2s;
        }
        
        .user-link:hover {
            background: rgba(255, 255, 255, 0.15);
        }
        
        /* Welcome Container */
        .welcome-container {
            max-width: 1200px;
            margin: 80px auto;
            padding: 0 20px;
            text-align: center;
            flex: 1;
        }
        
        .welcome-card {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 60px 40px;
            box-shadow: var(--shadow-md);
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            overflow: hidden;
        }
        
        .welcome-card:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 8px;
            background: linear-gradient(to right, var(--primary-blue), var(--secondary-blue));
        }
        
        .welcome-icon {
            width: 120px;
            height: 120px;
            background: rgba(0, 86, 224, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 60px;
            color: var(--primary-blue);
        }
        
        .welcome-title {
            font-size: 42px;
            font-weight: 800;
            margin-bottom: 20px;
            color: var(--primary-blue);
        }
        
        .welcome-subtitle {
            font-size: 24px;
            color: var(--medium-text);
            margin-bottom: 40px;
            line-height: 1.6;
        }
        
        .username {
            color: var(--primary-blue);
            font-weight: 700;
        }
        
        .welcome-features {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin: 50px 0;
            flex-wrap: wrap;
        }
        
        .feature-card {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 30px;
            width: 250px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            transition: all 0.3s;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-md);
        }
        
        .feature-icon {
            width: 70px;
            height: 70px;
            background: rgba(0, 86, 224, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 30px;
            color: var(--primary-blue);
        }
        
        .feature-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 15px;
        }
        
        .feature-desc {
            font-size: 16px;
            color: var(--medium-text);
            line-height: 1.6;
        }
        
        .dashboard-btn {
            background: var(--primary-blue);
            color: var(--white);
            border: none;
            padding: 18px 50px;
            border-radius: 50px;
            font-size: 20px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(0, 86, 224, 0.3);
            display: inline-flex;
            align-items: center;
            gap: 15px;
            margin-top: 30px;
            text-decoration: none;
        }
        
        .dashboard-btn:hover {
            background: #0048b8;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 86, 224, 0.4);
        }
        
        .dashboard-btn i {
            font-size: 24px;
        }
        
        .quick-links {
            margin-top: 40px;
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .quick-link {
            color: var(--primary-blue);
            font-weight: 600;
            text-decoration: none;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .quick-link:hover {
            text-decoration: underline;
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
            margin-bottom: 10px;
        }
        
        .footer-links a {
            color: var(--medium-text);
            text-decoration: none;
            transition: color 0.2s;
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
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .welcome-title {
                font-size: 32px;
            }
            
            .welcome-subtitle {
                font-size: 20px;
            }
            
            .welcome-features {
                flex-direction: column;
                align-items: center;
            }
            
            .feature-card {
                width: 100%;
                max-width: 350px;
            }
        }
    </style>
</head>
<body>
    <!-- Top Navigation -->
    <nav class="top-nav">
        <div class="logo-area">
            <a href="index.php" class="logo">
                <i class="fas fa-bolt"></i> Pulse<span>Buy</span>
            </a>
        </div>
        
        <div class="user-actions">
            <a href="seller_dashboard.php" class="user-link">
                <i class="fas fa-user-circle"></i>
                <span><?= htmlspecialchars($username) ?></span>
            </a>
            <a href="seller_inventory.php" class="user-link">
                <i class="fas fa-boxes"></i>
                <span>Inventory</span>
            </a>
            <a href="logout.php" class="user-link">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </nav>
    
    <!-- Welcome Content -->
    <div class="welcome-container">
        <div class="welcome-card">
            <div class="welcome-icon">
                <i class="fas fa-store"></i>
            </div>
            
            <h1 class="welcome-title">Welcome to PulseBuy Seller Center!</h1>
            
            <p class="welcome-subtitle">
                Congratulations <span class="username"><?= htmlspecialchars($username) ?></span>, 
                your seller account is now active. Start managing your store and growing your business.
            </p>
            
            <div class="welcome-features">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="feature-title">Sales Analytics</h3>
                    <p class="feature-desc">Track your sales performance and customer insights</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <h3 class="feature-title">Inventory Management</h3>
                    <p class="feature-desc">Easily manage your products and stock levels</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <h3 class="feature-title">Order Fulfillment</h3>
                    <p class="feature-desc">Process and ship orders efficiently</p>
                </div>
            </div>
            
            <a href="seller_dashboard.php" class="dashboard-btn">
                <i class="fas fa-tachometer-alt"></i>
                Go to Seller Dashboard
            </a>
            
            <div class="quick-links">
                <a href="manage.php" class="quick-link">
                    <i class="fas fa-box"></i> Manage Inventory
                </a>
                <a href="seller_orders.php" class="quick-link">
                    <i class="fas fa-shopping-bag"></i> View Orders
                </a>
                <a href="seller_analytics.php" class="quick-link">
                    <i class="fas fa-chart-bar"></i> Sales Analytics
                </a>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-section">
                <h3 class="footer-title">Seller Support</h3>
                <ul class="footer-links">
                    <li><a href="seller_help.php">Help Center</a></li>
                    <li><a href="seller_contact.php">Contact Support</a></li>
                    <li><a href="seller_policies.php">Seller Policies</a></li>
                    <li><a href="seller_shipping.php">Shipping Solutions</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3 class="footer-title">Seller Resources</h3>
                <ul class="footer-links">
                    <li><a href="seller_training.php">Seller Training</a></li>
                    <li><a href="seller_marketing.php">Marketing Tools</a></li>
                    <li><a href="seller_promotions.php">Promotions</a></li>
                    <li><a href="seller_community.php">Seller Community</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3 class="footer-title">Legal</h3>
                <ul class="footer-links">
                    <li><a href="seller_terms.php">Seller Agreement</a></li>
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
</body>
</html>