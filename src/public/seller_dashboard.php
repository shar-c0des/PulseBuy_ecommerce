<?php
// Start the session
session_start();

// Redirect to login if not logged in as seller
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header('Location: loginSignup.php');
    exit;
}

// Initialize message variables
$product_message = '';
$product_message_type = '';

// Include database configuration
require_once '../../config/db.php'; // Assumes $pdo is defined here

// Fetch categories for the form
try {
    $catStmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
    $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
    error_log('Error fetching categories: ' . $e->getMessage());
}

// Handle product addition
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])
) {
    $category_id = $_POST['category_id'] ?? '';
    $product_title = $_POST['product_name'] ?? '';
    $price = is_numeric($_POST['price'] ?? 0) ? (float)$_POST['price'] : 0;
    $description = $_POST['description'] ?? '';
    $image_path = '';
    $errors = [];
    if (empty($product_title)) $errors[] = 'Product name is required';
    if (empty($category_id)) $errors[] = 'Category is required';
    if ($price <= 0) $errors[] = 'Price must be greater than 0';

    // Handle image upload
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['product_image']['tmp_name'];
        $fileName = $_FILES['product_image']['name'];
        $fileSize = $_FILES['product_image']['size'];
        $fileNameCmps = explode('.', $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($fileExtension, $allowedfileExtensions)) {
            $errors[] = 'Only image files (jpg, jpeg, png, gif, webp) are allowed.';
        } elseif ($fileSize > 2 * 1024 * 1024) {
            $errors[] = 'Image size must be less than 2MB.';
        } else {
            $newFileName = uniqid('prod_', true) . '.' . $fileExtension;
            $uploadFileDir = __DIR__ . '/../uploads/products/';
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0777, true);
            }
            $dest_path = $uploadFileDir . $newFileName;
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $image_path = 'src/uploads/products/' . $newFileName;
            } else {
                $errors[] = 'There was an error uploading the image.';
            }
        }
    }

    if (!empty($errors)) {
        $product_message = implode('<br>', $errors);
        $product_message_type = 'danger';
    } else {
        try {
            $sql = "INSERT INTO products (title, description, price, category_id, user_id, status, image_path, created_at, updated_at)
                    VALUES (:title, :description, :price, :category_id, :user_id, :status, :image_path, NOW(), NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':title' => $product_title,
                ':description' => $description,
                ':price' => $price,
                ':category_id' => $category_id,
                ':user_id' => $_SESSION['user_id'],
                ':status' => 'active',
                ':image_path' => $image_path
            ]);
            $product_message = 'Product added successfully!';
            $product_message_type = 'success';
        } catch (PDOException $e) {
            $product_message = 'Database error: Please try again later.';
            $product_message_type = 'danger';
            error_log('Product addition error: ' . $e->getMessage());
        }
    }
}

// Handle product deletion
if (isset($_POST['delete_product_id'])) {
    $delete_id = (int)$_POST['delete_product_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id AND user_id = :user_id");
        $stmt->execute([':id' => $delete_id, ':user_id' => $_SESSION['user_id']]);
        $product_message = 'Product deleted successfully!';
        $product_message_type = 'success';
    } catch (PDOException $e) {
        $product_message = 'Error deleting product.';
        $product_message_type = 'danger';
        error_log('Product deletion error: ' . $e->getMessage());
    }
}

// Check for success message from edit page
if (isset($_SESSION['success'])) {
    $product_message = $_SESSION['success'];
    $product_message_type = 'success';
    unset($_SESSION['success']);
}

// Fetch products with category names
try {
    $sql = "SELECT p.id, p.title, p.description, p.price, c.name as category_name, p.status, p.image_path, p.created_at, p.updated_at 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.user_id = :user_id 
            ORDER BY p.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $products = [];
    error_log('Error fetching products: ' . $e->getMessage());
}

