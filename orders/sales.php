<?php
// seller/sales.php

session_start();
require_once '../config/db.php';

// 会话检查
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header('Location: ../users/login.php');
    exit;
}

// 获取卖家信息
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$seller = $stmt->fetch(PDO::FETCH_ASSOC);

// 处理订单状态更新
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
    $orderId = $_POST['order_id'];
    $newStatus = $_POST['status'];
    
    // 验证订单是否属于该卖家
    $checkStmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ? AND p.user_id = ?
    ");
    $checkStmt->execute([$orderId, $_SESSION['user_id']]);
    
    if ($checkStmt->fetchColumn() > 0) {
        // 更新订单状态
        $updateStmt = $pdo->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
        $updateStmt->execute([$newStatus, $orderId]);
        
        // 设置成功消息
        $_SESSION['success_message'] = "订单状态已成功更新。";
    } else {
        $_SESSION['error_message'] = "您无权更新此订单。";
    }
    
    header("Location: sales.php");
    exit;
}

// 获取订单列表
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// 构建查询条件
$whereClause = "WHERE p.user_id = ?";
$params = [$_SESSION['user_id']];

if ($statusFilter !== 'all') {
    $whereClause .= " AND o.status = ?";
    $params[] = $statusFilter;
}

if (!empty($searchQuery)) {
    $whereClause .= " AND (o.id LIKE ? OR u.username LIKE ? OR p.name LIKE ?)";
    $searchParam = "%{$searchQuery}%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
}

