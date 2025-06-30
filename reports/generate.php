<?php

session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../users/login.php');
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

$usersStmt = $pdo->query("SELECT role, COUNT(*) AS total FROM users GROUP BY role");
$usersReport = $usersStmt->fetchAll();

$productsStmt = $pdo->query("SELECT p.name, SUM(oi.quantity) AS total_sold
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    GROUP BY oi.product_id
    ORDER BY total_sold DESC
    LIMIT 5");
$topProducts = $productsStmt->fetchAll();

$sellerStmt = $pdo->query("SELECT u.username, SUM(oi.quantity * oi.price) AS revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN users u ON p.seller_id = u.id
    GROUP BY p.seller_id
    ORDER BY revenue DESC");
$sellerRevenue = $sellerStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Reports</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Admin Reports</h1>

        <section>
            <h2>User Roles Summary</h2>
            <ul>
                <?php foreach ($usersReport as $row): ?>
                    <li><?php echo htmlspecialchars($row['role']) . ': ' . $row['total']; ?></li>
                <?php endforeach; ?>
            </ul>
        </section>

        <section>
            <h2>Top Selling Products</h2>
            <ul>
                <?php foreach ($topProducts as $product): ?>
                    <li><?php echo htmlspecialchars($product['name']) . ' — Sold: ' . $product['total_sold']; ?></li>
                <?php endforeach; ?>
            </ul>
        </section>

        <section>
            <h2>Revenue per Seller</h2>
            <ul>
                <?php foreach ($sellerRevenue as $seller): ?>
                    <li><?php echo htmlspecialchars($seller['username']) . ' — R' . number_format($seller['revenue'], 2); ?></li>
                <?php endforeach; ?>
            </ul>
        </section>

        <p><a href="../admin/dashboard.php">Back to Dashboard</a></p>
    </div>
</body>
</html>
