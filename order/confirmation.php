<?php
session_start();
include_once '../config/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

if (!isset($_GET['order_id'])) {
    header('Location: /');
    exit;
}

$order_id = $_GET['order_id'];
$user_id = $_SESSION['user_id'];

// Verify the order belongs to the current user
$sql = "SELECT * FROM orders WHERE id = $order_id AND buyer_id = $user_id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    header('Location: /');
    exit;
}

$order = $result->fetch_assoc();

// Get order items
$items_sql = "SELECT order_items.*, products.name 
            FROM order_items 
            JOIN products ON order_items.product_id = products.id 
            WHERE order_items.order_id = $order_id";
$items_result = $conn->query($items_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Confirmation</title>
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>
    <h2>Order Confirmation</h2>
    <p>Your order (#<?php echo $order_id; ?>) has been successfully placed!</p>
    
    <h3>Order Details</h3>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($item = $items_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $item['name']; ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>$<?php echo $item['price']; ?></td>
                    <td>$<?php echo $item['price'] * $item['quantity']; ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">Total</td>
                <td>$<?php echo $order['total_amount']; ?></td>
            </tr>
        </tfoot>
    </table>
    
    <p>Thank you for your purchase!</p>
</body>
</html>