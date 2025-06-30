<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header('Location: ../users/login.php');
    exit;
}

// Check if product ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: manage.php');
    exit();
}

$product_id = (int)$_GET['id'];
$seller_id = $_SESSION['user_id'];

try {
    // Get product image before deletion
    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ? AND seller_id = ?");
    $stmt->execute([$product_id, $seller_id]);
    $product = $stmt->fetch();

    if (!$product) {
        throw new Exception('Product not found or you do not have permission to delete it.');
    }

    // Delete product
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ? AND seller_id = ?");
    $stmt->execute([$product_id, $seller_id]);

    // Delete product image if exists
    if (!empty($product['image'])) {
        $image_path = '../uploads/products/' . $product['image'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }

    $_SESSION['success'] = 'Product deleted successfully!';
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
}

header('Location: manage.php');
exit();
