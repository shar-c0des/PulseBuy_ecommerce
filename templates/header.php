<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get cart count (replace with your actual cart logic)
$cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>

<header class="site-header">
    <div class="container">
        <!-- Top Header: Logo, Search, User Actions -->
        <div class="header-top flex flex-col md:flex-row items-center justify-between py-4">
            <!-- Logo -->
            <a href="index.php" class="logo flex items-center mb-4 md:mb-0">
                <i class="fa-solid fa-bolt text-primary text-2xl mr-2"></i>
                <span class="font-bold text-xl">
                    <span class="text-dark">Pulse</span>
                    <span class="text-accent">Buy</span>
                </span>
            </a>

            <!-- Search Bar -->
            <div class="search-bar w-full md:w-auto md:flex-1 max-w-xl mb-4 md:mb-0 md:mx-8 relative">
                <input 
                    type="text" 
                    placeholder="Search products..." 
                    class="search-input w-full py-3 px-4 pr-10 rounded-full border border-gray-200 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all"
                    name="query"
                >
                <button class="search-button absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-primary transition-colors">
                    <i class="fa-solid fa-search text-lg"></i>
                </button>
            </div>

            <!-- User Actions -->
            <div class="user-actions flex items-center">
                <a href="cart.php" class="cart-link relative flex items-center justify-center w-10 h-10 rounded-full hover:bg-gray-100 transition-colors group">
                    <i class="fa-solid fa-shopping-cart text-gray-600 text-lg"></i>
                    <span class="cart-count absolute -top-1 -right-1 bg-accent text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">
                        <?php echo $cartCount; ?>
                    </span>
                    <span class="cart-tooltip absolute -bottom-10 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                        View Cart
                    </span>
                </a>

                <?php if (isset($_SESSION['id'])): ?>
                    <div class="user-menu relative ml-4">
                        <button class="user-button flex items-center space-x-2 text-gray-700 hover:text-primary transition-colors">
                            <i class="fa-solid fa-user-circle text-xl"></i>
                            <span class="hidden md:inline font-medium"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </button>
                        <div class="user-dropdown absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50 opacity-0 invisible transform origin-top-right scale-95 group-hover:opacity-100 group-hover:visible group-hover:scale-100 transition-all duration-200">
                            <?php if ($_SESSION['role'] === 'buyer'): ?>
                                <a href="buyer_dashboard.php" class="dropdown-item flex items-center px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors">
                                    <i class="fa-solid fa-tachometer-alt w-5"></i> Dashboard
                                </a>
                                <a href="/ecommerce_platformv2/src/public/wishlist.php" class="dropdown-item flex items-center px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors">
                                    <i class="fa-solid fa-heart w-5"></i> Wishlist
                                </a>
                                <a href="orders/index.php" class="dropdown-item flex items-center px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors">
                                    <i class="fa-solid fa-shopping-bag w-5"></i> My Orders
                                </a>
                                <a href="#" class="dropdown-item flex items-center px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors">
                                    <i class="fa-solid fa-user w-5"></i> Profile
                                </a>
                            <?php elseif ($_SESSION['role'] === 'seller'): ?>
                                <a href="seller_dashboard.php" class="dropdown-item flex items-center px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors">
                                    <i class="fa-solid fa-store w-5"></i> Seller Dashboard
                                </a>
                                <a href="#" class="dropdown-item flex items-center px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors">
                                    <i class="fa-solid fa-box-open w-5"></i> Manage Products
                                </a>
                            <?php endif; ?>
                            <a href="logout.php" class="dropdown-item flex items-center px-4 py-2 text-red-600 hover:bg-red-50 transition-colors">
                                <i class="fa-solid fa-sign-out-alt w-5"></i> Logout
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="login-link hidden md:inline-flex items-center px-4 py-2 text-gray-700 hover:text-primary transition-colors">
                        <i class="fa-solid fa-sign-in-alt mr-2"></i> Login
                    </a>
                    <a href="register.php" class="register-link hidden md:inline-flex items-center px-4 py-2 bg-primary text-white rounded-full hover:bg-primary/90 transition-colors">
                        <i class="fa-solid fa-user-plus mr-2"></i> Register
                    </a>
                    <a href="login.php" class="md:hidden ml-4 flex items-center justify-center w-10 h-10 rounded-full hover:bg-gray-100 transition-colors">
                        <i class="fa-solid fa-user text-gray-600"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Main Navigation -->
        <nav class="main-nav border-t border-gray-100">
            <div class="nav-wrapper flex items-center justify-between">
                <ul class="nav-list flex overflow-x-auto scrollbar-hide">
                    <li class="nav-item">
                        <a href="index.php" class="nav-link block px-4 py-3 text-gray-700 hover:text-primary font-medium transition-colors whitespace-nowrap">
                            Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="products/browse.php" class="nav-link block px-4 py-3 text-gray-700 hover:text-primary font-medium transition-colors whitespace-nowrap">
                            All Products
                        </a>
                    </li>
                    <li class="nav-item dropdown relative">
                        <a href="#" class="nav-link block px-4 py-3 text-gray-700 hover:text-primary font-medium transition-colors flex items-center whitespace-nowrap">
                            Categories
                            <i class="fa-solid fa-chevron-down ml-1 text-xs"></i>
                        </a>
                        <div class="dropdown-menu absolute left-0 mt-1 w-60 bg-white rounded-lg shadow-lg py-2 z-50 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                            <a href="#" class="dropdown-item flex items-center px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors">
                                <i class="fa-solid fa-laptop w-5"></i> Electronics
                            </a>
                            <a href="#" class="dropdown-item flex items-center px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors">
                                <i class="fa-solid fa-tshirt w-5"></i> Clothing
                            </a>
                            <a href="#" class="dropdown-item flex items-center px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors">
                                <i class="fa-solid fa-home w-5"></i> Home & Kitchen
                            </a>
                            <a href="#" class="dropdown-item flex items-center px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors">
                                <i class="fa-solid fa-gift w-5"></i> Gifts & Accessories
                            </a>
                            <a href="#" class="dropdown-item flex items-center px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors">
                                <i class="fa-solid fa-book w-5"></i> Books & Media
                            </a>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link block px-4 py-3 text-gray-700 hover:text-primary font-medium transition-colors whitespace-nowrap">
                            Deals
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link block px-4 py-3 text-gray-700 hover:text-primary font-medium transition-colors whitespace-nowrap">
                            About
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link block px-4 py-3 text-gray-700 hover:text-primary font-medium transition-colors whitespace-nowrap">
                            Contact
                        </a>
                    </li>
                </ul>
                <div class="nav-actions hidden md:flex items-center">
                    <a href="#" class="nav-action flex items-center text-gray-700 hover:text-primary transition-colors">
                        <i class="fa-solid fa-truck mr-2"></i>
                        <span>Track Order</span>
                    </a>
                    <a href="#" class="nav-action flex items-center text-gray-700 hover:text-primary transition-colors ml-6">
                        <i class="fa-solid fa-phone mr-2"></i>
                        <span>Support</span>
                    </a>
                </div>
            </div>
        </nav>

        <!-- Mobile Menu Toggle -->
        <div class="mobile-menu-toggle md:hidden flex justify-between items-center py-3 border-t border-gray-100">
            <button id="mobile-menu-button" class="text-gray-700 focus:outline-none">
                <i class="fa-solid fa-bars text-xl"></i>
            </button>
            <div class="flex items-center space-x-4">
                <a href="#" class="text-gray-700">
                    <i class="fa-solid fa-truck"></i>
                </a>
                <a href="#" class="text-gray-700">
                    <i class="fa-solid fa-phone"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Mobile Menu (Hidden by default) -->
    <div id="mobile-menu" class="mobile-menu md:hidden bg-white shadow-lg absolute w-full left-0 z-40 transform -translate-y-full transition-transform duration-300 ease-in-out">
        <div class="container py-4">
            <ul class="mobile-nav-list">
                <li class="mobile-nav-item">
                    <a href="index.php" class="mobile-nav-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                        <i class="fa-solid fa-home w-5 text-gray-500"></i> Home
                    </a>
                </li>
                <li class="mobile-nav-item">
                    <a href="products/browse.php" class="mobile-nav-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                        <i class="fa-solid fa-shopping-bag w-5 text-gray-500"></i> All Products
                    </a>
                </li>
                <li class="mobile-nav-item mobile-dropdown">
                    <button class="mobile-dropdown-button flex items-center justify-between w-full px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                        <span><i class="fa-solid fa-th-large w-5 text-gray-500"></i> Categories</span>
                        <i class="fa-solid fa-chevron-down text-gray-500"></i>
                    </button>
                    <div class="mobile-dropdown-menu mt-2 ml-4 bg-gray-50 rounded-lg overflow-hidden">
                        <a href="#" class="mobile-dropdown-item block px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors">Electronics</a>
                        <a href="#" class="mobile-dropdown-item block px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors">Clothing</a>
                        <a href="#" class="mobile-dropdown-item block px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors">Home & Kitchen</a>
                        <a href="#" class="mobile-dropdown-item block px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors">Gifts & Accessories</a>
                        <a href="#" class="mobile-dropdown-item block px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors">Books & Media</a>
                    </div>
                </li>
                <li class="mobile-nav-item">
                    <a href="#" class="mobile-nav-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                        <i class="fa-solid fa-tags w-5 text-gray-500"></i> Deals
                    </a>
                </li>
                <li class="mobile-nav-item">
                    <a href="#" class="mobile-nav-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                        <i class="fa-solid fa-info-circle w-5 text-gray-500"></i> About
                    </a>
                </li>
                <li class="mobile-nav-item">
                    <a href="#" class="mobile-nav-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                        <i class="fa-solid fa-envelope w-5 text-gray-500"></i> Contact
                    </a>
                </li>
                <li class="mobile-nav-item">
                    <a href="#" class="mobile-nav-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                        <i class="fa-solid fa-truck w-5 text-gray-500"></i> Track Order
                    </a>
                </li>
                <li class="mobile-nav-item">
                    <a href="#" class="mobile-nav-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                        <i class="fa-solid fa-phone w-5 text-gray-500"></i> Support
                    </a>
                </li>
                <?php if (!isset($_SESSION['id'])): ?>
                    <li class="mobile-nav-item">
                        <a href="login.php" class="mobile-nav-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                            <i class="fa-solid fa-sign-in-alt w-5 text-gray-500"></i> Login
                        </a>
                    </li>
                    <li class="mobile-nav-item">
                        <a href="register.php" class="mobile-nav-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                            <i class="fa-solid fa-user-plus w-5 text-gray-500"></i> Register
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</header>

