<?php
session_start();

// 验证用户是否已登录且为卖家
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'seller') {
    header("Location: /login.php");
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/config/database.php';

try {
    $seller_id = $_SESSION['user_id'];
    
    // 分页设置
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;
    
    // 查询卖家的商品总数
    $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM products WHERE seller_id = ?");
    $countStmt->execute([$seller_id]);
    $total = $countStmt->fetchColumn();
    $totalPages = ceil($total / $limit);
    
    // 查询当前页的商品
    $stmt = $pdo->prepare("SELECT p.id, p.name, p.price, p.stock, p.status, c.name AS category_name 
                           FROM products p 
                           LEFT JOIN categories c ON p.category_id = c.id 
                           WHERE p.seller_id = ? 
                           ORDER BY p.created_at DESC 
                           LIMIT ? OFFSET ?");
    $stmt->execute([$seller_id, $limit, $offset]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("数据库错误: " . $e->getMessage());
}

// 处理批量操作
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $selectedIds = isset($_POST['product_ids']) ? $_POST['product_ids'] : [];
    
    if (empty($selectedIds)) {
        $_SESSION['message'] = ['type' => 'error', 'text' => '请选择商品'];
        header("Location: manage.php");
        exit;
    }
    
    try {
        switch ($_POST['action']) {
            case 'activate':
                $updateStmt = $pdo->prepare("UPDATE products SET status = 'active' WHERE id = ? AND seller_id = ?");
                foreach ($selectedIds as $id) {
                    $updateStmt->execute([$id, $seller_id]);
                }
                $_SESSION['message'] = ['type' => 'success', 'text' => '所选商品已激活'];
                break;
                
            case 'deactivate':
                $updateStmt = $pdo->prepare("UPDATE products SET status = 'inactive' WHERE id = ? AND seller_id = ?");
                foreach ($selectedIds as $id) {
                    $updateStmt->execute([$id, $seller_id]);
                }
                $_SESSION['message'] = ['type' => 'success', 'text' => '所选商品已禁用'];
                break;
                
            case 'delete':
                $deleteStmt = $pdo->prepare("DELETE FROM products WHERE id = ? AND seller_id = ?");
                foreach ($selectedIds as $id) {
                    $deleteStmt->execute([$id, $seller_id]);
                }
                $_SESSION['message'] = ['type' => 'success', 'text' => '所选商品已删除'];
                break;
                
            default:
                $_SESSION['message'] = ['type' => 'error', 'text' => '未知操作'];
        }
    } catch (PDOException $e) {
        $_SESSION['message'] = ['type' => 'error', 'text' => '操作失败: ' . $e->getMessage()];
    }
    
    header("Location: manage.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商品管理 - SellerHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#165DFF',
                        secondary: '#0FC6C2',
                        success: '#00B42A',
                        warning: '#FF7D00',
                        danger: '#F53F3F',
                        info: '#86909C',
                        light: '#F2F3F5',
                        dark: '#1D2129',
                        'primary-light': '#E8F3FF',
                        'secondary-light': '#E6FFFB',
                        'success-light': '#F0FFF4',
                        'warning-light': '#FFF7E6',
                        'danger-light': '#FFF2F0',
                    },
                    fontFamily: {
                        inter: ['Inter', 'sans-serif'],
                    },
                },
            }
        }
    </script>
    
    <style type="text/tailwindcss">
        @layer utilities {
            .content-auto {
                content-visibility: auto;
            }
            .scrollbar-hide {
                -ms-overflow-style: none;
                scrollbar-width: none;
            }
            .scrollbar-hide::-webkit-scrollbar {
                display: none;
            }
            .sidebar-item {
                @apply flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:bg-primary-light hover:text-primary transition-all duration-200;
            }
            .sidebar-item.active {
                @apply bg-primary-light text-primary font-medium;
            }
            .card {
                @apply bg-white rounded-xl shadow-sm border border-gray-100 p-5 transition-all duration-300 hover:shadow-md;
            }
            .btn {
                @apply px-4 py-2 rounded-lg font-medium transition-all duration-200 flex items-center justify-center gap-2;
            }
            .btn-primary {
                @apply bg-primary text-white hover:bg-primary/90 active:bg-primary/80;
            }
            .btn-secondary {
                @apply bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 active:bg-gray-100;
            }
            .btn-danger {
                @apply bg-danger text-white hover:bg-danger/90 active:bg-danger/80;
            }
            .badge {
                @apply px-2 py-1 rounded-full text-xs font-medium;
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
            .table-row {
                @apply hover:bg-gray-50 transition-colors duration-150;
            }
        }
    </style>
</head>
<body class="font-inter bg-gray-50 text-gray-800 min-h-screen flex flex-col">
    <!-- 顶部导航栏 -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <!-- 左侧Logo和汉堡菜单 -->
                <div class="flex items-center gap-4">
                    <button id="sidebar-toggle" class="lg:hidden text-gray-500 hover:text-primary">
                        <i class="fa fa-bars text-xl"></i>
                    </button>
                    <a href="#" class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center">
                            <i class="fa fa-shopping-bag text-white"></i>
                        </div>
                        <span class="font-bold text-lg text-gray-800">SellerHub</span>
                    </a>
                </div>
                
                <!-- 搜索栏 -->
                <div class="hidden md:block flex-grow max-w-xl mx-4">
                    <div class="relative">
                        <input type="text" placeholder="搜索订单、商品..." class="form-input pl-10 w-full">
                        <i class="fa fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>
                
                <!-- 右侧用户信息和通知 -->
                <div class="flex items-center gap-4">
                    <button class="relative p-2 text-gray-500 hover:text-primary hover:bg-gray-100 rounded-full transition-colors">
                        <i class="fa fa-bell-o text-xl"></i>
                        <span class="absolute top-1 right-1 w-2 h-2 bg-danger rounded-full"></span>
                    </button>
                    <button class="relative p-2 text-gray-500 hover:text-primary hover:bg-gray-100 rounded-full transition-colors">
                        <i class="fa fa-envelope-o text-xl"></i>
                        <span class="absolute top-1 right-1 w-2 h-2 bg-primary rounded-full"></span>
                    </button>
                    <div class="relative group">
                        <button class="flex items-center gap-2">
                            <img src="https://picsum.photos/id/64/200/200" alt="用户头像" class="w-8 h-8 rounded-full object-cover border-2 border-white shadow-sm">
                            <span class="hidden md:block font-medium">王小明</span>
                            <i class="fa fa-angle-down text-gray-400"></i>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-10 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform group-hover:translate-y-0 translate-y-2">
                            <a href="#" class="dropdown-item flex items-center gap-2">
                                <i class="fa fa-user-o text-gray-400"></i>
                                <span>个人资料</span>
                            </a>
                            <a href="#" class="dropdown-item flex items-center gap-2">
                                <i class="fa fa-cog text-gray-400"></i>
                                <span>账号设置</span>
                            </a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <a href="#" class="dropdown-item flex items-center gap-2 text-danger">
                                <i class="fa fa-sign-out text-danger"></i>
                                <span>退出登录</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="flex flex-1 overflow-hidden">
        <!-- 侧边栏 -->
        <aside id="sidebar" class="w-64 bg-white shadow-sm border-r border-gray-100 transition-all duration-300 ease-in-out transform lg:translate-x-0 -translate-x-full fixed lg:relative h-[calc(100vh-4rem)] z-40 overflow-y-auto scrollbar-hide">
            <div class="p-4">
                <div class="mb-6">
                    <h2 class="text-xs uppercase font-semibold text-gray-400 tracking-wider mb-3">主导航</h2>
                    <nav class="space-y-1">
                        <a href="#" class="sidebar-item">
                            <i class="fa fa-dashboard w-5 text-center"></i>
                            <span>仪表盘</span>
                        </a>
                        <a href="#" class="sidebar-item active">
                            <i class="fa fa-tags w-5 text-center"></i>
                            <span>商品管理</span>
                        </a>
                        <a href="#" class="sidebar-item">
                            <i class="fa fa-shopping-cart w-5 text-center"></i>
                            <span>订单管理</span>
                        </a>
                        <a href="#" class="sidebar-item">
                            <i class="fa fa-users w-5 text-center"></i>
                            <span>客户管理</span>
                        </a>
                        <a href="#" class="sidebar-item">
                            <i class="fa fa-credit-card w-5 text-center"></i>
                            <span>财务管理</span>
                        </a>
                    </nav>
                </div>
                
                <div class="mb-6">
                    <h2 class="text-xs uppercase font-semibold text-gray-400 tracking-wider mb-3">营销中心</h2>
                    <nav class="space-y-1">
                        <a href="#" class="sidebar-item">
                            <i class="fa fa-bullhorn w-5 text-center"></i>
                            <span>促销活动</span>
                        </a>
                        <a href="#" class="sidebar-item">
                            <i class="fa fa-percent w-5 text-center"></i>
                            <span>优惠券管理</span>
                        </a>
                        <a href="#" class="sidebar-item">
                            <i class="fa fa-line-chart w-5 text-center"></i>
                            <span>广告投放</span>
                        </a>
                    </nav>
                </div>
                
                <div class="mb-6">
                    <h2 class="text-xs uppercase font-semibold text-gray-400 tracking-wider mb-3">运营管理</h2>
                    <nav class="space-y-1">
                        <a href="#" class="sidebar-item">
                            <i class="fa fa-truck w-5 text-center"></i>
                            <span>物流配送</span>
                        </a>
                        <a href="#" class="sidebar-item">
                            <i class="fa fa-refresh w-5 text-center"></i>
                            <span>退换货处理</span>
                        </a>
                        <a href="#" class="sidebar-item">
                            <i class="fa fa-comments w-5 text-center"></i>
                            <span>客户评价</span>
                        </a>
                    </nav>
                </div>
                
                <div class="mb-6">
                    <h2 class="text-xs uppercase font-semibold text-gray-400 tracking-wider mb-3">数据中心</h2>
                    <nav class="space-y-1">
                        <a href="#" class="sidebar-item">
                            <i class="fa fa-bar-chart w-5 text-center"></i>
                            <span>销售分析</span>
                        </a>
                        <a href="#" class="sidebar-item">
                            <i class="fa fa-pie-chart w-5 text-center"></i>
                            <span>流量分析</span>
                        </a>
                        <a href="#" class="sidebar-item">
                            <i class="fa fa-users w-5 text-center"></i>
                            <span>用户分析</span>
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- 侧边栏底部 -->
            <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-100 bg-white">
                <div class="bg-primary-light rounded-lg p-3">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center text-white">
                            <i class="fa fa-rocket"></i>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-800">升级专业版</h3>
                            <p class="text-xs text-gray-600 mt-1">解锁更多高级功能，提升店铺业绩</p>
                            <button class="btn btn-primary text-xs mt-2 w-full">立即升级</button>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- 遮罩层 -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 lg:hidden hidden"></div>

        <!-- 主内容区 -->
        <main class="flex-1 overflow-y-auto p-4 lg:p-6 bg-gray-50">
            <div class="max-w-7xl mx-auto">
                <!-- 页面标题 -->
                <div class="mb-6">
                    <h1 class="text-[clamp(1.5rem,3vw,2.5rem)] font-bold text-gray-800">商品管理</h1>
                    <p class="text-gray-500 mt-1">管理您的所有商品，包括查看、编辑和删除操作</p>
                </div>
                
                <!-- 消息提示 -->
                <?php if (isset($_SESSION['message'])): ?>
                <div class="mb-6">
                    <div class="alert alert-<?php echo $_SESSION['message']['type']; ?> p-4 rounded-lg">
                        <p><?php echo $_SESSION['message']['text']; ?></p>
                    </div>
                </div>
                <?php unset($_SESSION['message']); endif; ?>
                
                <!-- 操作按钮 -->
                <div class="flex flex-wrap items-center justify-between mb-6 gap-4">
                    <div class="flex flex-wrap gap-2">
                        <a href="add.php" class="btn btn-primary">
                            <i class="fa fa-plus mr-1"></i>
                            <span>添加新商品</span>
                        </a>
                        
                        <div class="relative group">
                            <button class="btn btn-secondary">
                                <i class="fa fa-cog mr-1"></i>
                                <span>批量操作</span>
                                <i class="fa fa-angle-down ml-1"></i>
                            </button>
                            <div class="absolute left-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-10 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform group-hover:translate-y-0 translate-y-2">
                                <form action="manage.php" method="POST" id="batch-action-form">
                                    <input type="hidden" name="action" id="batch-action">
                                    <input type="hidden" name="product_ids" id="batch-product-ids">
                                    
                                    <button type="button" onclick="setBatchAction('activate')" class="dropdown-item flex items-center gap-2">
                                        <i class="fa fa-check-circle text-success"></i>
                                        <span>激活选中商品</span>
                                    </button>
                                    <button type="button" onclick="setBatchAction('deactivate')" class="dropdown-item flex items-center gap-2">
                                        <i class="fa fa-ban text-danger"></i>
                                        <span>禁用选中商品</span>
                                    </button>
                                    <div class="border-t border-gray-100 my-1"></div>
                                    <button type="button" onclick="setBatchAction('delete')" class="dropdown-item flex items-center gap-2 text-danger">
                                        <i class="fa fa-trash"></i>
                                        <span>删除选中商品</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-2">
                        <div class="relative">
                            <input type="text" placeholder="搜索商品..." class="form-input pl-10 min-w-[200px]" id="search-input">
                            <i class="fa fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <select class="form-input" id="category-filter">
                            <option value="">所有分类</option>
                            <?php
                            // 获取所有分类
                            $catStmt = $pdo->prepare("SELECT id, name FROM categories ORDER BY name");
                            $catStmt->execute();
                            $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach ($categories as $cat):
                            ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select class="form-input" id="status-filter">
                            <option value="">所有状态</option>
                            <option value="active">活跃</option>
                            <option value="pending">待审核</option>
                            <option value="inactive">已禁用</option>
                        </select>
                        <button class="btn btn-secondary" id="filter-btn">
                            <i class="fa fa-filter mr-1"></i>
                            <span>筛选</span>
                        </button>
                    </div>
                </div>
                
                <!-- 商品表格 -->
                <div class="card">
                    <div class="overflow-x-auto">
                        <form action="manage.php" method="POST">
                            <table class="min-w-full">
                                <thead>
                                    <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <th class="pb-3 pr-4 w-10">
                                            <input type="checkbox" id="select-all">
                                        </th>
                                        <th class="pb-3 pr-4">商品信息</th>
                                        <th class="pb-3 pr-4">价格</th>
                                        <th class="pb-3 pr-4">库存</th>
                                        <th class="pb-3 pr-4">分类</th>
                                        <th class="pb-3 pr-4">状态</th>
                                        <th class="pb-3 pr-4">创建时间</th>
                                        <th class="pb-3 text-right">操作</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <?php foreach ($products as $p): ?>
                                    <tr class="table-row">
                                        <td class="py-3 pr-4">
                                            <input type="checkbox" name="product_ids[]" value="<?php echo $p['id']; ?>" class="product-checkbox">
                                        </td>
                                        <td class="py-3 pr-4">
                                            <div class="flex items-center gap-3">
                                                <img src="<?php echo !empty($p['image']) ? '../uploads/products/' . htmlspecialchars($p['image']) : 'https://picsum.photos/id/' . rand(1, 100) . '/100/100'; ?>" 
                                                     alt="<?php echo htmlspecialchars($p['name']); ?>" 
                                                     class="w-12 h-12 rounded-lg object-cover">
                                                <div>
                                                    <p class="text-sm font-medium"><?php echo htmlspecialchars($p['name']); ?></p>
                                                    <p class="text-xs text-gray-500">ID: <?php echo $p['id']; ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 pr-4 text-sm font-medium">¥<?php echo number_format($p['price'], 2); ?></td>
                                        <td class="py-3 pr-4 text-sm">
                                            <?php if ($p['stock'] <= 0): ?>
                                                <span class="text-danger">无库存</span>
                                            <?php elseif ($p['stock'] <= 10): ?>
                                                <span class="text-warning">库存低 (<?php echo $p['stock']; ?>)</span>
                                            <?php else: ?>
                                                <span class="text-success">充足 (<?php echo $p['stock']; ?>)</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 pr-4 text-sm"><?php echo htmlspecialchars($p['category_name']); ?></td>
                                        <td class="py-3 pr-4">
                                            <?php if ($p['status'] === 'active'): ?>
                                                <span class="badge badge-success">活跃</span>
                                            <?php elseif ($p['status'] === 'pending'): ?>
                                                <span class="badge badge-warning">待审核</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">已禁用</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 pr-4 text-sm text-gray-600">
                                            <?php echo date('Y-m-d', strtotime($p['created_at'])); ?>
                                        </td>
                                        <td class="py-3 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="view.php?id=<?php echo $p['id']; ?>" class="text-gray-500 hover:text-primary" title="查看">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <a href="edit.php?id=<?php echo $p['id']; ?>" class="text-gray-500 hover:text-primary" title="编辑">
                                                    <i class="fa fa-pencil"></i>
                                                </a>
                                                <a href="delete.php?id=<?php echo $p['id']; ?>" onclick="return confirm('确定要删除此商品吗？此操作不可撤销。')" class="text-gray-500 hover:text-danger" title="删除">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    
                                    <!-- 无数据时显示 -->
                                    <?php if (empty($products)): ?>
                                    <tr>
                                        <td colspan="8" class="py-6 text-center text-gray-500">
                                            <div class="flex flex-col items-center gap-3">
                                                <i class="fa fa-shopping-bag text-4xl text-gray-300"></i>
                                                <p>您还没有添加任何商品</p>
                                                <a href="add.php" class="btn btn-primary">
                                                    <i class="fa fa-plus mr-1"></i>
                                                    <span>添加第一个商品</span>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </form>
                    </div>
                    
                    <!-- 分页 -->
                    <div class="flex flex-col md:flex-row justify-between items-center mt-6 gap-4">
                        <div class="text-sm text-gray-500">
                            显示 <?php echo ($page - 1) * $limit + 1; ?>-<?php echo min($page * $limit, $total); ?> 条，共 <?php echo $total; ?> 条
                        </div>
                        <div class="flex items-center gap-1">
                            <a href="manage.php?page=1" class="btn btn-secondary text-sm px-3 py-1">
                                <i class="fa fa-angle-double-left"></i>
                            </a>
                            <a href="manage.php?page=<?php echo max(1, $page - 1); ?>" class="btn btn-secondary text-sm px-3 py-1">
                                <i class="fa fa-angle-left"></i>
                            </a>
                            
                            <?php
                            $startPage = max(1, $page - 2);
                            $endPage = min($totalPages, $startPage + 4);
                            $startPage = max(1, $endPage - 4);
                            
                            for ($i = $startPage; $i <= $endPage; $i++):
                            ?>
                            <a href="manage.php?page=<?php echo $i; ?>" class="btn <?php echo $i == $page ? 'btn-primary' : 'btn-secondary'; ?> text-sm px-3 py-1">
                                <?php echo $i; ?>
                            </a>
                            <?php endfor; ?>
                            
                            <a href="manage.php?page=<?php echo min($totalPages, $page + 1); ?>" class="btn btn-secondary text-sm px-3 py-1">
                                <i class="fa fa-angle-right"></i>
                            </a>
                            <a href="manage.php?page=<?php echo $totalPages; ?>" class="btn btn-secondary text-sm px-3 py-1">
                                <i class="fa fa-angle-double-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // 侧边栏切换
        document.getElementById('sidebar-toggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        });
        
        document.getElementById('sidebar-overlay').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        });
        
        // 全选/取消全选
        document.getElementById('select-all').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.product-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
        
        // 设置批量操作
        function setBatchAction(action) {
            const checkboxes = document.querySelectorAll('.product-checkbox:checked');
            const productIds = Array.from(checkboxes).map(cb => cb.value);
            
            if (productIds.length === 0) {
                alert('请选择商品');
                return;
            }
            
            if (action === 'delete' && !confirm('确定要删除选中的商品吗？此操作不可撤销。')) {
                return;
            }
            
            document.getElementById('batch-action').value = action;
            document.getElementById('batch-action-form').submit();
        }
        
        // 筛选功能
        document.getElementById('filter-btn').addEventListener('click', function() {
            const search = document.getElementById('search-input').value;
            const category = document.getElementById('category-filter').value;
            const status = document.getElementById('status-filter').value;
            
            let queryString = 'manage.php?';
            if (search) queryString += `search=${encodeURIComponent(search)}&`;
            if (category) queryString += `category=${category}&`;
            if (status) queryString += `status=${status}&`;
            
            window.location.href = queryString;
        });
    </script>
</body>
</html>    