// 获取订单总数（用于分页）
$countStmt = $pdo->prepare("
    SELECT COUNT(DISTINCT o.id) as total
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    JOIN users u ON o.user_id = u.id
    {$whereClause}
");
$countStmt->execute($params);
$totalOrders = $countStmt->fetchColumn();
$totalPages = ceil($totalOrders / $perPage);

// 获取订单列表（包含卖家的产品）
$ordersStmt = $pdo->prepare("
    SELECT 
        o.id as order_id,
        o.created_at,
        o.status,
        o.total,
        u.username as buyer_name,
        u.email as buyer_email,
        p.id as product_id,
        p.name as product_name,
        p.image as product_image,
        oi.quantity,
        oi.price as item_price
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    JOIN users u ON o.user_id = u.id
    {$whereClause}
    ORDER BY o.created_at DESC
    LIMIT ? OFFSET ?
");
$params[] = $perPage;
$params[] = $offset;
$ordersStmt->execute($params);
$orderItems = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);

// 处理订单数据，按订单ID分组
$orders = [];
foreach ($orderItems as $item) {
    $orderId = $item['order_id'];
    if (!isset($orders[$orderId])) {
        $orders[$orderId] = [
            'id' => $orderId,
            'created_at' => $item['created_at'],
            'status' => $item['status'],
            'total' => $item['total'],
            'buyer_name' => $item['buyer_name'],
            'buyer_email' => $item['buyer_email'],
            'items' => []
        ];
    }
    $orders[$orderId]['items'][] = [
        'product_id' => $item['product_id'],
        'product_name' => $item['product_name'],
        'product_image' => $item['product_image'],
        'quantity' => $item['quantity'],
        'item_price' => $item['item_price']
    ];
}

// 获取订单状态选项
$statusOptions = [
    'pending' => 'Pending Payment',
    'paid' => 'Paid',
    'shipped' => 'Shipped',
    'delivered' => 'Delivered',
    'cancelled' => 'Cancelled'
];

// 订单状态的CSS类映射
$statusClasses = [
    'pending' => 'text-amber-500',
    'paid' => 'text-blue-500',
    'shipped' => 'text-purple-500',
    'delivered' => 'text-green-500',
    'cancelled' => 'text-red-500'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Management - LocalTrader</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#FF4400', // 淘宝橙色
                        secondary: '#FFB800',
                        dark: '#333333',
                        light: '#F5F5F5',
                        success: '#00B42A',
                        warning: '#FF7D00',
                        danger: '#F53F3F',
                        info: '#86909C',
                        'taobao-orange': '#FF4400',
                        'tmall-red': '#FF0036',
                        'gray-light': '#F5F5F5',
                        'gray-medium': '#E5E5E5',
                        'gray-dark': '#666666'
                    },
                    fontFamily: {
                        inter: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style type="text/tailwindcss">
        @layer utilities {
            .content-auto {
                content-visibility: auto;
            }
            .taobao-header {
                @apply bg-taobao-orange text-white shadow-md;
            }
            .tmall-header {
                @apply bg-tmall-red text-white shadow-md;
            }
            .seller-nav {
                @apply bg-white border-b border-gray-medium;
            }
            .seller-nav-item {
                @apply px-4 py-3 text-gray-dark hover:text-taobao-orange font-medium transition-colors;
            }
            .seller-nav-item.active {
                @apply text-taobao-orange border-b-2 border-taobao-orange;
            }
            .order-card {
                @apply bg-white border border-gray-medium rounded-sm mb-4 transition-all hover:shadow-md;
            }
            .order-header {
                @apply bg-gray-light p-3 flex items-center justify-between border-b border-gray-medium;
            }
            .order-item {
                @apply p-3 flex items-center border-b border-gray-medium;
            }
            .order-footer {
                @apply p-3 flex items-center justify-between;
            }
            .status-badge {
                @apply px-2 py-1 rounded text-xs font-medium;
            }
            .btn {
                @apply px-4 py-1.5 rounded text-sm font-medium transition-all;
            }
            .btn-primary {
                @apply bg-taobao-orange text-white hover:bg-taobao-orange/90;
            }
            .btn-secondary {
                @apply bg-white border border-gray-medium text-gray-dark hover:bg-gray-light;
            }
            .btn-danger {
                @apply bg-danger text-white hover:bg-danger/90;
            }
        }
    </style>
</head>
<body class="bg-gray-light font-inter text-gray-dark min-h-screen flex flex-col">
    <!-- 顶部导航 -->
    <header class="taobao-header py-2">
        <div class="container mx-auto px-4 flex items-center justify-between">
            <div class="flex items-center">
                <a href="#" class="text-white font-bold text-xl mr-6">LocalTrader</a>
                <div class="hidden md:flex space-x-4">
                    <a href="#" class="text-white/80 hover:text-white transition-colors">Home</a>
                    <a href="#" class="text-white/80 hover:text-white transition-colors">Marketplace</a>
                    <a href="#" class="text-white/80 hover:text-white transition-colors">Categories</a>
                    <a href="#" class="text-white/80 hover:text-white transition-colors">Help Center</a>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <a href="#" class="text-white/80 hover:text-white transition-colors">
                    <i class="fa fa-shopping-cart mr-1"></i> Cart
                </a>
                <a href="#" class="text-white/80 hover:text-white transition-colors">
                    <i class="fa fa-bell mr-1"></i> Notifications
                </a>
                <div class="relative group">
                    <button class="flex items-center text-white/80 hover:text-white transition-colors">
                        <i class="fa fa-user-circle mr-1"></i>
                        <span class="hidden md:inline">Seller Center</span>
                        <i class="fa fa-angle-down ml-1"></i>
                    </button>
                    <div class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10 group-hover:block transition-all">
                        <a href="dashboard.php" class="block px-4 py-2 text-sm text-gray-dark hover:bg-gray-light">Dashboard</a>
                        <a href="products.php" class="block px-4 py-2 text-sm text-gray-dark hover:bg-gray-light">Manage Products</a>
                        <a href="sales.php" class="block px-4 py-2 text-sm text-taobao-orange hover:bg-gray-light">Sales Management</a>
                        <a href="analytics.php" class="block px-4 py-2 text-sm text-gray-dark hover:bg-gray-light">Analytics</a>
                        <div class="border-t border-gray-light my-1"></div>
                        <a href="../users/logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-light">Sign Out</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- 卖家中心导航 -->
    <nav class="seller-nav">
        <div class="container mx-auto px-4">
            <ul class="flex">
                <li><a href="dashboard.php" class="seller-nav-item">Dashboard</a></li>
                <li><a href="products.php" class="seller-nav-item">Products</a></li>
                <li><a href="sales.php" class="seller-nav-item active">Sales</a></li>
                <li><a href="analytics.php" class="seller-nav-item">Analytics</a></li>
                <li><a href="marketing.php" class="seller-nav-item">Marketing</a></li>
                <li><a href="customer-service.php" class="seller-nav-item">Customer Service</a></li>
                <li><a href="settings.php" class="seller-nav-item">Settings</a></li>
            </ul>
        </div>
    </nav>

    <!-- 主内容区 -->
    <main class="flex-1 container mx-auto px-4 py-6">
        <!-- 页面标题 -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Sales Management</h1>
            <p class="text-gray-dark mt-1">Manage and process your orders</p>
        </div>
        
        <!-- 消息提示 -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md relative mb-6" role="alert">
                <div class="flex items-center">
                    <i class="fa fa-check-circle mr-2"></i>
                    <strong class="font-bold">Success!</strong>
                    <span class="ml-2"><?php echo htmlspecialchars($_SESSION['success_message']); ?></span>
                </div>
                <button class="absolute top-0 right-0 px-4 py-3 text-green-400 hover:text-green-600" onclick="this.parentElement.style.display='none'">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md relative mb-6" role="alert">
                <div class="flex items-center">
                    <i class="fa fa-exclamation-circle mr-2"></i>
                    <strong class="font-bold">Error!</strong>
                    <span class="ml-2"><?php echo htmlspecialchars($_SESSION['error_message']); ?></span>
                </div>
                <button class="absolute top-0 right-0 px-4 py-3 text-red-400 hover:text-red-600" onclick="this.parentElement.style.display='none'">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        
        <!-- 筛选和搜索 -->
        <div class="bg-white rounded-sm border border-gray-medium mb-6">
            <div class="p-4">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                    <div class="flex flex-wrap gap-2">
                        <a href="sales.php?status=all" class="px-3 py-1.5 rounded text-sm font-medium 
                            <?php echo ($statusFilter === 'all') ? 'bg-taobao-orange text-white' : 'bg-white border border-gray-medium text-gray-dark hover:bg-gray-light'; ?>">
                            All Orders
                        </a>
                        <a href="sales.php?status=pending" class="px-3 py-1.5 rounded text-sm font-medium 
                            <?php echo ($statusFilter === 'pending') ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-white border border-gray-medium text-gray-dark hover:bg-gray-light'; ?>">
                            Pending
                        </a>
                        <a href="sales.php?status=paid" class="px-3 py-1.5 rounded text-sm font-medium 
                            <?php echo ($statusFilter === 'paid') ? 'bg-blue-100 text-blue-800 border border-blue-200' : 'bg-white border border-gray-medium text-gray-dark hover:bg-gray-light'; ?>">
                            Paid
                        </a>
                        <a href="sales.php?status=shipped" class="px-3 py-1.5 rounded text-sm font-medium 
                            <?php echo ($statusFilter === 'shipped') ? 'bg-purple-100 text-purple-800 border border-purple-200' : 'bg-white border border-gray-medium text-gray-dark hover:bg-gray-light'; ?>">
                            Shipped
                        </a>
                        <a href="sales.php?status=delivered" class="px-3 py-1.5 rounded text-sm font-medium 
                            <?php echo ($statusFilter === 'delivered') ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-white border border-gray-medium text-gray-dark hover:bg-gray-light'; ?>">
                            Delivered
                        </a>
                        <a href="sales.php?status=cancelled" class="px-3 py-1.5 rounded text-sm font-medium 
                            <?php echo ($statusFilter === 'cancelled') ? 'bg-red-100 text-red-800 border border-red-200' : 'bg-white border border-gray-medium text-gray-dark hover:bg-gray-light'; ?>">
                            Cancelled
                        </a>
                    </div>
                    
                    <div class="relative w-full md:w-auto">
                        <input type="text" id="search-orders" placeholder="Search by order ID, buyer name or product name..." 
                               class="w-full md:w-80 pl-10 pr-4 py-2 border border-gray-medium rounded-sm focus:outline-none focus:ring-1 focus:ring-taobao-orange focus:border-taobao-orange transition-colors"
                               value="<?php echo htmlspecialchars($searchQuery); ?>">
                        <button class="absolute right-1 top-1 bg-taobao-orange text-white px-3 py-1 rounded-sm hover:bg-taobao-orange/90 transition-colors"
                                onclick="searchOrders()">
                            Search
                        </button>
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa fa-search text-gray-dark"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 订单列表 -->
        <div class="mb-6">
            <?php if (!empty($orders)): ?>
                <?php foreach ($orders as $orderId => $order): ?>
                    <div class="order-card">
                        <!-- 订单头部信息 -->
                        <div class="order-header">
                            <div class="flex items-center">
                                <span class="text-sm font-medium text-gray-900 mr-6">#<?php echo $order['id']; ?></span>
                                <span class="text-sm text-gray-dark mr-6">
                                    <i class="fa fa-calendar-o mr-1"></i> 
                                    <?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?>
                                </span>
                                <span class="text-sm text-gray-dark">
                                    <i class="fa fa-user-o mr-1"></i> 
                                    <?php echo htmlspecialchars($order['buyer_name']); ?>
                                </span>
                            </div>
                            <div>
                                <span class="font-medium <?php echo $statusClasses[$order['status']]; ?>">
                                    <?php echo $statusOptions[$order['status']]; ?>
                                </span>
                            </div>
                        </div>
                        
                        <!-- 订单商品信息 -->
                        <div class="order-items">
                            <?php foreach ($order['items'] as $index => $item): ?>
                                <div class="order-item <?php echo ($index > 0) ? 'border-t border-gray-light' : ''; ?>">
                                    <div class="flex items-start w-2/3">
                                        <img src="https://picsum.photos/id/<?php echo $item['product_id'] + 300; ?>/80/80" alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="w-16 h-16 object-cover rounded-sm mr-3">
                                        <div class="flex-1">
                                            <h4 class="text-sm font-medium text-gray-900 hover:text-taobao-orange">
                                                <a href="product-details.php?id=<?php echo $item['product_id']; ?>">
                                                    <?php echo htmlspecialchars($item['product_name']); ?>
                                                </a>
                                            </h4>
                                            <p class="text-xs text-gray-dark mt-1">Seller SKU: #<?php echo $item['product_id']; ?></p>
                                        </div>
                                        <div class="w-1/3 flex justify-between items-start">
                                            <div class="text-right">
                                                <p class="text-sm font-medium text-gray-900">R<?php echo number_format($item['item_price'], 2); ?></p>
                                                <p class="text-xs text-gray-dark">x<?php echo $item['quantity']; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- 订单底部信息 -->
                        <div class="order-footer">
                            <div class="text-right">
                                <p class="text-sm text-gray-dark mb-1">Subtotal: R<?php echo number_format($order['total'], 2); ?></p>
                                <p class="text-sm font-medium text-gray-900">Grand Total: R<?php echo number_format($order['total'], 2); ?></p>
                            </div>
                            <div class="flex space-x-2">
                                <button class="btn btn-secondary" onclick="viewOrderDetails(<?php echo $order['id']; ?>)">
                                    View Details
                                </button>
                                <?php if ($order['status'] !== 'delivered' && $order['status'] !== 'cancelled'): ?>
                                    <button class="btn btn-primary" onclick="showStatusModal(<?php echo $order['id']; ?>, '<?php echo $order['status']; ?>')">
                                        Update Status
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="bg-white rounded-sm border border-gray-medium p-8 text-center">
                    <div class="mb-4">
                        <i class="fa fa-shopping-cart text-5xl text-gray-200"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Orders Found</h3>
                    <p class="text-gray-dark max-w-md mx-auto mb-6">You haven't received any orders yet. Start promoting your products to get more sales!</p>
                    <a href="add-product.php" class="inline-block bg-taobao-orange text-white px-6 py-2 rounded-sm hover:bg-taobao-orange/90 transition-colors">
                        Add New Product
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- 分页 -->
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-dark">
                <?php if ($totalOrders > 0): ?>
                    Showing <span class="font-medium"><?php echo min(($page-1)*$perPage + 1, $totalOrders); ?></span> 
                    to <span class="font-medium"><?php echo min($page*$perPage, $totalOrders); ?></span> 
                    of <span class="font-medium"><?php echo $totalOrders; ?></span> orders
                <?php else: ?>
                    No orders to display
                <?php endif; ?>
            </div>
            
            <div class="flex items-center space-x-1">
                <?php if ($page > 1): ?>
                    <a href="sales.php?page=<?php echo $page-1; ?><?php echo ($statusFilter !== 'all') ? '&status='.$statusFilter : ''; ?><?php echo (!empty($searchQuery)) ? '&search='.urlencode($searchQuery) : ''; ?>" 
                       class="px-3 py-1.5 border border-gray-medium rounded-sm text-gray-dark hover:bg-gray-light transition-colors">
                        <i class="fa fa-angle-left"></i>
                    </a>
                <?php else: ?>
                    <span class="px-3 py-1.5 border border-gray-medium rounded-sm text-gray-300 cursor-not-allowed">
                        <i class="fa fa-angle-left"></i>
                    </span>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <a href="sales.php?page=<?php echo $i; ?><?php echo ($statusFilter !== 'all') ? '&status='.$statusFilter : ''; ?><?php echo (!empty($searchQuery)) ? '&search='.urlencode($searchQuery) : ''; ?>" 
                       class="px-3 py-1.5 border border-gray-medium rounded-sm 
                              <?php echo ($i === $page) ? 'bg-taobao-orange text-white border-taobao-orange' : 'text-gray-dark hover:bg-gray-light'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="sales.php?page=<?php echo $page+1; ?><?php echo ($statusFilter !== 'all') ? '&status='.$statusFilter : ''; ?><?php echo (!empty($searchQuery)) ? '&search='.urlencode($searchQuery) : ''; ?>" 
                       class="px-3 py-1.5 border border-gray-medium rounded-sm text-gray-dark hover:bg-gray-light transition-colors">
                        <i class="fa fa-angle-right"></i>
                    </a>
                <?php else: ?>
                    <span class="px-3 py-1.5 border border-gray-medium rounded-sm text-gray-300 cursor-not-allowed">
                        <i class="fa fa-angle-right"></i>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- 页脚 -->
    <footer class="bg-white border-t border-gray-medium mt-8">
        <div class="container mx-auto px-4 py-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 mb-4">About LocalTrader</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-dark hover:text-taobao-orange transition-colors">About Us</a></li>
                        <li><a href="#" class="text-gray-dark hover:text-taobao-orange transition-colors">Career Opportunities</a></li>
                        <li><a href="#" class="text-gray-dark hover:text-taobao-orange transition-colors">Press Center</a></li>
                        <li><a href="#" class="text-gray-dark hover:text-taobao-orange transition-colors">LocalTrader Foundation</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Seller Services</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-dark hover:text-taobao-orange transition-colors">Seller Center</a></li>
                        <li><a href="#" class="text-gray-dark hover:text-taobao-orange transition-colors">Training Courses</a></li>
                        <li><a href="#" class="text-gray-dark hover:text-taobao-orange transition-colors">Seller Forums</a></li>
                        <li><a href="#" class="text-gray-dark hover:text-taobao-orange transition-colors">Seller Help Center</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Buyer Services</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-dark hover:text-taobao-orange transition-colors">Help Center</a></li>
                        <li><a href="#" class="text-gray-dark hover:text-taobao-orange transition-colors">Trust & Safety</a></li>
                        <li><a href="#" class="text-gray-dark hover:text-taobao-orange transition-colors">Shopping Guide</a></li>
                        <li><a href="#" class="text-gray-dark hover:text-taobao-orange transition-colors">Contact Us</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Mobile Apps</h3>
                    <p class="text-gray-dark mb-4">Download our mobile app for a better shopping experience</p>
                    <div class="flex space-x-3">
                        <a href="#" class="bg-gray-100 hover:bg-gray-200 p-2 rounded-md transition-colors">
                            <i class="fa fa-apple text-xl"></i>
                        </a>
                        <a href="#" class="bg-gray-100 hover:bg-gray-200 p-2 rounded-md transition-colors">
                            <i class="fa fa-android text-xl"></i>
                        </a>
                        <a href="#" class="bg-gray-100 hover:bg-gray-200 p-2 rounded-md transition-colors">
                            <i class="fa fa-weixin text-xl"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-medium mt-8 pt-6 text-center text-sm text-gray-dark">
                <p>© 2025 LocalTrader. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- 订单状态更新模态框 -->
    <div id="status-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-sm w-full max-w-md mx-4 transform transition-all">
            <div class="p-4 border-b border-gray-medium flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">Update Order Status</h3>
                <button class="text-gray-dark hover:text-gray-900" onclick="hideStatusModal()">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            
            <div class="p-6">
                <form id="status-form" method="POST">
                    <input type="hidden" id="modal-order-id" name="order_id">
                    
                    <div class="mb-4">
                        <label for="modal-status" class="block text-sm font-medium text-gray-dark mb-1">New Status:</label>
                        <select id="modal-status" name="status" class="w-full border border-gray-medium rounded-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-taobao-orange focus:border-taobao-orange transition-colors">
                            <?php foreach ($statusOptions as $value => $label): ?>
                                <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" class="btn btn-secondary" onclick="hideStatusModal()">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            Update Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // 搜索订单
        function searchOrders() {
            const search = document.getElementById('search-orders').value;
            const status = '<?php echo $statusFilter; ?>';
            window.location.href = `sales.php?search=${encodeURIComponent(search)}${status !== 'all' ? '&status=' + status : ''}`;
        }
        
        // 查看订单详情
        function viewOrderDetails(orderId) {
            window.location.href = `order-details.php?id=${orderId}`;
        }
        
        // 状态更新模态框
        function showStatusModal(orderId, currentStatus) {
            document.getElementById('modal-order-id').value = orderId;
            document.getElementById('modal-status').value = currentStatus;
            document.getElementById('status-modal').classList.remove('hidden');
        }
        
        function hideStatusModal() {
            document.getElementById('status-modal').classList.add('hidden');
        }
        
        // 点击模态框外部关闭
        document.getElementById('status-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideStatusModal();
            }
        });
        
        // 下拉菜单
        document.querySelector('button.flex.items-center.text-gray-dark').addEventListener('click', function() {
            const menu = this.parentElement.querySelector('div.hidden');
            menu.classList.toggle('hidden');
        });
        
        // 点击其他区域关闭下拉菜单
        document.addEventListener('click', function(event) {
            const dropdown = document.querySelector('div.relative');
            if (!dropdown.contains(event.target)) {
                dropdown.querySelector('div.hidden').classList.add('hidden');
            }
        });
    </script>
</body>
</html>