// Dashboard stats
$total_products = count($products);
$active_products = count(array_filter($products, function($p){ return $p['status']==='active'; }));
$total_categories = count($categories);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PulseBuy Seller Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #0056E0;
            --secondary-blue: #1A75FF;
            --accent-green: #00C853;
            --accent-yellow: #FFC107;
            --background: #F5F7FA;
            --white: #fff;
            --gray: #e5e9ef;
            --dark: #22252A;
        }
        body {
            background: var(--background);
            font-family: 'Poppins', sans-serif;
            margin: 0;
            color: var(--dark);
            font-size: 15px;
        }
        .top-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 40px;
            height: 70px;
            background: var(--white);
            border-bottom: 1px solid var(--gray);
            position: sticky;
            top: 0;
            z-index: 100;
            flex-wrap: wrap;
        }
        .nav-left {
            display: flex;
            align-items: center;
            gap: 30px;
            min-width: 0;
        }
        .brand-logo {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-blue);
            letter-spacing: -1px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .brand-logo i {
            color: var(--accent-yellow);
            font-size: 2rem;
        }
        .nav-links {
            display: flex;
            gap: 22px;
        }
        .nav-link {
            color: var(--dark);
            text-decoration: none;
            font-weight: 500;
            font-size: 1rem;
            padding: 8px 0;
            border-bottom: 2px solid transparent;
            transition: border 0.2s, color 0.2s;
        }
        .nav-link.active, .nav-link:hover {
            color: var(--primary-blue);
            border-bottom: 2px solid var(--primary-blue);
        }
        .search-bar {
            flex: 1 1 200px;
            min-width: 180px;
            max-width: 400px;
            margin: 0 40px;
            position: relative;
            display: flex;
        }
        .search-bar input {
            width: 100%;
            padding: 10px 40px 10px 16px;
            border-radius: 20px;
            border: 1px solid var(--gray);
            font-size: 1rem;
            background: var(--background);
        }
        .search-bar i {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-blue);
            font-size: 1.1rem;
        }
        .nav-right {
            display: flex;
            align-items: center;
            gap: 22px;
            min-width: 0;
            flex-shrink: 0;
        }
        .nav-icon {
            font-size: 1.4rem;
            color: var(--primary-blue);
            margin-right: 2px;
        }
        .user-name {
            font-weight: 500;
            color: var(--primary-blue);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 180px;
            font-size: 1rem;
            line-height: 1.2;
            vertical-align: middle;
        }
        .greeting-banner {
            background: linear-gradient(90deg, var(--primary-blue), var(--secondary-blue));
            color: var(--white);
            padding: 18px 0 12px 0;
            text-align: center;
            font-size: 1.05rem;
            font-weight: 500;
            letter-spacing: 0.5px;
        }
        .greeting-banner .username {
            font-weight: 700;
            color: var(--accent-yellow);
        }
        .dashboard-cards {
            display: flex;
            gap: 18px;
            margin: 24px auto 0 auto;
            max-width: 1200px;
            flex-wrap: wrap;
        }
        .dashboard-card {
            flex: 1;
            min-width: 180px;
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            padding: 18px 14px;
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .dashboard-card .icon {
            font-size: 1.5rem;
        }
        .dashboard-card .icon.blue { color: var(--primary-blue); }
        .dashboard-card .icon.green { color: var(--accent-green); }
        .dashboard-card .icon.yellow { color: var(--accent-yellow); }
        .dashboard-card .label {
            font-size: 0.98rem;
            color: #666;
        }
        .dashboard-card .value {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark);
        }
        .products-section {
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            margin: 24px auto;
            max-width: 1200px;
            padding: 20px 12px;
        }
        .product-table {
            font-size: 0.97rem;
        }
        .product-table th, .product-table td {
            padding: 8px 6px;
        }
        .edit-btn, .delete-btn {
            transition: background 0.18s, color 0.18s, box-shadow 0.18s;
            vertical-align: middle;
        }
        .edit-btn:hover {
            background: var(--primary-blue) !important;
            color: #fff !important;
            box-shadow: 0 2px 8px rgba(0,86,224,0.10);
        }
        .delete-btn:hover {
            background: #c62828 !important;
            color: #fff !important;
            box-shadow: 0 2px 8px rgba(244,67,54,0.10);
        }
        /* Responsive */
        @media (max-width: 900px) {
            .dashboard-cards { flex-direction: column; gap: 16px; }
            .dashboard-card { min-width: 0; }
            .top-nav { flex-direction: column; height: auto; padding: 0 10px; }
            .search-bar { margin: 10px 0; max-width: 100%; min-width: 120px; }
            .nav-right { margin-top: 10px; }
        }
        @media (max-width: 600px) {
            .top-nav { flex-direction: column; height: auto; padding: 0 10px; }
            .search-bar { margin: 10px 0; max-width: 100%; min-width: 80px; }
            .dashboard-cards, .products-section { padding: 16px 4px; }
            .user-name { max-width: 80px; }
        }
        .sparky-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 999;
            display: flex;
            align-items: flex-end;
        }
        .sparky-bubble {
            background-color: white;
            border-radius: 15px;
            padding: 15px;
            max-width: 250px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-right: 20px;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.5s ease;
            position: relative;
        }
        .sparky-bubble::after {
            content: '';
            position: absolute;
            right: -10px;
            bottom: 20px;
            border-width: 10px 0 0 10px;
            border-style: solid;
            border-color: transparent transparent transparent white;
        }
        .sparky-bubble.show {
            opacity: 1;
            transform: translateY(0);
        }
        .sparky {
            width: 100px;
            height: 100px;
            background-color: white;
            border-radius: 50%;
            position: relative;
            box-shadow: 0 0 15px rgba(0, 102, 204, 0.3);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .sparky:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(0, 102, 204, 0.5);
        }
        .ear {
            width: 10px;
            height: 40px;
            background-color: white;
            border: 1.5px solid #0056E0;
            border-radius: 50%;
            position: absolute;
            top: -15px;
        }
        .ear.left {
            left: 20px;
            transform: rotate(-15deg);
        }
        .ear.right {
            right: 20px;
            transform: rotate(15deg);
        }
        .eye {
            width: 10px;
            height: 10px;
            background-color: #0056E0;
            border-radius: 50%;
            position: absolute;
            top: 30px;
        }
        .eye.left {
            left: 25px;
        }
        .eye.right {
            right: 25px;
        }
        .nose {
            width: 5px;
            height: 5px;
            background-color: #FFC107;
            border-radius: 50%;
            position: absolute;
            top: 45px;
            left: 47.5px;
        }
        .mouth {
            width: 10px;
            height: 5px;
            border-bottom: 1.5px solid #FFC107;
            border-radius: 50%;
            position: absolute;
            top: 50px;
            left: 45px;
        }
        .vest {
            width: 40px;
            height: 30px;
            background-color: #0056E0;
            position: absolute;
            top: 60px;
            left: 30px;
            border-radius: 5px;
        }
        .lightning-bolt {
            width: 10px;
            height: 15px;
            background-color: #FFC107;
            clip-path: polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%);
            position: absolute;
            top: 67px;
            left: 45px;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 #FFC10744; }
            70% { box-shadow: 0 0 0 10px #FFC10700; }
            100% { box-shadow: 0 0 0 0 #FFC10700; }
        }
        .tail {
            width: 15px;
            height: 15px;
            background-color: white;
            border: 1.5px solid #0056E0;
            border-top: none;
            border-right: none;
            border-radius: 50%;
            position: absolute;
            bottom: 10px;
            right: 10px;
            transform: rotate(45deg);
        }
        /* Toast styles */
        .toast {
            position: fixed;
            top: 32px;
            right: 32px;
            background: var(--primary-blue);
            color: #fff;
            padding: 16px 32px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            box-shadow: 0 4px 16px rgba(0,86,224,0.10);
            opacity: 0;
            pointer-events: none;
            z-index: 2000;
            transition: opacity 0.4s, transform 0.4s;
            transform: translateY(-20px);
        }
        .toast.show {
            opacity: 1;
            pointer-events: auto;
            transform: translateY(0);
        }
        .dashboard-card.clickable { cursor: pointer; box-shadow: 0 2px 12px rgba(0,86,224,0.08); transition: box-shadow 0.2s, background 0.2s; }
        .dashboard-card.clickable:hover { background: var(--gray); box-shadow: 0 4px 18px rgba(0,86,224,0.13); }
        .mini-sparky { position: relative; width: 36px; height: 36px; animation: float 2.2s ease-in-out infinite; }
        .mini-sparky .ear {
            position: absolute;
            background: #fff;
            border: 1px solid #0056E0;
            border-radius: 50%;
            z-index: 2;
        }
        .mini-sparky .ear.left { transform: rotate(-15deg); }
        .mini-sparky .ear.right { transform: rotate(15deg); }
        .mini-sparky .mini-face { border: 1px solid #0056E0; }
        .mini-sparky .eye {
            position: absolute;
            background: #0056E0;
            border-radius: 50%;
            top: 16px;
            width: 3px; height: 3px;
        }
        .mini-sparky .eye.left { left: 13px; }
        .mini-sparky .eye.right { right: 13px; }
        .mini-sparky .nose {
            position: absolute;
            background: #FFC107;
            border-radius: 50%;
            width: 2px; height: 2px;
            left: 17px; top: 21px;
        }
        .mini-sparky .mouth {
            position: absolute;
            border-bottom: 1px solid #FFC107;
            border-radius: 50%;
            width: 6px; height: 2px;
            left: 15px; top: 23px;
        }
        .mini-sparky .vest {
            position: absolute;
            background: #0056E0;
            border-radius: 2px;
            width: 12px; height: 7px;
            left: 12px; top: 23px;
        }
        .mini-sparky .lightning-bolt {
            position: absolute;
            background: #FFC107;
            clip-path: polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%);
            width: 4px; height: 7px;
            left: 17px; top: 26px;
            animation: pulse 2s infinite;
        }
        @keyframes float {
            0% { transform: translateY(0); }
            50% { transform: translateY(-4px); }
            100% { transform: translateY(0); }
        }
    </style>
</head>
<body>
    <!-- Toast Notification -->
    <div id="toast" class="toast">
        <span id="toast-sparky" style="display:inline-block;vertical-align:middle;margin-right:12px;">
            <span class="mini-sparky" style="display:inline-block;position:relative;width:36px;height:36px;vertical-align:middle;">
                <span class="ear left" style="width:4px;height:16px;left:7px;top:-7px;"></span>
                <span class="ear right" style="width:4px;height:16px;right:7px;top:-7px;"></span>
                <span class="mini-face" style="position:absolute;left:8px;top:8px;width:20px;height:20px;background:#fff;border-radius:50%;box-shadow:0 0 6px #0056e033;"></span>
                <span class="eye left" style="width:3px;height:3px;left:13px;top:16px;"></span>
                <span class="eye right" style="width:3px;height:3px;right:13px;top:16px;"></span>
                <span class="nose" style="width:2px;height:2px;left:17px;top:21px;"></span>
                <span class="mouth" style="width:6px;height:2px;left:15px;top:23px;"></span>
                <span class="vest" style="width:12px;height:7px;left:12px;top:23px;"></span>
                <span class="lightning-bolt" style="width:4px;height:7px;left:17px;top:26px;"></span>
            </span>
        </span>
        <span id="toast-msg"></span>
    </div>
    <!-- Top Navigation -->
    <nav class="top-nav">
        <div class="nav-left">
            <span class="brand-logo"><i class="fas fa-bolt"></i> PulseBuy</span>
            <div class="nav-links">
                <a href="#" class="nav-link active">Dashboard</a>
                <a href="#" class="nav-link">Products</a>
                <a href="#" class="nav-link">Orders</a>
                <a href="#" class="nav-link">Analytics</a>
            </div>
        </div>
        <div class="search-bar">
            <input type="text" placeholder="Search products, orders...">
            <i class="fas fa-search"></i>
        </div>
        <div class="nav-right">
            <span class="user-name"><?= htmlspecialchars($_SESSION['username']) ?></span>
            <a href="logout.php" class="nav-link" style="color:var(--primary-blue);font-weight:600;">Log Out</a>
        </div>
    </nav>
    <!-- Greeting Banner -->
    <div class="greeting-banner">
        Welcome, <span class="username"><?= htmlspecialchars($_SESSION['username']) ?></span>! Here's your seller dashboard.
    </div>
    <!-- Dashboard Cards -->
    <div class="dashboard-cards">
        <div class="dashboard-card clickable" data-filter="all"><span class="icon blue"><i class="fas fa-box"></i></span><div><div class="label">Total Products</div><div class="value"><?= $total_products ?></div></div></div>
        <div class="dashboard-card clickable" data-filter="active"><span class="icon green"><i class="fas fa-check-circle"></i></span><div><div class="label">Active Products</div><div class="value"><?= $active_products ?></div></div></div>
        <div class="dashboard-card clickable" data-filter="category"><span class="icon yellow"><i class="fas fa-list"></i></span><div><div class="label">Categories</div><div class="value"><?= $total_categories ?></div></div></div>
    </div>
    <!-- Products Section -->
    <div class="products-section">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h2 style="font-size: 1.6rem; font-weight: 700; color: var(--primary-blue); margin: 0;">Manage Products</h2>
            <a href="../../products/add.php" style="background: var(--primary-blue); border: 1.5px solid var(--primary-blue); color: #fff; border-radius: 6px; padding: 12px 28px; font-size: 1rem; font-weight: 600; cursor: pointer; box-shadow: 0 2px 8px rgba(0,86,224,0.08); transition: background 0.2s, border 0.2s; text-decoration: none; display: inline-block;" onmouseover="this.style.background='var(--secondary-blue)';this.style.borderColor='var(--secondary-blue)';" onmouseout="this.style.background='var(--primary-blue)';this.style.borderColor='var(--primary-blue)';">+ Add Product</a>
        </div>
        <?php if ($product_message): ?>
            <div class="message <?= $product_message_type ?>"><?= $product_message ?></div>
        <?php endif; ?>
        <div style="overflow-x:auto;">
        <table class="product-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8f9fa;">
                    <th>ID</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Image</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr style="border-bottom: 1px solid #dee2e6;">
                        <td><?= $product['id'] ?></td>
                        <td><?= htmlspecialchars($product['title']) ?></td>
                        <td><?= htmlspecialchars($product['category_name'] ?? 'N/A') ?></td>
                        <td>R<?= number_format($product['price'], 2) ?></td>
                        <td>
                            <span style="padding: 4px 8px; border-radius: 4px; font-size: 12px; background: <?= $product['status'] === 'active' ? '#d4edda' : '#f8d7da' ?>; color: <?= $product['status'] === 'active' ? '#155724' : '#721c24' ?>;">
                                <?= ucfirst($product['status'] ?? 'inactive') ?>
                            </span>
                        </td>
                        <td>
                            <?php if (!empty($product['image_path'])): ?>
                                <img src="/<?= htmlspecialchars($product['image_path']) ?>" alt="Product Image" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px;">
                            <?php else: ?>
                                <span style="color: #bbb;">No image</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('Y-m-d H:i', strtotime($product['created_at'])) ?></td>
                        <td><?= date('Y-m-d H:i', strtotime($product['updated_at'])) ?></td>
                        <td>
                            <a href="../../products/edit.php?id=<?= $product['id'] ?>" class="edit-btn" style="background: var(--secondary-blue); color: #fff; border: none; border-radius: 4px; padding: 6px 14px; font-size: 0.95rem; font-weight: 500; margin-right: 6px; cursor: pointer; text-decoration: none; display: inline-block; transition: all 0.2s ease;" onmouseover="this.style.background='var(--primary-blue)';this.style.transform='translateY(-1px)';" onmouseout="this.style.background='var(--secondary-blue)';this.style.transform='translateY(0)';" onclick="console.log('Edit button clicked for product ID: <?= $product['id'] ?>');">Edit</a>
                            <form method="POST" style="display: inline;" class="delete-form">
                                <input type="hidden" name="delete_product_id" value="<?= $product['id'] ?>">
                                <button type="submit" name="delete_product" class="delete-btn" style="background: #F44336; color: #fff; border: none; border-radius: 4px; padding: 6px 14px; font-size: 0.95rem; font-weight: 500; cursor: pointer;">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php if (empty($products)): ?>
            <p style="text-align: center; color: #6c757d; margin-top: 20px;">No products found. Add your first product above!</p>
        <?php endif; ?>
    </div>
    <!-- Edit Product Modal -->
    <div id="editProductModal" class="modal" style="display:none;">
        <!-- Modal content removed as edit is now on a separate page -->
    </div>
    <!-- Sparky Bunny Assistant -->
    <div class="sparky-container flex items-center">
        <div class="sparky-bubble show">
            <p class="text-sm text-gray-700">Hi, I'm Sparky! Need help managing your products? Click me for tips and support!</p>
        </div>
        <div class="sparky animate-float">
            <div class="ear left"></div>
            <div class="ear right"></div>
            <div class="eye left"></div>
            <div class="eye right"></div>
            <div class="nose"></div>
            <div class="mouth"></div>
            <div class="vest"></div>
            <div class="lightning-bolt"></div>
            <div class="tail"></div>
        </div>
    </div>
    <script>
    // Toast logic with fun animation
    function showToast(message) {
        const toast = document.getElementById('toast');
        const toastMsg = document.getElementById('toast-msg');
        toastMsg.textContent = message;
        toast.classList.add('show', 'bounce-in');
        setTimeout(() => { toast.classList.remove('show', 'bounce-in'); }, 3000);
    }
    // Add bounce-in animation
    const style = document.createElement('style');
    style.innerHTML = `
    .toast.bounce-in { animation: bounceIn 0.7s cubic-bezier(.23,1.01,.32,1); }
    @keyframes bounceIn {
      0% { opacity: 0; transform: scale(0.7) translateY(-40px); }
      60% { opacity: 1; transform: scale(1.1) translateY(10px); }
      80% { transform: scale(0.95) translateY(-4px); }
      100% { opacity: 1; transform: scale(1) translateY(0); }
    }`;
    document.head.appendChild(style);
    // Show toast if PHP sets a product_message
    <?php if ($product_message && $product_message_type === 'success'): ?>
    window.addEventListener('DOMContentLoaded', function() {
        showToast(<?= json_encode($product_message) ?>);
    });
    <?php endif; ?>
    // Delete confirmation
    Array.from(document.querySelectorAll('.delete-form')).forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to delete this product?')) {
                e.preventDefault();
            }
        });
    });
    // Dashboard card filtering
    const productRows = Array.from(document.querySelectorAll('.product-table tbody tr'));
    document.querySelectorAll('.dashboard-card.clickable').forEach(card => {
        card.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');
            productRows.forEach(row => {
                if (filter === 'all') {
                    row.style.display = '';
                } else if (filter === 'active') {
                    row.style.display = row.querySelector('td:nth-child(5) span').textContent.trim().toLowerCase() === 'active' ? '' : 'none';
                } else if (filter === 'category') {
                    // For now, show all (or you can implement category-specific filtering)
                    row.style.display = '';
                }
            });
        });
    });
    </script>
</body>
</html>