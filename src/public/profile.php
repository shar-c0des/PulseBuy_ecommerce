<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);

    $stmt = $pdo->prepare("UPDATE users SET email = ?, username = ? WHERE id = ?");
    $stmt->execute([$email, $username, $user_id]);
    $_SESSION['username'] = $username;
    $msg = "Profile has been updated.";
}

$stmt = $pdo->prepare("SELECT username, email, avatar FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - LocalTrader</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#FF4400', // Main brand color
                        secondary: '#FFB800',
                        dark: '#333333',
                        light: '#F5F5F5',
                        success: '#00B42A',
                        warning: '#FF7D00',
                        danger: '#F53F3F',
                        info: '#86909C'
                    },
                    fontFamily: {
                        sans: ['Inter', 'Helvetica Neue', 'Arial', 'sans-serif']
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
            .sidebar-active {
                @apply bg-primary/10 text-primary font-medium border-l-4 border-primary;
            }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <!-- Top Navigation -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="container mx-auto px-4 py-3 flex flex-col md:flex-row justify-between items-center">
            <div class="flex items-center space-x-2 w-full md:w-auto">
                <i class="fa fa-shopping-bag text-primary text-2xl"></i>
                <span class="text-xl font-bold text-dark">LocalTrader</span>
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
                    <span class="hidden md:inline ml-1">My Account</span>
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
    </header>

    <!-- Main Content Area -->
    <div class="flex-1 container mx-auto px-4 py-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">My Account</h1>
            <p class="text-gray-500 mt-1">Manage your personal information and account settings</p>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Left Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="bg-gradient-orange p-4 text-white">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center">
                                <i class="fa fa-user text-white"></i>
                            </div>
                            <div>
                                <h3 class="font-medium"><?php echo htmlspecialchars($user['username']); ?></h3>
                                <p class="text-sm text-white/80"><?php echo htmlspecialchars($user['email']); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <nav class="p-2">
                        <h4 class="text-xs uppercase text-gray-400 font-semibold mb-2 px-3 mt-4">Account Management</h4>
                        <ul>
                            <li>
                                <a href="profile.php" class="flex items-center space-x-3 px-3 py-2 rounded-lg sidebar-active">
                                    <i class="fa fa-user-circle"></i>
                                    <span>Profile</span>
                                </a>
                            </li>
                            <li>
                                <a href="security.php" class="flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                                    <i class="fa fa-lock"></i>
                                    <span>Account Security</span>
                                </a>
                            </li>
                            <li>
                                <a href="address.php" class="flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                                    <i class="fa fa-map-marker-alt"></i>
                                    <span>Shipping Address</span>
                                </a>
                            </li>
                        </ul>
                        
                        <h4 class="text-xs uppercase text-gray-400 font-semibold mb-2 px-3 mt-6">My Transactions</h4>
                        <ul>
                            <li>
                                <a href="#" class="flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                                    <i class="fa fa-shopping-cart"></i>
                                    <span>All Orders</span>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                                    <i class="fa fa-clock"></i>
                                    <span>Pending Payment</span>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                                    <i class="fa fa-truck"></i>
                                    <span>Pending Receipt</span>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                                    <i class="fa fa-star"></i>
                                    <span>Pending Review</span>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                                    <i class="fa fa-exchange-alt"></i>
                                    <span>Refund/After-sales</span>
                                </a>
                            </li>
                        </ul>
                        
                        <h4 class="text-xs uppercase text-gray-400 font-semibold mb-2 px-3 mt-6">My Favorites</h4>
                        <ul>
                            <li>
                                <a href="#" class="flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                                    <i class="fa fa-heart"></i>
                                    <span>Favorite Products</span>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                                    <i class="fa fa-store"></i>
                                    <span>Favorite Shops</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
            
            <!-- Right Content -->
            <div class="lg:col-span-3">
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Profile</h2>
                    
                    <?php if (isset($msg)): ?>
                        <div class="bg-success/10 border border-success/30 text-success p-3 rounded-lg mb-6 flex items-center">
                            <i class="fa fa-check-circle mr-2"></i>
                            <span><?php echo htmlspecialchars($msg); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username <span class="text-danger">*</span></label>
                                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required
                                    class="block w-full rounded-lg border border-gray-300 py-3 px-4 form-input-focus"
                                    placeholder="Enter username">
                            </div>
                            
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-danger">*</span></label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required
                                    class="block w-full rounded-lg border border-gray-300 py-3 px-4 form-input-focus"
                                    placeholder="Enter email">
                            </div>
                        </div>
                        
                        <div class="border-t border-gray-100 pt-6">
                            <h3 class="text-lg font-medium text-gray-800 mb-4">Profile Picture</h3>
                            <div class="flex items-center">
                                <div class="w-20 h-20 rounded-full bg-gray-200 overflow-hidden mr-4">
                                    <img src="<?php echo $user['avatar'] ?: 'https://picsum.photos/200/200?random=10'; ?>" alt="Profile Picture" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <button type="button" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                                        <i class="fa fa-upload mr-2"></i>Upload Picture
                                    </button>
                                    <p class="text-xs text-gray-500 mt-2">Supports JPG, PNG formats. Recommended size: 200×200px</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                            <a href="javascript:history.back()" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                                Back
                            </a>
                            <button type="submit" class="px-6 py-3 bg-primary text-white rounded-lg btn-hover">
                                <i class="fa fa-save mr-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Account Security</h2>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:border-primary/50 transition-colors">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary mr-3">
                                    <i class="fa fa-lock"></i>
                                </div>
                                <div>
                                    <h3 class="font-medium">Login Password</h3>
                                    <p class="text-sm text-gray-500">Last modified: 3 months ago</p>
                                </div>
                            </div>
                            <a href="change_password.php" class="text-primary hover:text-primary/80">Change</a>
                        </div>
                        
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:border-primary/50 transition-colors">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary mr-3">
                                    <i class="fa fa-mobile-alt"></i>
                                </div>
                                <div>
                                    <h3 class="font-medium">Phone Number</h3>
                                    <p class="text-sm text-gray-500">Bound: 138****1234</p>
                                </div>
                            </div>
                            <a href="bind_phone.php" class="text-primary hover:text-primary/80">Change</a>
                        </div>
                        
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:border-primary/50 transition-colors">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary mr-3">
                                    <i class="fa fa-shield-alt"></i>
                                </div>
                                <div>
                                    <h3 class="font-medium">ID Verification</h3>
                                    <p class="text-sm text-gray-500">Not verified</p>
                                </div>
                            </div>
                            <a href="verify_id.php" class="text-primary hover:text-primary/80">Verify Now</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-10 mt-10">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-lg font-bold mb-4">Shopping Guide</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">Getting Started</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">After-sales Rules</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">Consumer Protection</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">Dispute Resolution</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-bold mb-4">Payment Methods</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">Credit Card</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">PayPal</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">Bank Transfer</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">Cash on Delivery</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-bold mb-4">Seller Services</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">Become a Seller</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">Seller Center</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">Operations Service</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">Training Center</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-bold mb-4">Mobile App</h3>
                    <div class="bg-white p-2 inline-block rounded-lg">
                        <img src="https://picsum.photos/100/100?random=101" alt="Mobile App" class="w-24 h-24">
                    </div>
                    <p class="text-gray-400 text-sm mt-2">Scan to download our mobile app</p>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400 text-sm">
                <p>© 2025 LocalTrader. All rights reserved.</p>
                <p class="mt-2">Customer Service: 400-800-8888</p>
            </div>
        </div>
    </footer>

    <script>
        // Form interaction effects
        document.querySelectorAll('input, select, textarea').forEach(input => {
            input.addEventListener('focus', () => {
                input.parentElement.classList.add('scale-[1.01]');
                input.parentElement.style.transition = 'all 0.2s ease';
            });
            
            input.addEventListener('blur', () => {
                input.parentElement.classList.remove('scale-[1.01]');
            });
        });
        
        // Navigation bar scroll effects
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 50) {
                header.classList.add('shadow-md');
            } else {
                header.classList.remove('shadow-md');
            }
        });
    </script>
</body>
</html>