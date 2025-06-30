<?php
session_start();
require_once '../config/db.php';

$stmt = $pdo->query("SELECT p.name, p.price, p.description, i.image_url FROM products p 
left join product_images i on p.id = i.product_id");
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Products - Taobao</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#FF4400', // Taobao primary color
                        secondary: '#FFB800',
                        dark: '#333333',
                        light: '#F5F5F5',
                        success: '#00B42A',
                        warning: '#FF7D00',
                        danger: '#F53F3F',
                        info: '#86909C'
                    },
                    fontFamily: {
                        sans: ['PingFang SC', 'Helvetica Neue', 'Arial', 'sans-serif']
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
            .product-card-hover {
                @apply hover:shadow-xl hover:-translate-y-1 transition-all duration-300;
            }
            .btn-hover {
                @apply hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300;
            }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <!-- 顶部导航 -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="container mx-auto px-4 py-3 flex flex-col md:flex-row justify-between items-center">
            <div class="flex items-center space-x-2 w-full md:w-auto">
                <i class="fa fa-shopping-bag text-primary text-2xl"></i>
                <span class="text-xl font-bold text-dark">Taobao</span>
            </div>
            
            <div class="w-full md:w-1/2 mt-3 md:mt-0 mb-3 md:mb-0">
                <div class="relative">
                    <input type="text" placeholder="Search products" 
                        class="w-full py-2 px-4 pr-10 rounded-full border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none" />
                    <button class="absolute right-2 top-1/2 transform -translate-y-1/2 text-primary">
                        <i class="fa fa-search"></i>
                    </button>
                </div>
            </div>
            
            <div class="flex items-center space-x-4 w-full md:w-auto justify-between md:justify-end">
                <a href="#" class="text-gray-600 hover:text-primary transition-colors">
                    <i class="fa fa-user-circle text-xl"></i>
                    <span class="hidden md:inline ml-1">My Taobao</span>
                </a>
                <a href="#" class="text-gray-600 hover:text-primary transition-colors">
                    <i class="fa fa-shopping-cart text-xl"></i>
                    <span class="hidden md:inline ml-1">Shopping Cart</span>
                    <span class="absolute -top-1 -right-1 bg-primary text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">3</span>
                </a>
                <a href="#" class="text-gray-600 hover:text-primary transition-colors">
                    <i class="fa fa-heart text-xl"></i>
                    <span class="hidden md:inline ml-1">Favorites</span>
                </a>
            </div>
        </div>
        
        <!-- 二级导航 -->
        <nav class="bg-white border-t border-gray-100">
            <div class="container mx-auto px-4">
                <ul class="flex overflow-x-auto whitespace-nowrap py-2 text-sm">
                    <li class="mr-6">
                        <a href="#" class="text-primary font-medium">All Products</a>
                    </li>
                    <li class="mr-6">
                        <a href="#" class="text-gray-600 hover:text-primary transition-colors">Electronics</a>
                    </li>
                    <li class="mr-6">
                        <a href="#" class="text-gray-600 hover:text-primary transition-colors">Home Appliances</a>
                    </li>
                    <li class="mr-6">
                        <a href="#" class="text-gray-600 hover:text-primary transition-colors">Clothing & Shoes</a>
                    </li>
                    <li class="mr-6">
                        <a href="#" class="text-gray-600 hover:text-primary transition-colors">Beauty & Personal Care</a>
                    </li>
                    <li class="mr-6">
                        <a href="#" class="text-gray-600 hover:text-primary transition-colors">Baby & Maternity</a>
                    </li>
                    <li class="mr-6">
                        <a href="#" class="text-gray-600 hover:text-primary transition-colors">Food & Beverages</a>
                    </li>
                    <li class="mr-6">
                        <a href="#" class="text-gray-600 hover:text-primary transition-colors">Home & Furniture</a>
                    </li>
                    <li class="mr-6">
                        <a href="#" class="text-gray-600 hover:text-primary transition-colors">Sports & Outdoors</a>
                    </li>
                    <li class="mr-6">
                        <a href="#" class="text-gray-600 hover:text-primary transition-colors">Auto Accessories</a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <!-- 主要内容区 -->
    <main class="flex-1 container mx-auto px-4 py-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">All Products</h1>
            <p class="text-gray-500 mt-1">Discover over 1 billion products</p>
        </div>
        
        <!-- 筛选工具栏 -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
            <div class="flex flex-wrap items-center space-x-4">
                <div class="flex items-center space-x-6">
                    <button class="text-primary font-medium">综合</button>
                    <button class="text-gray-600 hover:text-primary transition-colors">销量</button>
                    <button class="text-gray-600 hover:text-primary transition-colors">价格</button>
                    <button class="text-gray-600 hover:text-primary transition-colors">新品</button>
                    <button class="text-gray-600 hover:text-primary transition-colors">信用</button>
                </div>
                
                <div class="ml-auto flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <span class="text-gray-600">价格:</span>
                        <input type="number" placeholder="¥" class="w-20 py-1 px-2 border border-gray-300 rounded text-sm" />
                        <span class="text-gray-400">-</span>
                        <input type="number" placeholder="¥" class="w-20 py-1 px-2 border border-gray-300 rounded text-sm" />
                        <button class="bg-primary text-white py-1 px-3 rounded text-sm">确定</button>
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <button class="p-1 border border-gray-300 rounded hover:border-primary">
                            <i class="fa fa-th-large"></i>
                        </button>
                        <button class="p-1 border border-gray-300 rounded hover:border-primary">
                            <i class="fa fa-list"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 商品列表 -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php foreach ($products as $p): ?>
                <div class="bg-white rounded-lg shadow-sm overflow-hidden product-card-hover">
                    <div class="relative">
                        <img src="<?php echo $p['image_url'] ?: 'https://picsum.photos/300/300?random=' . rand(1, 100); ?>" 
                             alt="<?php echo htmlspecialchars($p['name']); ?>" 
                             class="w-full h-64 object-cover">
                        <div class="absolute top-2 left-2">
                            <span class="bg-primary text-white text-xs px-2 py-1 rounded">精选</span>
                        </div>
                    </div>
                    
                    <div class="p-4">
                        <div class="flex items-baseline">
                            <span class="text-primary font-bold text-xl">¥<?php echo $p['price']; ?></span>
                            <span class="text-gray-400 text-xs ml-1 line-through">¥<?php echo number_format($p['price'] * 1.2, 2); ?></span>
                        </div>
                        
                        <h3 class="mt-2 font-medium line-clamp-2 h-12">
                            <?php echo htmlspecialchars($p['name']); ?>
                        </h3>
                        
                        <div class="mt-3 flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex text-yellow-400">
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star-half-o"></i>
                                </div>
                                <span class="text-gray-500 text-xs ml-1">4.5 (128)</span>
                            </div>
                            
                            <span class="text-gray-500 text-xs">月销1.2万+</span>
                        </div>
                        
                        <div class="mt-3 flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fa fa-shop text-gray-400"></i>
                                <span class="text-gray-600 text-xs ml-1">官方旗舰店</span>
                            </div>
                            
                            <div class="flex items-center text-gray-400 text-xs">
                                <i class="fa fa-location-dot"></i>
                                <span>广东</span>
                            </div>
                        </div>
                        
                        <div class="mt-3 pt-3 border-t border-gray-100 flex justify-between">
                            <button class="text-gray-600 hover:text-primary transition-colors text-sm">
                                <i class="fa fa-heart-o mr-1"></i>收藏
                            </button>
                            <button class="text-primary hover:bg-primary/10 transition-colors text-sm px-3 py-1 rounded">
                                <i class="fa fa-shopping-cart mr-1"></i>加入购物车
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- 分页 -->
        <div class="mt-8 flex justify-center">
            <nav class="flex items-center space-x-1">
                <a href="#" class="px-3 py-2 rounded-lg border border-gray-300 text-gray-600 hover:border-primary hover:text-primary transition-colors">
                    <i class="fa fa-angle-left"></i>
                </a>
                <a href="#" class="px-4 py-2 rounded-lg bg-primary text-white">1</a>
                <a href="#" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-600 hover:border-primary hover:text-primary transition-colors">2</a>
                <a href="#" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-600 hover:border-primary hover:text-primary transition-colors">3</a>
                <span class="px-2 text-gray-400">...</span>
                <a href="#" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-600 hover:border-primary hover:text-primary transition-colors">12</a>
                <a href="#" class="px-3 py-2 rounded-lg border border-gray-300 text-gray-600 hover:border-primary hover:text-primary transition-colors">
                    <i class="fa fa-angle-right"></i>
                </a>
            </nav>
        </div>
    </main>

    <!-- 页脚 -->
    <footer class="bg-gray-800 text-white py-10 mt-10">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-lg font-bold mb-4">购物指南</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">新手上路</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">售后规则</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">消费者保障</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">争议处理</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-bold mb-4">付款方式</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">支付宝</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">微信支付</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">银行卡支付</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">货到付款</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-bold mb-4">商家服务</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">商家入驻</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">商家中心</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">运营服务</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">培训中心</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-bold mb-4">手机淘宝</h3>
                    <div class="bg-white p-2 inline-block rounded-lg">
                        <img src="https://picsum.photos/100/100?random=101" alt="手机淘宝" class="w-24 h-24">
                    </div>
                    <p class="text-gray-400 text-sm mt-2">扫码下载手机淘宝</p>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400 text-sm">
                <p>© 2025 淘宝网 版权所有</p>
                <p class="mt-2">消费者客服热线：400-800-8888</p>
            </div>
        </div>
    </footer>

    <script>
        // 商品卡片悬停效果
        document.querySelectorAll('.product-card-hover').forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-5px)';
                card.style.boxShadow = '0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)';
                card.style.transition = 'all 0.3s ease';
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0)';
                card.style.boxShadow = '0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)';
            });
        });
        
        // 滚动时导航栏效果
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 50) {
                header.classList.add('shadow-md');
                header.classList.add('py-2');
                header.classList.remove('py-3');
            } else {
                header.classList.remove('shadow-md');
                header.classList.add('py-3');
                header.classList.remove('py-2');
            }
        });
    </script>
</body>
</html>