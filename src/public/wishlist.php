<?php
session_start();
require_once '../../config/db.php';

// 验证用户登录状态
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$wishlistItems = [];

try {
    // 查询用户愿望单
    $stmt = $pdo->prepare("
        SELECT w.id as wishlist_id, p.* 
        FROM wishlist w 
        JOIN products p ON w.product_id = p.id 
        WHERE w.user_id = ?
    ");
    $stmt->execute([$userId]);
    $wishlistItems = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "获取愿望单失败: " . $e->getMessage();
}

// 处理移除愿望单项目
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $wishlistId = $_GET['remove'];
    try {
        $stmt = $pdo->prepare("DELETE FROM wishlist WHERE id = ? AND user_id = ?");
        $stmt->execute([$wishlistId, $userId]);
        header("Location: wishlist.php");
        exit;
    } catch (PDOException $e) {
        $error = "移除项目失败: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>我的愿望单 - C2C电商平台</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3b82f6',
                        secondary: '#f97316',
                        neutral: '#f8fafc',
                    },
                    fontFamily: {
                        inter: ['Inter', 'system-ui', 'sans-serif'],
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
            .product-card-hover {
                @apply transition-all duration-300 hover:shadow-lg hover:-translate-y-1;
            }
            .btn-primary {
                @apply bg-primary hover:bg-primary/90 text-white font-medium py-2 px-4 rounded-lg transition-all duration-200;
            }
            .btn-secondary {
                @apply bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2 px-4 rounded-lg transition-all duration-200;
            }
            .btn-danger {
                @apply bg-red-500 hover:bg-red-600 text-white font-medium py-2 px-4 rounded-lg transition-all duration-200;
            }
        }
    </style>
</head>
<body class="font-inter bg-neutral min-h-screen flex flex-col">
    <!-- 导航栏 -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="container mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i class="fa fa-shopping-bag text-primary text-2xl"></i>
                <span class="text-xl font-bold text-gray-800">C2C电商平台</span>
            </div>
            
            <nav class="hidden md:flex items-center space-x-6">
                <a href="index.php" class="text-gray-700 hover:text-primary transition-colors">首页</a>
                <a href="categories.php" class="text-gray-700 hover:text-primary transition-colors">分类</a>
                <a href="wishlist.php" class="text-primary font-medium">愿望单</a>
                <a href="cart.php" class="text-gray-700 hover:text-primary transition-colors relative">
                    <i class="fa fa-shopping-cart"></i>
                    <span class="absolute -top-2 -right-2 bg-secondary text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">3</span>
                </a>
            </nav>
            
            <div class="flex items-center space-x-4">
                <div class="relative md:hidden">
                    <button class="text-gray-700 focus:outline-none">
                        <i class="fa fa-bars text-xl"></i>
                    </button>
                </div>
                
                <div class="relative hidden md:block">
                    <input type="text" placeholder="搜索商品..." class="w-64 pl-10 pr-4 py-2 rounded-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                    <i class="fa fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
                
                <div class="relative">
                    <button class="flex items-center space-x-2 focus:outline-none">
                        <img src="https://picsum.photos/id/1005/40/40" alt="用户头像" class="w-8 h-8 rounded-full object-cover border-2 border-primary">
                        <span class="hidden md:inline-block text-gray-700 font-medium"><?php echo $_SESSION['username'] ?? '用户'; ?></span>
                        <i class="fa fa-angle-down text-gray-500"></i>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- 主内容区 -->
    <main class="flex-grow container mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-[clamp(1.5rem,3vw,2.5rem)] font-bold text-gray-800 mb-2">我的愿望单</h1>
            <p class="text-gray-600">您收藏的商品都在这里，随时可以查看和购买</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                <strong class="font-bold">错误!</strong>
                <span class="block sm:inline"><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <?php if (empty($wishlistItems)): ?>
            <div class="bg-white rounded-xl shadow-sm p-8 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 mb-4">
                    <i class="fa fa-heart-o text-primary text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">您的愿望单是空的</h3>
                <p class="text-gray-600 mb-6">浏览商品并添加到愿望单，方便日后查看和购买</p>
                <a href="index.php" class="btn-primary inline-flex items-center">
                    <i class="fa fa-shopping-bag mr-2"></i> 开始购物
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php foreach ($wishlistItems as $item): ?>
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden product-card-hover">
                        <div class="relative">
                            <img src="<?php echo $item['image_path'] ?: 'https://picsum.photos/id/26/400/300'; ?>" 
                                alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                class="w-full h-52 object-cover">
                            <button onclick="location.href='wishlist.php?remove=<?php echo $item['wishlist_id']; ?>'" 
                                class="absolute top-3 right-3 bg-white/80 hover:bg-white text-red-500 rounded-full w-8 h-8 flex items-center justify-center transition-all duration-200">
                                <i class="fa fa-heart text-red-500"></i>
                            </button>
                        </div>
                        <div class="p-4">
                            <div class="flex items-center text-yellow-400 mb-2">
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star-half-o"></i>
                                <span class="text-gray-600 text-sm ml-1">(42)</span>
                            </div>
                            <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2 h-12">
                                <?php echo htmlspecialchars($item['title']); ?>
                            </h3>
                            <p class="text-gray-600 text-sm mb-3 line-clamp-2 h-10">
                                <?php echo htmlspecialchars(substr($item['description'], 0, 80)); ?>
                            </p>
                            <div class="flex justify-between items-center">
                                <span class="text-xl font-bold text-gray-800">¥<?php echo number_format($item['price'], 2); ?></span>
                                <button class="btn-primary text-sm py-1.5 px-3" onclick="addToCart(<?php echo $item['id']; ?>)">
                                    <i class="fa fa-shopping-cart mr-1"></i> 加入购物车
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- 页脚 -->
    <footer class="bg-gray-800 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <i class="fa fa-shopping-bag text-primary text-2xl"></i>
                        <span class="text-xl font-bold">C2C电商平台</span>
                    </div>
                    <p class="text-gray-400 mb-4">连接买家与卖家的电子商务平台，让交易更简单。</p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <i class="fa fa-facebook"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <i class="fa fa-twitter"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <i class="fa fa-instagram"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <i class="fa fa-linkedin"></i>
                        </a>
                    </div>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">快速链接</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">首页</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">商品分类</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">热门商品</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">最新上架</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">关于我们</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">客户服务</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">帮助中心</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">联系我们</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">退换政策</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">隐私政策</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">用户协议</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">联系我们</h4>
                    <ul class="space-y-2">
                        <li class="flex items-start space-x-3">
                            <i class="fa fa-map-marker mt-1 text-primary"></i>
                            <span class="text-gray-400">北京市朝阳区建国路88号</span>
                        </li>
                        <li class="flex items-center space-x-3">
                            <i class="fa fa-phone text-primary"></i>
                            <span class="text-gray-400">400-123-4567</span>
                        </li>
                        <li class="flex items-center space-x-3">
                            <i class="fa fa-envelope text-primary"></i>
                            <span class="text-gray-400">support@c2cplatform.com</span>
                        </li>
                    </ul>
                    <div class="mt-4">
                        <h5 class="text-sm font-medium mb-2">订阅我们的新闻</h5>
                        <div class="flex">
                            <input type="email" placeholder="您的邮箱地址" class="px-4 py-2 bg-gray-700 text-white rounded-l-lg focus:outline-none focus:ring-1 focus:ring-primary w-full">
                            <button class="bg-primary hover:bg-primary/90 text-white px-4 rounded-r-lg transition-colors">
                                <i class="fa fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-500 text-sm">
                <p>&copy; 2025 C2C电商平台. 保留所有权利.</p>
            </div>
        </div>
    </footer>

    <script>
        // 加入购物车功能
        function addToCart(productId) {
            // 这里应该有AJAX请求添加商品到购物车
            alert(`商品 ${productId} 已添加到购物车`);
        }

        // 移动端菜单
        document.querySelector('.fa-bars').parentElement.addEventListener('click', function() {
            const nav = document.querySelector('nav');
            nav.classList.toggle('hidden');
            nav.classList.toggle('absolute');
            nav.classList.toggle('top-16');
            nav.classList.toggle('left-0');
            nav.classList.toggle('w-full');
            nav.classList.toggle('bg-white');
            nav.classList.toggle('shadow-md');
            nav.classList.toggle('p-4');
            nav.classList.toggle('flex-col');
            nav.classList.toggle('space-y-4');
        });
    </script>
</body>
</html>    