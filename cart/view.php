<?php
session_start();
include_once '../config/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT cart.id, products.name, products.price, cart.quantity 
        FROM cart 
        JOIN products ON cart.product_id = products.id 
        WHERE cart.user_id = $user_id";
$result = $conn->query($sql);

$total = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>
    <h2>Shopping Cart</h2>
    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['name']; ?></td>
                        <td>$<?php echo $row['price']; ?></td>
                        <td><?php echo $row['quantity']; ?></td>
                        <td>$<?php $item_total = $row['price'] * $row['quantity']; echo $item_total; ?></td>
                        <td><a href="/cart/remove.php?id=<?php echo $row['id']; ?>">Remove</a></td>
                    </tr>
                    <?php $total += $item_total; ?>
                <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3">Total</td>
                    <td>$<?php echo $total; ?></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
        <form action="/payment/process.php" method="post">
            <input type="hidden" name="total" value="<?php echo $total; ?>">
            <button type="submit">Simulate Payment</button>
        </form>
    <?php else: ?>
        <p>Your cart is empty.</p>
    <?php endif; ?>
</body>
</html>