<script>
    // Mobile menu toggle
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        
        if (mobileMenuButton && mobileMenu) {
            mobileMenuButton.addEventListener('click', function() {
                if (mobileMenu.classList.contains('-translate-y-full')) {
                    mobileMenu.classList.remove('-translate-y-full');
                    mobileMenu.classList.add('translate-y-0');
                } else {
                    mobileMenu.classList.remove('translate-y-0');
                    mobileMenu.classList.add('-translate-y-full');
                }
            });
        }
        
        // Mobile dropdown toggle
        const mobileDropdownButton = document.querySelector('.mobile-dropdown-button');
        const mobileDropdownMenu = document.querySelector('.mobile-dropdown-menu');
        
        if (mobileDropdownButton && mobileDropdownMenu) {
            mobileDropdownButton.addEventListener('click', function() {
                mobileDropdownMenu.classList.toggle('hidden');
                const icon = mobileDropdownButton.querySelector('i');
                if (mobileDropdownMenu.classList.contains('hidden')) {
                    icon.classList.remove('fa-chevron-up');
                    icon.classList.add('fa-chevron-down');
                } else {
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-up');
                }
            });
        }
        
        // User dropdown toggle
        const userButton = document.querySelector('.user-button');
        const userDropdown = document.querySelector('.user-dropdown');
        
        if (userButton && userDropdown) {
            userButton.addEventListener('click', function() {
                userDropdown.classList.toggle('active');
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                if (!userButton.contains(event.target) && !userDropdown.contains(event.target)) {
                    userDropdown.classList.remove('active');
                }
            });
        }
        
        // Nav dropdown toggle
        const dropdowns = document.querySelectorAll('.dropdown');
        
        dropdowns.forEach(dropdown => {
            const dropdownLink = dropdown.querySelector('.nav-link');
            const dropdownMenu = dropdown.querySelector('.dropdown-menu');
            
            dropdownLink.addEventListener('click', function(e) {
                e.preventDefault();
                dropdownMenu.classList.toggle('active');
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                if (!dropdown.contains(event.target)) {
                    dropdownMenu.classList.remove('active');
                }
            });
        });
    });
