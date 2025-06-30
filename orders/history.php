<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header('Location: ../users/login.php');
    exit;
}

$buyer_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("
    SELECT o.id, o.total, o.status, o.created_at
    FROM orders o
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
");
$stmt->execute([$buyer_id]);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order History</title>
</head>
<body>
    <h1>My Orders</h1>
    <?php if ($orders): ?>
        <ul>
        <?php foreach ($orders as $order): ?>
            <li>
                <strong>Order #<?php echo $order['id']; ?></strong><br>
                Total: R<?php echo $order['total']; ?><br>
                Status: <?php echo ucfirst($order['status']); ?><br>
                Date: <?php echo $order['created_at']; ?>
            </li>
        <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>You have no orders.</p>
    <?php endif; ?>
</body>
</html>
