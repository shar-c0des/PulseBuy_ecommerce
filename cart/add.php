<?php
session_start();
include_once '../config/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $user_id = $_SESSION['user_id'];

    $sql = "SELECT id FROM cart WHERE user_id = $user_id AND product_id = $product_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $cart_id = $row['id'];
        $sql = "UPDATE cart SET quantity = quantity + $quantity WHERE id = $cart_id";
    } else {
        $sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES ($user_id, $product_id, $quantity)";
    }

    if ($conn->query($sql) === TRUE) {
        header('Location: /cart/view.php');
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}