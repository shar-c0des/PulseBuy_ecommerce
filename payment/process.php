<?php
session_start();
include_once '../config/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $total = $_POST['total'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Create order
        $sql = "INSERT INTO orders (buyer_id, total_amount, status) VALUES ($user_id, $total, 'completed')";
        $conn->query($sql);
        $order_id = $conn->insert_id;

        // Get cart items
        $cart_sql = "SELECT cart.product_id, cart.quantity, products.price 
                    FROM cart 
                    JOIN products ON cart.product_id = products.id 
                    WHERE cart.user_id = $user_id";
        $cart_result = $conn->query($cart_sql);

        while ($cart_item = $cart_result->fetch_assoc()) {
            $product_id = $cart_item['product_id'];
            $quantity = $cart_item['quantity'];
            $price = $cart_item['price'];

            // Add order item
            $item_sql = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                        VALUES ($order_id, $product_id, $quantity, $price)";
            $conn->query($item_sql);

            // Update product quantity
            $update_sql = "UPDATE products SET quantity = quantity - $quantity WHERE id = $product_id";
            $conn->query($update_sql);
        }

        // Create transaction record
        $transaction_sql = "INSERT INTO transactions (order_id, amount, status) VALUES ($order_id, $total, 'success')";
        $conn->query($transaction_sql);

        // Empty cart
        $empty_cart_sql = "DELETE FROM cart WHERE user_id = $user_id";
        $conn->query($empty_cart_sql);

        $conn->commit();
        header('Location: /order/confirmation.php?order_id=' . $order_id);
    } catch (Exception $e) {
        $conn->rollback();
        echo "Payment failed: " . $e->getMessage();
    }
}