</script>

<style>
    /* Variables */
    :root {
        --primary: #0050b5;       /* Main blue */
        --accent: #ffd166;        /* Accent yellow */
        --dark: #212529;          /* Dark text */
        --light: #f8f9fa;         /* Light background */
        --gray: #e9ecef;          /* Gray border */
        --gray-dark: #495057;     /* Dark gray text */
        --pink: #ff6b6b;          /* Pink accent */
        --white: #ffffff;         /* White */
    }

    /* Global styles */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Inter', sans-serif;
    }

    body {
        font-size: 16px;
        line-height: 1.5;
        color: var(--dark);
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 15px;
    }

    .flex {
        display: flex;
    }

    .items-center {
        align-items: center;
    }

    .justify-between {
        justify-content: between;
    }

    .mb-4 {
        margin-bottom: 1rem;
    }

    .ml-4 {
        margin-left: 1rem;
    }

    .px-4 {
        padding-left: 1rem;
        padding-right: 1rem;
    }

    .py-3 {
        padding-top: 0.75rem;
        padding-bottom: 0.75rem;
    }

    .rounded-full {
        border-radius: 9999px;
    }

    .hover:bg-gray-100:hover {
        background-color: #f3f4f6;
    }

    .transition-colors {
        transition: color 0.2s ease, background-color 0.2s ease;
    }

    /* Header styles */
    .site-header {
        background-color: var(--white);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        position: sticky;
        top: 0;
        z-index: 100;
    }

    .header-top {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem 0;
    }

    .logo {
        display: flex;
        align-items: center;
        text-decoration: none;
        font-size: 1.5rem;
        font-weight: bold;
    }

    .logo i {
        color: var(--primary);
        margin-right: 0.5rem;
    }

    .text-primary {
        color: var(--primary);
    }

    .text-accent {
        color: var(--accent);
    }

    .text-dark {
        color: var(--dark);
    }

    .search-bar {
        position: relative;
    }

    .search-input {
        width: 100%;
        padding: 0.75rem 2.5rem 0.75rem 1rem;
        border: 1px solid var(--gray);
        border-radius: 9999px;
        outline: none;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .search-input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(0, 80, 181, 0.2);
    }

    .search-button {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        color: var(--gray-dark);
        transition: color 0.2s ease;
    }

    .search-button:hover {
        color: var(--primary);
    }

    .user-actions {
        display: flex;
        align-items: center;
    }

    .cart-link {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 50%;
        transition: background-color 0.2s ease;
    }

    .cart-link:hover {
        background-color: var(--light);
    }

    .cart-count {
        position: absolute;
        top: -0.25rem;
        right: -0.25rem;
        background-color: var(--pink);
        color: var(--white);
        font-size: 0.75rem;
        font-weight: bold;
        width: 1.25rem;
        height: 1.25rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .cart-tooltip {
        position: absolute;
        bottom: -1.5rem;
        left: 50%;
        transform: translateX(-50%);
        background-color: var(--dark);
        color: var(--white);
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.2s ease, visibility 0.2s ease;
        white-space: nowrap;
    }

    .cart-link:hover .cart-tooltip {
        opacity: 1;
        visibility: visible;
    }

    .user-menu {
        position: relative;
        margin-left: 1rem;
    }

    .user-button {
        display: flex;
        align-items: center;
        background: none;
        border: none;
        cursor: pointer;
        color: var(--dark);
        transition: color 0.2s ease;
    }

    .user-button:hover {
        color: var(--primary);
    }

    .username {
        font-weight: 500;
        margin: 0 0.5rem;
    }

    .user-dropdown {
        position: absolute;
        right: 0;
        top: calc(100% + 0.5rem);
        background-color: var(--white);
        border-radius: 0.5rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        width: 15rem;
        z-index: 100;
        opacity: 0;
        visibility: hidden;
        transform: scale(0.95);
        transform-origin: top right;
        transition: opacity 0.2s ease, visibility 0.2s ease, transform 0.2s ease;
    }

    .user-dropdown.active {
        opacity: 1;
        visibility: visible;
        transform: scale(1);
    }

    .dropdown-item {
        display: flex;
        align-items: center;
        padding: 0.75rem 1rem;
        text-decoration: none;
        color: var(--dark);
        transition: background-color 0.2s ease;
    }

    .dropdown-item:hover {
        background-color: var(--light);
    }

    .text-danger {
        color: var(--pink);
    }

    .login-link, .register-link {
        display: flex;
        align-items: center;
        text-decoration: none;
        padding: 0.5rem 1rem;
        margin-left: 0.5rem;
        transition: background-color 0.2s ease, color 0.2s ease;
    }

    .login-link {
        color: var(--dark);
    }

    .login-link:hover {
        color: var(--primary);
    }

    .register-link {
        background-color: var(--primary);
        color: var(--white);
        border-radius: 9999px;
    }

    .register-link:hover {
        background-color: #004091;
    }

    /* Main navigation styles */
    .main-nav {
        border-top: 1px solid var(--gray);
    }

    .nav-wrapper {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .nav-list {
        display: flex;
        list-style: none;
        overflow-x: auto;
        scrollbar-width: none;
    }

    .nav-list::-webkit-scrollbar {
        display: none;
    }

    .nav-item {
        position: relative;
    }

    .nav-link {
        display: block;
        padding: 0.75rem 1rem;
        text-decoration: none;
        color: var(--dark);
        font-weight: 500;
        transition: color 0.2s ease;
        white-space: nowrap;
    }

    .nav-link:hover {
        color: var(--primary);
    }

    .dropdown {
        position: relative;
    }

    .dropdown-menu {
        position: absolute;
        left: 0;
        top: calc(100% + 0.5rem);
        background-color: var(--white);
        border-radius: 0.5rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        width: 15rem;
        z-index: 100;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.2s ease, visibility 0.2s ease;
    }

    .dropdown:hover .dropdown-menu {
        opacity: 1;
        visibility: visible;
    }

    .dropdown-item {
        display: flex;
        align-items: center;
        padding: 0.75rem 1rem;
        text-decoration: none;
        color: var(--dark);
        transition: background-color 0.2s ease;
    }

    .dropdown-item:hover {
        background-color: var(--light);
    }

    .nav-actions {
        display: flex;
        align-items: center;
    }

    .nav-action {
        display: flex;
        align-items: center;
        text-decoration: none;
        color: var(--dark);
        padding: 0.75rem 1rem;
        transition: color 0.2s ease;
    }

    .nav-action:hover {
        color: var(--primary);
    }

    /* Mobile menu styles */
    .mobile-menu-toggle {
        display: none;
    }

    .mobile-menu {
        position: absolute;
        width: 100%;
        left: 0;
        background-color: var(--white);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        z-index: 50;
        transform: translateY(-100%);
        transition: transform 0.3s ease-in-out;
    }

    .mobile-menu.active {
        transform: translateY(0);
    }

    .mobile-nav-list {
        list-style: none;
    }

    .mobile-nav-item {
        margin-bottom: 0.5rem;
    }

    .mobile-nav-link {
        display: flex;
        align-items: center;
        text-decoration: none;
        color: var(--dark);
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        transition: background-color 0.2s ease;
    }

    .mobile-nav-link:hover {
        background-color: var(--light);
    }

    .mobile-dropdown-button {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        background: none;
        border: none;
        text-align: left;
        color: var(--dark);
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        transition: background-color 0.2s ease;
        cursor: pointer;
    }

    .mobile-dropdown-button:hover {
        background-color: var(--light);
    }

    .mobile-dropdown-menu {
        display: none;
        margin-top: 0.5rem;
        margin-left: 1rem;
        border-radius: 0.5rem;
        background-color: var(--light);
    }

    .mobile-dropdown-menu.active {
        display: block;
    }

    .mobile-dropdown-item {
        display: block;
        text-decoration: none;
        color: var(--dark);
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        transition: background-color 0.2s ease;
    }

    .mobile-dropdown-item:hover {
        background-color: var(--gray);
    }

    /* Responsive styles */
    @media (max-width: 768px) {
        .header-top {
            flex-direction: column;
        }

        .logo, .search-bar, .user-actions {
            width: 100%;
            margin-bottom: 1rem;
        }

        .search-bar {
            margin: 0 0 1rem;
        }

        .user-actions {
            justify-content: center;
        }

        .main-nav, .nav-actions {
            display: none;
        }

        .mobile-menu-toggle {
            display: flex;
        }
    }
</style>