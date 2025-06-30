<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'buyer') {
    header("Location: login.php");
    exit;
}
require_once '../../config/db.php';

try {
    // Get user stats
    $stats_stmt = $pdo->prepare("SELECT 
                                COUNT(DISTINCT o.id) as total_orders,
                                COALESCE(SUM(o.total_amount), 0) as total_spent,
                                COUNT(DISTINCT oi.product_id) as unique_products,
                                COUNT(DISTINCT uc.id) as coupon_count
                             FROM orders o 
                             LEFT JOIN order_items oi ON o.id = oi.order_id 
                             LEFT JOIN user_coupons uc ON uc.user_id = o.user_id AND uc.used = 0
                             WHERE o.user_id = ?");
    $stats_stmt->execute([$_SESSION['user_id']]);
    $user_stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get recent orders
    $order_stmt = $pdo->prepare("SELECT o.id, o.total_amount, o.status, o.created_at, 
                                (SELECT p.title 
                                 FROM order_items oi 
                                 JOIN products p ON oi.product_id = p.id 
                                 WHERE oi.order_id = o.id 
                                 ORDER BY oi.id 
                                 LIMIT 1) as product_name,
                                COUNT(oi.id) as item_count
                         FROM orders o 
                         LEFT JOIN order_items oi ON o.id = oi.order_id 
                         WHERE o.user_id = ? 
                         GROUP BY o.id 
                         ORDER BY o.created_at DESC 
                         LIMIT 10");
    $order_stmt->execute([$_SESSION['user_id']]);
    $orders = $order_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get available coupons for user
    $coupon_stmt = $pdo->prepare("SELECT c.id, c.code, c.description, c.discount_type, 
                                 c.discount_amount, c.expiry_date
                          FROM user_coupons uc
                          JOIN coupons c ON uc.coupon_id = c.id
                          WHERE uc.user_id = ? 
                          AND uc.used = 0
                          AND (c.expiry_date IS NULL OR c.expiry_date >= CURDATE())");
    $coupon_stmt->execute([$_SESSION['user_id']]);
    $coupons = $coupon_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error fetching data: " . $e->getMessage());
}

// Format currency
function format_currency($amount) {
    return 'R' . number_format($amount, 0);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PulseBuy - My Dashboard</title>
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
            --warning: #FF9800;
            --danger: #F44336;
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
        }
        
        /* Top Navigation */
        .top-nav {
            background-color: var(--primary-blue);
            color: var(--white);
            padding: 15px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
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
        
        .search-container {
            flex: 1;
            max-width: 600px;
            margin: 0 30px;
            position: relative;
        }
        
        .search-bar {
            width: 100%;
            padding: 12px 20px 12px 48px;
            border: none;
            border-radius: 30px;
            font-size: 15px;
            background: rgba(255, 255, 255, 0.15);
            color: var(--white);
            transition: all 0.3s;
        }
        
        .search-bar::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }
        
        .search-bar:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.25);
        }
        
        .search-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.7);
            font-size: 17px;
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
            font-size: 14px;
        }
        
        .user-link:hover {
            background: rgba(255, 255, 255, 0.15);
        }
        
        .nav-btn {
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.15);
            color: var(--white);
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .nav-btn:hover {
            background: rgba(255, 255, 255, 0.25);
        }
        
        /* Main Navigation */
        .main-nav {
            background: var(--card-bg);
            border-bottom: 1px solid var(--border-color);
            padding: 0 5%;
            overflow-x: auto;
            scrollbar-width: none;
        }
        
        .main-nav::-webkit-scrollbar {
            display: none;
        }
        
        .nav-links {
            display: flex;
            list-style: none;
            gap: 25px;
            padding: 15px 0;
        }
        
        .nav-link {
            color: var(--dark-text);
            text-decoration: none;
            font-size: 15px;
            font-weight: 500;
            white-space: nowrap;
            padding: 5px 0;
            position: relative;
            transition: color 0.2s;
        }
        
        .nav-link.active {
            color: var(--primary-blue);
            font-weight: 700;
        }
        
        .nav-link.active:after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--primary-blue);
            border-radius: 3px;
        }
        
        /* Promotional Banner */
        .promo-banner {
            background: linear-gradient(to right, var(--primary-blue), var(--secondary-blue));
            color: white;
            padding: 16px 5%;
        }
        
        .promo-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .promo-text {
            font-size: 16px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .promo-text .username {
            font-weight: 700;
            margin: 0 4px;
        }
        
        .promo-btn {
            background: white;
            color: var(--primary-blue);
            border: none;
            padding: 10px 28px;
            border-radius: 30px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            font-size: 15px;
        }
        
        .promo-btn:hover {
            background: #f5fff9;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.12);
        }
        
        /* Dashboard Layout */
        .dashboard-container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 5%;
            display: grid;
            grid-template-columns: 1fr;
            gap: 25px;
        }
        
        @media (min-width: 992px) {
            .dashboard-container {
                grid-template-columns: 280px 1fr;
            }
        }
        
        /* Account Features */
        .features-section {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--dark-text);
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 18px;
        }
        
        .feature-card {
            background: #f9f9f9;
            border-radius: 10px;
            padding: 20px;
            transition: all 0.3s;
            border: 1px solid #f0f0f0;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        
        .feature-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
            border-color: #e0e0e0;
        }
        
        .feature-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }
        
        .feature-icon {
            width: 36px;
            height: 36px;
            background: rgba(0, 86, 224, 0.15);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-blue);
            font-size: 18px;
        }
        
        .feature-link {
            color: var(--primary-blue);
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .feature-main {
            margin-top: 15px;
        }
        
        .feature-amount {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary-blue);
            margin: 8px 0;
        }
        
        .feature-title {
            font-size: 17px;
            font-weight: 600;
            color: var(--dark-text);
        }
        
        .feature-desc {
            font-size: 14px;
            color: var(--light-text);
            line-height: 1.5;
            margin-top: 5px;
        }
        
        /* Sidebar */
        .sidebar {
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            padding: 25px;
            position: sticky;
            top: 20px;
            height: fit-content;
        }
        
        .sidebar-title {
            font-size: 22px;
            font-weight: 700;
            color: var(--dark-text);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .sidebar-title-icon {
            width: 40px;
            height: 40px;
            background: rgba(0, 86, 224, 0.12);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-blue);
            font-size: 20px;
        }
        
        .sidebar-menu {
            list-style: none;
        }
        
        .sidebar-item {
            padding: 15px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .sidebar-link {
            color: var(--medium-text);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.2s;
            padding: 6px 10px;
            border-radius: 8px;
        }
        
        .sidebar-link:hover, .sidebar-link.active {
            background: rgba(0, 86, 224, 0.08);
            color: var(--primary-blue);
        }
        
        .sidebar-icon {
            width: 24px;
            text-align: center;
            font-size: 18px;
        }
        
        /* Order History */
        .order-section {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 30px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 30px;
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .filter-group {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .filter-label {
            font-size: 15px;
            color: var(--light-text);
        }
        
        .filter-select {
            padding: 10px 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background-color: white;
            font-size: 15px;
            min-width: 150px;
        }
        
        .filter-text {
            font-size: 14px;
            color: var(--light-text);
            margin-left: 15px;
        }
        
        .order-list {
            margin-top: 15px;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 20px;
            border-radius: 10px;
            background: white;
            margin-bottom: 12px;
            border: 1px solid #f0f0f0;
            align-items: center;
            transition: all 0.3s;
        }
        
        .order-item:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
            border-color: #ddd;
        }
        
        .order-info {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        
        .order-date {
            font-size: 15px;
            font-weight: 600;
            color: var(--medium-text);
            min-width: 100px;
        }
        
        .order-product {
            font-size: 16px;
            font-weight: 500;
            max-width: 500px;
        }
        
        .order-status {
            font-size: 14px;
            font-weight: 500;
            padding: 5px 12px;
            border-radius: 15px;
            margin-left: 15px;
        }
        
        .status-delivered {
            background: rgba(76, 175, 80, 0.15);
            color: var(--success);
        }
        
        .status-processing {
            background: rgba(255, 152, 0, 0.15);
            color: var(--warning);
        }
        
        .status-cancelled {
            background: rgba(244, 67, 54, 0.15);
            color: var(--danger);
        }
        
        .order-price {
            font-weight: 700;
            color: var(--dark-text);
            font-size: 16px;
            margin-right: 10px;
        }
        
        .order-action {
            background: var(--primary-blue);
            color: white;
            border: none;
            padding: 10px 22px;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .order-action:hover {
            background: #0048b8;
        }
        
        /* Invoice Section */
        .invoice-section {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 30px;
            box-shadow: var(--shadow-sm);
        }
        
        .invoice-content {
            margin-top: 15px;
        }
        
        .invoice-desc {
            font-size: 15px;
            color: var(--medium-text);
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        .invoice-form {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .invoice-input {
            flex: 1;
            min-width: 300px;
            padding: 13px 18px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 15px;
        }
        
        .invoice-btn {
            background: var(--primary-blue);
            color: white;
            border: none;
            padding: 13px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .invoice-btn:hover {
            background: #0048b8;
        }
        
        /* No Orders Message */
        .no-orders {
            text-align: center;
            padding: 40px 20px;
            color: var(--medium-text);
        }
        
        .no-orders-icon {
            font-size: 48px;
            color: var(--light-text);
            margin-bottom: 20px;
        }
        
        /* Responsive Design */
        @media (max-width: 1100px) {
            .dashboard-container {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                position: relative;
                top: 0;
                margin-bottom: 25px;
            }
        }
        
        @media (max-width: 768px) {
            .top-nav {
                padding: 15px;
            }
            
            .logo {
                font-size: 24px;
            }
            
            .search-container {
                order: 3;
                width: 100%;
                margin: 15px 0 0;
            }
            
            .user-actions {
                margin-left: auto;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .order-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .order-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .order-actions {
                width: 100%;
                display: flex;
                justify-content: flex-end;
            }
        }
        
        @media (max-width: 480px) {
            .user-link span {
                display: none;
            }
            
            .nav-btn {
                padding: 8px;
            }
            
            .promo-container {
                flex-direction: column;
                text-align: center;
            }
            
            .promo-btn {
                width: 100%;
            }
            
            .invoice-form {
                flex-direction: column;
            }
            
            .invoice-input, .invoice-btn {
                width: 100%;
            }
        }
        
        @keyframes float {
            0% { transform: translateY(0px);}
            50% { transform: translateY(-15px);}
            100% { transform: translateY(0px);}
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 #FFC10744; }
            70% { box-shadow: 0 0 0 10px #FFC10700; }
            100% { box-shadow: 0 0 0 0 #FFC10700; }
        }
        .feature-card:hover, .order-action:hover, .promo-btn:hover {
            transform: translateY(-3px) scale(1.03);
            box-shadow: 0 6px 18px rgba(0,86,224,0.13);
        }
    </style>
</head>
<body>
    <!-- Top Navigation -->
    <nav class="top-nav">
        <div class="logo-area">
            <a href="#" class="logo">
                <i class="fas fa-bolt"></i> Pulse<span>Buy</span>
            </a>
        </div>
        
        <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-bar" placeholder="Search for products, brands and categories">
        </div>
        
        <div class="user-actions">
            <a href="#" class="user-link">
                <i class="fas fa-headset"></i>
                <span>customer service center</span>
            </a>
            <a href="#" class="user-link">
                <i class="fas fa-globe"></i>
                <span>Global</span>
            </a>
            <a href="#" class="user-link">
                <i class="fas fa-user"></i>
                <span><?= htmlspecialchars($_SESSION['username'] ?? 'Customer') ?></span>
            </a>
            <a href="logout.php" class="nav-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>log out</span>
            </a>
        </div>
    </nav>
    
    <!-- Dashboard Content -->
    <main class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-title">
                <div class="sidebar-title-icon">
                    <i class="fas fa-user"></i>
                </div>
                <div>My PulseBuy</div>
            </div>
            
            <ul class="sidebar-menu">
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link active">
                        <span class="sidebar-icon"><i class="fas fa-home"></i></span>
                        <span>Home</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link">
                        <span class="sidebar-icon"><i class="fas fa-file-alt"></i></span>
                        <span>Orders</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link">
                        <span class="sidebar-icon"><i class="fas fa-heart"></i></span>
                        <span>Wishlist</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div>
            <!-- Account Features -->
            <div class="features-section">
                <h2 class="section-title">
                    <i class="fas fa-wallet"></i> Account Summary
                </h2>
                
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-header">
                            <div class="feature-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <a href="#" class="feature-link">
                                View Details <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                        <div class="feature-main">
                            <div class="feature-amount"><?= $user_stats['total_orders'] ?? 0 ?></div>
                            <h3 class="feature-title">Total Orders</h3>
                            <p class="feature-desc">Orders placed with PulseBuy</p>
                        </div>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-header">
                            <div class="feature-icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <a href="#" class="feature-link">
                                View Details <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                        <div class="feature-main">
                            <div class="feature-amount">R<?= number_format($user_stats['total_spent'] ?? 0, 2) ?></div>
                            <h3 class="feature-title">Total Spent</h3>
                            <p class="feature-desc">Cumulative order value</p>
                        </div>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-header">
                            <div class="feature-icon">
                                <i class="fas fa-box-open"></i>
                            </div>
                            <a href="#" class="feature-link">
                                View Details <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                        <div class="feature-main">
                            <div class="feature-amount"><?= $user_stats['unique_products'] ?? 0 ?></div>
                            <h3 class="feature-title">Unique Products</h3>
                            <p class="feature-desc">Different products purchased</p>
                        </div>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-header">
                            <div class="feature-icon">
                                <i class="fas fa-ticket-alt"></i>
                            </div>
                            <a href="#" class="feature-link">
                                View Details <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                        <div class="feature-main">
                            <div class="feature-amount"><?= $user_stats['coupon_count'] ?? 0 ?></div>
                            <h3 class="feature-title">Discount Coupons</h3>
                            <p class="feature-desc">Available discount offers</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Order History -->
            <div class="order-section">
                <div class="order-header">
                    <h2 class="section-title">
                        <i class="fas fa-history"></i> Recent Order History
                    </h2>
                    
                    <div class="filter-group">
                        <span class="filter-label">for the past month</span>
                        <select class="filter-select" id="orderFilter">
                            <option value="all">All Orders</option>
                            <option value="delivered">Delivered</option>
                            <option value="processing">Processing</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <span class="filter-text">up to (5 years)</span>
                    </div>
                </div>
                
                <div class="order-list" id="orderList">
                    <?php if (count($orders) > 0): ?>
                        <?php foreach ($orders as $order): 
                            $statusClass = '';
                            if ($order['status'] == 'delivered') {
                                $statusClass = 'status-delivered';
                            } elseif ($order['status'] == 'processing') {
                                $statusClass = 'status-processing';
                            } elseif ($order['status'] == 'cancelled') {
                                $statusClass = 'status-cancelled';
                            }
                        ?>
                        <div class="order-item" data-status="<?= $order['status'] ?>">
                            <div class="order-info">
                                <div class="order-date"><?= date('Y-m-d', strtotime($order['created_at'])) ?></div>
                                <div class="order-product">
                                    <?= htmlspecialchars($order['product_name']) ?>
                                    <?php if ($order['item_count'] > 1): ?>
                                        and <?= $order['item_count'] - 1 ?> more
                                    <?php endif; ?>
                                </div>
                                <div class="order-status <?= $statusClass ?>"><?= ucfirst($order['status']) ?></div>
                            </div>
                            <div class="order-price">R<?= number_format($order['total_amount'], 2) ?></div>
                            <button class="order-action" data-order-id="<?= $order['id'] ?>">
                                <i class="fas fa-eye"></i> View
                            </button>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-orders">
                            <div class="no-orders-icon">
                                <i class="fas fa-shopping-basket"></i>
                            </div>
                            <h3>No Orders Found</h3>
                            <p>You haven't placed any orders yet. Start shopping to see them here.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Invoice Section -->
            <div class="invoice-section">
                <h2 class="section-title">
                    <i class="fas fa-file-invoice"></i> Receipt/Invoice Inquiry
                </h2>
                
                <div class="invoice-content">
                    <p class="invoice-desc">Enter the order ID or product information to view detailed receipts and invoices.</p>
                    
                    <form action="invoice.php" method="GET" class="invoice-form">
                        <input type="text" class="invoice-input" name="search" placeholder="Enter order ID or product name...">
                        <button type="submit" class="invoice-btn">
                            <i class="fas fa-search"></i> View Invoice
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Sparky Bunny Assistant -->
    <div class="sparky-container" style="position: fixed; bottom: 20px; right: 20px; z-index: 999; display: flex; align-items: flex-end;">
        <div class="sparky-bubble show" style="background: #fff; border-radius: 15px; padding: 15px; max-width: 250px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); margin-right: 20px; opacity: 1; transform: translateY(0); transition: all 0.5s ease; position: relative;">
            <p style="font-size: 15px; color: #222;">Hi, I'm Sparky! Need help or tips? Click me!</p>
        </div>
        <div class="sparky" style="width: 100px; height: 100px; background: #fff; border-radius: 50%; position: relative; box-shadow: 0 0 15px rgba(0, 86, 224, 0.3); cursor: pointer; animation: float 3s ease-in-out infinite;">
            <div class="ear left" style="width: 10px; height: 40px; background: #fff; border: 1.5px solid #0056E0; border-radius: 50%; position: absolute; top: -15px; left: 20px; transform: rotate(-15deg);"></div>
            <div class="ear right" style="width: 10px; height: 40px; background: #fff; border: 1.5px solid #0056E0; border-radius: 50%; position: absolute; top: -15px; right: 20px; transform: rotate(15deg);"></div>
            <div class="eye left" style="width: 10px; height: 10px; background: #0056E0; border-radius: 50%; position: absolute; top: 30px; left: 25px;"></div>
            <div class="eye right" style="width: 10px; height: 10px; background: #0056E0; border-radius: 50%; position: absolute; top: 30px; right: 25px;"></div>
            <div class="nose" style="width: 5px; height: 5px; background: #FFC107; border-radius: 50%; position: absolute; top: 45px; left: 47.5px;"></div>
            <div class="mouth" style="width: 10px; height: 5px; border-bottom: 1.5px solid #FFC107; border-radius: 50%; position: absolute; top: 50px; left: 45px;"></div>
            <div class="vest" style="width: 40px; height: 30px; background: #0056E0; position: absolute; top: 60px; left: 30px; border-radius: 5px;"></div>
            <div class="lightning-bolt" style="width: 10px; height: 15px; background: #FFC107; clip-path: polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%); position: absolute; top: 67px; left: 45px; animation: pulse 2s infinite;"></div>
            <div class="tail" style="width: 15px; height: 15px; background: #fff; border: 1.5px solid #0056E0; border-top: none; border-right: none; border-radius: 50%; position: absolute; bottom: 10px; right: 10px; transform: rotate(45deg);"></div>
        </div>
    </div>
    
    <script>
        // Interactive functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Order filtering
            const orderFilter = document.getElementById('orderFilter');
            const orderList = document.getElementById('orderList');
            const orderItems = document.querySelectorAll('.order-item');
            
            orderFilter.addEventListener('change', function() {
                const filterValue = this.value;
                
                orderItems.forEach(item => {
                    if (filterValue === 'all' || item.getAttribute('data-status') === filterValue) {
                        item.style.display = 'flex';
                    } else {
                        item.style.display = 'none';
                    }
                });
                
                // Show no orders message if all are filtered out
                const visibleItems = Array.from(orderItems).filter(item => 
                    item.style.display !== 'none');
                
                if (visibleItems.length === 0) {
                    if (!document.querySelector('.no-orders')) {
                        const noOrders = document.createElement('div');
                        noOrders.className = 'no-orders';
                        noOrders.innerHTML = `
                            <div class="no-orders-icon">
                                <i class="fas fa-shopping-basket"></i>
                            </div>
                            <h3>No Orders Found</h3>
                            <p>No orders match the selected filter.</p>
                        `;
                        orderList.appendChild(noOrders);
                    }
                } else {
                    const noOrdersMsg = document.querySelector('.no-orders');
                    if (noOrdersMsg) noOrdersMsg.remove();
                }
            });
            
            // Order view functionality
            const viewButtons = document.querySelectorAll('.order-action');
            viewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const orderId = this.getAttribute('data-order-id');
                    window.location.href = `order_details.php?id=${orderId}`;
                });
            });
            
            // Feature card interactions
            const featureCards = document.querySelectorAll('.feature-card');
            featureCards.forEach(card => {
                card.addEventListener('click', function() {
                    const title = this.querySelector('.feature-title').textContent;
                    window.location.href = this.querySelector('.feature-link').href;
                });
            });
            
            // Navigation active state
            const navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    navLinks.forEach(l => l.classList.remove('active'));
                    this.classList.add('active');
                });
            });
            
            // Sidebar active state
            const sidebarLinks = document.querySelectorAll('.sidebar-link');
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    sidebarLinks.forEach(l => l.classList.remove('active'));
                    this.classList.add('active');
                });
            });
            
            // Search functionality
            const searchBar = document.querySelector('.search-bar');
            searchBar.addEventListener('keyup', (e) => {
                if (e.key === 'Enter') {
                    window.location.href = `search.php?q=${encodeURIComponent(searchBar.value)}`;
                }
            });
            
            // Logout button
            const logoutBtn = document.querySelector('.logout-btn');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', function() {
                    window.location.href = 'logout.php';
                });
            }
        });
    </script>
</body>
</html>