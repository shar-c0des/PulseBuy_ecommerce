<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php?error=access_denied");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - PulseBuy</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700;900&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #0056E0;
            --accent-yellow: #FFC107;
            --secondary-blue: #1A75FF;
            --dark-text: #22252A;
            --medium-text: #525760;
            --light-text: #7B7E85;
            --lighter-text: #A5A8B0;
            --background: #F5F7FA;
            --card-bg: #FFFFFF;
            --border-color: #E5E9EF;
            --shadow-sm: 0 2px 6px rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.08);
            --success: #00C853;
            --white: #FFFFFF;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', 'Noto Sans KR', sans-serif;
        }
        
        body {
            background-color: var(--background);
            color: var(--dark-text);
            line-height: 1.6;
        }
        
        /* Header */
        .top-nav {
            background-color: var(--primary-blue);
            color: var(--white);
            padding: 15px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .logo-area {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .logo {
            display: flex;
            align-items: center;
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: var(--white);
            text-decoration: none;
        }
        
        .logo i {
            color: var(--accent-yellow);
            margin-right: 8px;
            font-size: 26px;
        }
        
        .logo span {
            color: var(--accent-yellow);
        }
        
        .search-container {
            flex: 1;
            max-width: 600px;
            margin: 0 30px;
            position: relative;
        }
        
        .search-bar {
            width: 100%;
            padding: 12px 20px 12px 48px;
            border: none;
            border-radius: 30px;
            font-size: 16px;
            background: rgba(255, 255, 255, 0.15);
            color: var(--white);
            transition: all 0.3s;
        }
        
        .search-bar::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }
        
        .search-bar:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.25);
        }
        
        .search-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.7);
            font-size: 17px;
        }
        
        .user-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .user-link {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--white);
            text-decoration: none;
            font-weight: 500;
            padding: 6px 12px;
            border-radius: 5px;
            transition: background 0.2s;
            font-size: 14px;
        }
        
        .user-link:hover {
            background: rgba(255, 255, 255, 0.15);
        }
        
        .nav-btn {
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.15);
            color: var(--white);
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .nav-btn:hover {
            background: rgba(255, 255, 255, 0.25);
        }
        
        /* Products Content */
        .products-container {
            max-width: 1400px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .section-title {
            font-size: 32px;
            font-weight: 700;
            color: var(--dark-text);
        }
        
        .category-filter {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .category-btn {
            padding: 10px 20px;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 30px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .category-btn.active, .category-btn:hover {
            background: var(--primary-blue);
            color: var(--white);
            border-color: var(--primary-blue);
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }
        
        .product-card {
            background: var(--card-bg);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s;
            border: 1px solid var(--border-color);
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }
        
        .product-image {
            width: 100%;
            height: 200px;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .product-image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .product-info {
            padding: 20px;
        }
        
        .product-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--dark-text);
        }
        
        .product-price {
            font-size: 22px;
            font-weight: 700;
            color: var(--primary-blue);
            margin-bottom: 15px;
        }
        
        .product-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 14px;
            color: var(--light-text);
        }
        
        .product-rating {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .product-rating i {
            color: var(--accent-yellow);
        }
        
        .product-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .cart-btn {
            flex: 1;
            background: var(--primary-blue);
            color: var(--white);
            border: none;
            padding: 10px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .cart-btn:hover {
            background: #0048b8;
        }
        
        .wishlist-btn {
            width: 40px;
            height: 40px;
            background: var(--background);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .wishlist-btn:hover {
            background: #ffebee;
            border-color: #f44336;
            color: #f44336;
        }
        
        /* Footer */
        .footer {
            background: var(--card-bg);
            padding: 40px 5%;
            border-top: 1px solid var(--border-color);
        }
        
        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 30px;
        }
        
        .footer-section {
            flex: 1;
            min-width: 200px;
        }
        
        .footer-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--dark-text);
        }
        
        .footer-links {
            list-style: none;
        }
        
        .footer-links li {
            margin-bottom: 10px;
        }
        
        .footer-links a {
            color: var(--medium-text);
            text-decoration: none;
            transition: color 0.2s;
        }
        
        .footer-links a:hover {
            color: var(--primary-blue);
        }
        
        .footer-bottom {
            max-width: 1200px;
            margin: 30px auto 0;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
            text-align: center;
            color: var(--medium-text);
            font-size: 14px;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .top-nav {
                flex-direction: column;
                gap: 15px;
            }
            
            .search-container {
                order: 3;
                width: 100%;
                margin: 15px 0 0;
            }
            
            .section-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <!-- Top Navigation -->
    <nav class="top-nav">
        <div class="logo-area">
            <a href="index.php" class="logo">
                <i class="fas fa-bolt"></i> Pulse<span>Buy</span>
            </a>
        </div>
        
        <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-bar" placeholder="Search for products, brands and categories">
        </div>
        
        <div class="user-actions">
            <a href="cart.php" class="user-link">
                <i class="fas fa-shopping-cart"></i>
                <span>Cart</span>
            </a>
            <a href="profile.php" class="user-link">
                <i class="fas fa-user"></i>
                <span><?= htmlspecialchars($_SESSION['username']) ?></span>
            </a>
            <a href="logout.php" class="nav-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Log out</span>
            </a>
        </div>
    </nav>
    
    <!-- Products Content -->
    <div class="products-container">
        <div class="section-header">
            <h1 class="section-title">Featured Products</h1>
            
            <div class="category-filter">
                <button class="category-btn active">All</button>
                <button class="category-btn">Electronics</button>
                <button class="category-btn">Fashion</button>
                <button class="category-btn">Home & Garden</button>
                <button class="category-btn">Beauty</button>
                <button class="category-btn">Sports</button>
            </div>
        </div>
        
        <div class="products-grid">
            <!-- Product 1 -->
            <div class="product-card">
                <div class="product-image">
                    <img src="https://via.placeholder.com/300x300?text=Smartphone" alt="Smartphone">
                </div>
                <div class="product-info">
                    <h3 class="product-title">Premium Smartphone XT-200</h3>
                    <div class="product-price">₩899,000</div>
                    <div class="product-meta">
                        <div class="product-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                            <span>(128)</span>
                        </div>
                        <div class="product-stock">In Stock</div>
                    </div>
                    <div class="product-actions">
                        <button class="cart-btn">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                        <button class="wishlist-btn">
                            <i class="fas fa-heart"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Product 2 -->
            <div class="product-card">
                <div class="product-image">
                    <img src="https://via.placeholder.com/300x300?text=Headphones" alt="Headphones">
                </div>
                <div class="product-info">
                    <h3 class="product-title">Wireless Headphones Pro</h3>
                    <div class="product-price">₩189,000</div>
                    <div class="product-meta">
                        <div class="product-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <span>(92)</span>
                        </div>
                        <div class="product-stock">In Stock</div>
                    </div>
                    <div class="product-actions">
                        <button class="cart-btn">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                        <button class="wishlist-btn">
                            <i class="fas fa-heart"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Product 3 -->
            <div class="product-card">
                <div class="product-image">
                    <img src="https://via.placeholder.com/300x300?text=Smart+Watch" alt="Smart Watch">
                </div>
                <div class="product-info">
                    <h3 class="product-title">Smart Fitness Tracker</h3>
                    <div class="product-price">₩125,000</div>
                    <div class="product-meta">
                        <div class="product-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="far fa-star"></i>
                            <span>(75)</span>
                        </div>
                        <div class="product-stock">In Stock</div>
                    </div>
                    <div class="product-actions">
                        <button class="cart-btn">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                        <button class="wishlist-btn">
                            <i class="fas fa-heart"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Product 4 -->
            <div class="product-card">
                <div class="product-image">
                    <img src="https://via.placeholder.com/300x300?text=Blender" alt="Blender">
                </div>
                <div class="product-info">
                    <h3 class="product-title">Smart Kitchen Blender</h3>
                    <div class="product-price">₩198,000</div>
                    <div class="product-meta">
                        <div class="product-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                            <i class="far fa-star"></i>
                            <span>(43)</span>
                        </div>
                        <div class="product-stock">In Stock</div>
                    </div>
                    <div class="product-actions">
                        <button class="cart-btn">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                        <button class="wishlist-btn">
                            <i class="fas fa-heart"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Product 5 -->
            <div class="product-card">
                <div class="product-image">
                    <img src="https://via.placeholder.com/300x300?text=Yoga+Mat" alt="Yoga Mat">
                </div>
                <div class="product-info">
                    <h3 class="product-title">Premium Yoga Mat</h3>
                    <div class="product-price">₩75,800</div>
                    <div class="product-meta">
                        <div class="product-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                            <span>(57)</span>
                        </div>
                        <div class="product-stock">In Stock</div>
                    </div>
                    <div class="product-actions">
                        <button class="cart-btn">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                        <button class="wishlist-btn">
                            <i class="fas fa-heart"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Product 6 -->
            <div class="product-card">
                <div class="product-image">
                    <img src="https://via.placeholder.com/300x300?text=T-Shirt" alt="T-Shirt">
                </div>
                <div class="product-info">
                    <h3 class="product-title">Summer Fashion T-Shirt</h3>
                    <div class="product-price">₩28,900</div>
                    <div class="product-meta">
                        <div class="product-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="far fa-star"></i>
                            <span>(112)</span>
                        </div>
                        <div class="product-stock">In Stock</div>
                    </div>
                    <div class="product-actions">
                        <button class="cart-btn">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                        <button class="wishlist-btn">
                            <i class="fas fa-heart"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-section">
                <h3 class="footer-title">Customer Service</h3>
                <ul class="footer-links">
                    <li><a href="help.php">Help Center</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                    <li><a href="returns.php">Return Policy</a></li>
                    <li><a href="shipping.php">Shipping Information</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3 class="footer-title">About PulseBuy</h3>
                <ul class="footer-links">
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="careers.php">Careers</a></li>
                    <li><a href="press.php">Press</a></li>
                    <li><a href="corporate.php">Corporate Information</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3 class="footer-title">Legal</h3>
                <ul class="footer-links">
                    <li><a href="terms.php">Terms of Service</a></li>
                    <li><a href="privacy.php">Privacy Policy</a></li>
                    <li><a href="ip.php">Intellectual Property</a></li>
                    <li><a href="cookies.php">Cookies Policy</a></li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; 2025 PulseBuy. All rights reserved.</p>
        </div>
    </footer>
    
    <script>
        // Category filter functionality
        const categoryBtns = document.querySelectorAll('.category-btn');
        categoryBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                categoryBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });
        
        // Add to cart functionality
        const cartBtns = document.querySelectorAll('.cart-btn');
        cartBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const productCard = this.closest('.product-card');
                const productTitle = productCard.querySelector('.product-title').textContent;
                alert(`${productTitle} added to cart!`);
            });
        });
        
        // Wishlist functionality
        const wishlistBtns = document.querySelectorAll('.wishlist-btn');
        wishlistBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const icon = this.querySelector('i');
                if (icon.classList.contains('fas')) {
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                    this.style.color = '';
                    this.style.borderColor = '';
                    this.style.background = '';
                } else {
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                    this.style.color = '#f44336';
                    this.style.borderColor = '#f44336';
                    this.style.background = '#ffebee';
                }
            });
        });
    </script>
</body>
</html>