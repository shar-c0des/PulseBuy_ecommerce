<?php
session_start();
include_once '../config/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

if (isset($_GET['id'])) {
    $cart_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    $sql = "DELETE FROM cart WHERE id = $cart_id AND user_id = $user_id";
    if ($conn->query($sql) === TRUE) {
        header('Location: /cart/view.php');
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}