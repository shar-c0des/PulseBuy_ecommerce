<?php
// File: orders/view.php

session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../users/login.php');
    exit;
}

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Validate ownership
if ($role === 'buyer') {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$order_id, $user_id]);
} elseif ($role === 'seller') {
    $stmt = $pdo->prepare("SELECT o.*, u.username as buyer_username FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        JOIN users u ON o.user_id = u.id
        WHERE o.id = ? AND p.seller_id = ?
        GROUP BY o.id"); // Group by order.id to avoid duplicate orders if one order has multiple items from the same seller
    $stmt->execute([$order_id, $user_id]);
} else {
     // Admin case or other roles if applicable - currently just checks order ID
     // Consider adding appropriate checks for admin role if needed
    $stmt = $pdo->prepare("SELECT o.*, u.username as buyer_username FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
    $stmt->execute([$order_id]);
}


$order = $stmt->fetch();
if (!$order) {
    // Use a simple styled error message
    echo '<div style="font-family: sans-serif; text-align: center; padding: 40px; color: #F53F3F;">Order not found or access denied.</div>';
    exit;
}

// Fetch items for the order
$itemStmt = $pdo->prepare("SELECT p.name, p.image, oi.quantity, oi.price, p.id as product_id FROM order_items oi
    JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$itemStmt->execute([$order_id]);
$items = $itemStmt->fetchAll();

// Fetch address for the buyer of this order
$addrStmt = $pdo->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY id DESC LIMIT 1");
$addrStmt->execute([$order['user_id']]);
$address = $addrStmt->fetch();

// Get seller information for the header if buyer is viewing
$seller_info = null;
if ($role === 'buyer') {
    $sellerStmt = $pdo->prepare("SELECT username, avatar FROM users WHERE id = ?");
    // Assuming the seller ID can be retrieved from one of the order items' products
    // This might need adjustment depending on your DB schema if a single order can have multiple sellers
    if (!empty($items)) {
        $first_product_id = $items[0]['product_id'];
        $sellerIdStmt = $pdo->prepare("SELECT seller_id FROM products WHERE id = ? LIMIT 1");
        $sellerIdStmt->execute([$first_product_id]);
        $seller_id_for_buyer_view = $sellerIdStmt->fetchColumn();
        if ($seller_id_for_buyer_view) {
            $sellerStmt->execute([$seller_id_for_buyer_view]);
            $seller_info = $sellerStmt->fetch(PDO::FETCH_ASSOC);
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo $order_id; ?> Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#FF4400', // Taobao Primary
                        secondary: '#FFB800', // Taobao Secondary (Example, adjust as needed)
                        dark: '#1D2129', // Dark text/elements
                        light: '#F7F8FA', // Light backgrounds
                        success: '#00B42A',
                        warning: '#FF7D00',
                        danger: '#F53F3F',
                        info: '#165DFF', // Adjusted info color for better contrast
                         'primary-light': '#FFF0EB',
                         'success-light': '#F0FFF4',
                         'warning-light': '#FFF7E6',
                         'danger-light': '#FFF2F0',
                         'info-light': '#E8F3FF',
                    },
                    fontFamily: {
                        sans: ['Inter', 'PingFang SC', 'Helvetica Neue', 'Arial', 'sans-serif']
                    },
                     boxShadow: {
                        sm: '0 1px 3px 0 rgba(0, 0, 0, 0.08)',
                        md: '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
                        lg: '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
                        xl: '0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)',
                    }
                }
            }
        }
    </script>
    <style type="text/tailwindcss">
        @layer utilities {
            .content-auto {
                content-visibility: auto;
            }
            .form-input-focus {
                @apply focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none;
            }
            .btn-hover {
                @apply hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300;
            }
             .sidebar-item {
                @apply flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-primary-light hover:text-primary transition-colors duration-200;
            }
            .sidebar-item.active {
                @apply bg-primary-light text-primary font-semibold;
            }
             .card {
                @apply bg-white rounded-xl shadow-sm p-6 border border-gray-100;
            }
            .badge {
                @apply px-2 py-0.5 rounded-full text-xs font-medium;
            }
            .badge-primary {
                @apply bg-primary-light text-primary;
            }
            .badge-success {
                @apply bg-success-light text-success;
            }
            .badge-warning {
                @apply bg-warning-light text-warning;
            }
            .badge-danger {
                @apply bg-danger-light text-danger;
            }
             .badge-info {
                @apply bg-info-light text-info;
            }
             .table-header {
                @apply text-left text-xs font-semibold text-gray-500 uppercase tracking-wider pb-4 pr-4;
            }
             .table-cell {
                @apply py-3 pr-4 text-sm;
            }
            .table-row {
                 @apply border-b border-gray-100 last:border-b-0 hover:bg-gray-50 transition-colors duration-150;
            }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col font-sans text-gray-800 antialiased">

    <!-- Top Navigation -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <i class="fa fa-shopping-bag text-primary text-2xl"></i>
                <span class="text-xl font-bold text-dark">Taobao
                    <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                     <i class="fa fa-angle-down text-gray-400 group-hover:text-gray-600 transition-colors text-sm"></i>
                </div>
                 <a href="../users/logout.php" class="text-gray-600 hover:text-primary transition-colors text-sm font-medium">Logout</a>
            </div>
        </div>
    </header>

     <!-- Main Content Area -->
    <div class="flex flex-1 bg-gray-100">
        <main class="flex-1 p-6 bg-gray-100">
            <div class="max-w-7xl mx-auto">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-800">Order #<?php echo $order_id; ?> Details</h1>
                    <p class="text-gray-500 mt-2">View the details of this order.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Order Summary Card -->
                    <div class="card md:col-span-2">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Order Summary</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gray-500">Order ID:</p>
                                <p class="font-medium text-gray-900">#<?php echo $order_id; ?></p>
                            </div>
                            <div>
                                <p class="text-gray-500">Status:</p>
                                <?php
                                    $statusClass = [
                                        'pending' => 'badge-warning',
                                        'processing' => 'badge-primary',
                                        'shipped' => 'badge-info',
                                        'delivered' => 'badge-success',
                                        'cancelled' => 'badge-danger'
                                    ][$order['status']] ?? 'badge-info';
                                ?>
                                <span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst($order['status']); ?></span>
                            </div>
                            <div>
                                <p class="text-gray-500">Order Date:</p>
                                <p class="font-medium text-gray-900"><?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-500">Total Amount:</p>
                                <p class="text-lg font-bold text-primary">R<?php echo number_format($order['total'], 2); ?></p>
                            </div>
                             <?php if ($role === 'seller' && isset($order['buyer_username'])): ?>
                                <div>
                                    <p class="text-gray-500">Buyer:</p>
                                    <p class="font-medium text-gray-900"><?php echo htmlspecialchars($order['buyer_username']); ?></p>
                                </div>
                             <?php endif; ?>
                        </div>
                    </div>

                    <!-- Shipping Address Card -->
                    <div class="card">
                         <h3 class="text-lg font-semibold text-gray-800 mb-4">Shipping Address</h3>
                         <?php if ($address): ?>
                             <div class="text-sm text-gray-700">
                                 <p class="font-medium mb-1"><?php echo htmlspecialchars($address['recipient_name']); ?></p>
                                 <p><?php echo htmlspecialchars($address['street']); ?></p>
                                 <p><?php echo htmlspecialchars($address['city']) . ", " . htmlspecialchars($address['postal_code']); ?></p>
                                 <p><?php echo htmlspecialchars($address['country']); ?></p>
                                 <p class="mt-2"><i class="fa fa-phone text-gray-400 mr-2"></i><?php echo htmlspecialchars($address['phone_number']); ?></p>
                             </div>
                         <?php else: ?>
                            <p class="text-gray-500 text-sm">No address found for this order.</p>
                         <?php endif; ?>
                    </div>
                </div>

                <!-- Order Items Table -->
                <div class="card mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Order Items</h3>
                     <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead>
                                <tr>
                                    <th class="table-header">Product</th>
                                    <th class="table-header">Price</th>
                                    <th class="table-header text-center">Quantity</th>
                                    <th class="table-header text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php foreach ($items as $item): ?>
                                    <tr class="table-row">
                                        <td class="table-cell">
                                            <div class="flex items-center gap-3">
                                                 <img src="<?php echo htmlspecialchars($item['image'] ?? '../assets/images/no-image.png'); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-14 h-14 rounded-lg object-cover shadow-sm">
                                                <div>
                                                     <p class="font-medium text-gray-900"><?php echo htmlspecialchars($item['name']); ?></p>
                                                     <!-- Optional: Add a link to the product page if applicable -->
                                                     <!-- <a href="../products/view.php?id=<?php echo $item['product_id']; ?>" class="text-primary hover:underline text-sm mt-1 inline-block">View Product</a> -->
                                                </div>
                                            </div>
                                        </td>
                                        <td class="table-cell text-gray-700">R<?php echo number_format($item['price'], 2); ?></td>
                                        <td class="table-cell text-center text-gray-700"><?php echo $item['quantity']; ?></td>
                                        <td class="table-cell text-right font-semibold text-gray-900">R<?php echo number_format($item['quantity'] * $item['price'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($items)): ?>
                                    <tr>
                                        <td colspan="4" class="table-cell text-center text-gray-500 py-4">No items found for this order.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                     </div>
                </div>

                <!-- Back Button -->
                <div class="text-center">
                    <a href="<?php echo ($role === 'seller') ? 'list.php' : '../buyer/orders.php'; ?>" class="inline-block bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-medium hover:bg-gray-300 transition-colors">
                        <i class="fa fa-arrow-left mr-2"></i> Back to Orders
                    </a>
                </div>

            </div>
        </main>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-12">
        <div class="container mx-auto px-4 text-center">
            <p class="text-gray-400 text-sm">&copy; <?php echo date('Y'); ?> Taobao.com All rights reserved.</p>
            <p class="text-gray-500 text-xs mt-2">Seller service hotline: 400-800-8888</p>
        </div>
    </footer>

</body>
</html>
