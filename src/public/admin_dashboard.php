<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}
require_once '../../config/db.php';

try {
    // Get user statistics
    $user_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $buyer_count = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'buyer'")->fetchColumn();
    $seller_count = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'seller'")->fetchColumn();
    
    // Get product statistics
    $product_count = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $published_products = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'published'")->fetchColumn();
    
    // Get order statistics
    $order_count = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    
} catch (PDOException $e) {
    die("Error fetching statistics: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #0050b5;
        }
        .stat-label {
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <h2>Welcome Admin <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?php echo $user_count; ?></div>
            <div class="stat-label">Total Users</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $buyer_count; ?></div>
            <div class="stat-label">Buyers</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $seller_count; ?></div>
            <div class="stat-label">Sellers</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $product_count; ?></div>
            <div class="stat-label">Total Products</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $published_products; ?></div>
            <div class="stat-label">Published Products</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $order_count; ?></div>
            <div class="stat-label">Total Orders</div>
        </div>
    </div>
    
    <div style="margin-top: 30px;">
        <h3>Quick Actions</h3>
        <p><a href="../products/manage.php">Manage Products</a></p>
        <p><a href="../orders/sales.php">View Sales</a></p>
        <p><a href="../reports/generate.php">Generate Reports</a></p>
    </div>
</body>
</html>