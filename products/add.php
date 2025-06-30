<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header('Location: ../src/public/loginSignup.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $price = floatval($_POST['price']);
        $category_id = intval($_POST['category_id']);
        $status = $_POST['status'];

        // Validate input
        if (empty($title) || $price <= 0 || empty($category_id)) {
            throw new Exception('Please fill in all required fields correctly.');
        }

        // Handle image upload
        $image_path = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $filename = $_FILES['image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (!in_array($ext, $allowed)) {
                throw new Exception('Invalid image format. Allowed formats: ' . implode(', ', $allowed));
            }

            $new_filename = uniqid('prod_', true) . '.' . $ext;
            $upload_path = '../src/uploads/products/' . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_path = 'src/uploads/products/' . $new_filename;
            } else {
                throw new Exception('Failed to upload image. Please try again.');
            }
        } else {
            throw new Exception('Product image is required.');
        }

        // Insert new product
        $stmt = $pdo->prepare("INSERT INTO products (user_id, title, description, price, category_id, status, image_path, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        
        $stmt->execute([
            $_SESSION['user_id'],
            $title, 
            $description, 
            $price, 
            $category_id, 
            $status,
            $image_path
        ]);

        $_SESSION['success'] = 'Product added successfully!';
        header('Location: ../src/public/seller_dashboard.php');
        exit();

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$categories = $pdo->query("SELECT id, name FROM categories")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - PulseBuy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        gmarket: {
                            blue: '#0c49c9',
                            lightBlue: '#e4eaff',
                            orange: '#ff6c37',
                            yellow: '#ffc620',
                            gray: '#f5f7fa',
                            dark: '#22252a',
                            medium: '#777b8c',
                            success: '#00a870',
                        }
                    },
                    fontFamily: {
                        poppins: ['Poppins', 'sans-serif'],
                    },
                    animation: {
                        'float': 'float 3s ease-in-out infinite',
                        'pulse-slow': 'pulse 2s infinite',
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-up': 'slideUp 0.5s ease-out',
                        'bounce-subtle': 'bounceSubtle 2s infinite',
                        'pencil-write': 'pencilWrite 2s infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' },
                        },
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(20px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' },
                        },
                        bounceSubtle: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-5px)' },
                        },
                        pencilWrite: {
                            '0%, 100%': { transform: 'rotate(0deg)' },
                            '25%': { transform: 'rotate(-5deg)' },
                            '75%': { transform: 'rotate(5deg)' },
                        }
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
            .text-shadow {
                text-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .card-hover {
                @apply transition-all duration-300 hover:shadow-lg hover:-translate-y-1;
            }
            .input-focus {
                @apply focus:border-gmarket-blue focus:ring-2 focus:ring-gmarket-lightBlue/50 outline-none transition-all duration-200;
            }
            .btn-hover {
                @apply transition-all duration-300 hover:shadow-md;
            }
            .section-title-underline {
                @apply relative after:absolute after:bottom-0 after:left-0 after:h-0.5 after:w-16 after:bg-gmarket-blue;
            }
            .badge {
                @apply inline-block px-3 py-1 rounded-full text-xs font-semibold;
            }
            .clip-path-polygon {
                clip-path: polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%);
            }
        }
    </style>
</head>
<body class="bg-gmarket-gray font-poppins text-gmarket-dark">
    <!-- Breadcrumb Navigation -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <a href="../src/public/index.php" class="text-gmarket-blue hover:text-gmarket-blue/80 transition-colors duration-200">
                        <i class="fa fa-home mr-2"></i> Home
                    </a>
                    <span class="mx-2 text-gmarket-medium">/</span>
                    <a href="../src/public/seller_dashboard.php" class="text-gmarket-blue hover:text-gmarket-blue/80 transition-colors duration-200">
                        Seller Dashboard
                    </a>
                    <span class="mx-2 text-gmarket-medium">/</span>
                    <a href="../src/public/seller_dashboard.php" class="text-gmarket-blue hover:text-gmarket-blue/80 transition-colors duration-200">
                        Manage Products
                    </a>
                    <span class="mx-2 text-gmarket-medium">/</span>
                    <span class="text-gmarket-dark font-semibold">Add Product</span>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 animate-fade-in">
        <div class="mb-8">
            <h1 class="text-[clamp(1.5rem,3vw,2.5rem)] font-bold text-gmarket-dark mb-2 flex items-center">
                <i class="fa fa-plus-circle text-gmarket-blue mr-3 animate-pulse-slow"></i>
                Add New Product
            </h1>
            <p class="text-gmarket-medium">Fill out the form below to add a new product to your store</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Product Add Form Card -->
            <div class="lg:col-span-8 bg-white rounded-xl shadow-md p-6 animate-slide-up" style="animation-delay: 0.1s">
                <?php if (isset($error)): ?>
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
                        <div class="flex items-center">
                            <i class="fa fa-exclamation-circle mr-3"></i>
                            <p><?php echo htmlspecialchars($error); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <!-- Product Details Section -->
                    <div class="bg-gmarket-gray rounded-lg p-5">
                        <h2 class="text-lg font-semibold text-gmarket-dark mb-4 flex items-center section-title-underline pb-2">
                            <i class="fa fa-info-circle text-gmarket-blue mr-2"></i>
                            Product Details
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label for="title" class="block text-sm font-medium text-gmarket-dark mb-1 required">
                                    Product Title
                                </label>
                                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" 
                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 input-focus" required>
                            </div>
                            
                            <div>
                                <label for="price" class="block text-sm font-medium text-gmarket-dark mb-1 required">
                                    Price (R)
                                </label>
                                <input type="number" id="price" name="price" value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>" 
                                    step="0.01" min="0.01" 
                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 input-focus" required>
                            </div>
                            
                            <div class="md:col-span-2">
                                <label for="description" class="block text-sm font-medium text-gmarket-dark mb-1">
                                    Description
                                </label>
                                <textarea id="description" name="description" rows="4" 
                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 input-focus"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Category & Status Section -->
                    <div class="bg-gmarket-gray rounded-lg p-5">
                        <h2 class="text-lg font-semibold text-gmarket-dark mb-4 flex items-center section-title-underline pb-2">
                            <i class="fa fa-tag text-gmarket-blue mr-2"></i>
                            Category & Status
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label for="category_id" class="block text-sm font-medium text-gmarket-dark mb-1 required">
                                    Category
                                </label>
                                <select id="category_id" name="category_id" 
                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 input-focus" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div>
                                <label for="status" class="block text-sm font-medium text-gmarket-dark mb-1">
                                    Status
                                </label>
                                <select id="status" name="status" 
                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 input-focus">
                                    <option value="active" <?php echo (isset($_POST['status']) && $_POST['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="pending" <?php echo (isset($_POST['status']) && $_POST['status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="inactive" <?php echo (isset($_POST['status']) && $_POST['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                            
                            <div class="md:col-span-2">
                                <div class="bg-white p-4 rounded-lg border border-gray-200">
                                    <div class="flex items-center">
                                        <span class="badge bg-gmarket-blue/10 text-gmarket-blue">
                                            New Product
                                        </span>
                                        <p class="ml-3 text-sm text-gmarket-medium">
                                            Products marked as "Active" will appear immediately in the marketplace
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Product Image Section -->
                    <div class="bg-gmarket-gray rounded-lg p-5">
                        <h2 class="text-lg font-semibold text-gmarket-dark mb-4 flex items-center section-title-underline pb-2">
                            <i class="fa fa-image text-gmarket-blue mr-2"></i>
                            Product Image
                        </h2>
                        
                        <div class="space-y-4">
                            <div class="flex justify-center">
                                <div class="w-64 h-64 bg-gray-100 rounded-xl border-2 border-dashed border-gray-300 flex flex-col items-center justify-center">
                                    <i class="fa fa-image text-4xl text-gray-400 mb-2"></i>
                                    <p class="text-gray-500 text-sm">No image selected</p>
                                </div>
                            </div>
                            
                            <div class="relative border-2 border-dashed border-gray-300 rounded-lg p-6 text-center transition-all duration-300 hover:border-gmarket-blue">
                                <input type="file" name="image" accept="image/*" 
                                    class="absolute inset-0 w-full h-full cursor-pointer opacity-0 z-10" required>
                                
                                <div class="space-y-2">
                                    <i class="fa fa-cloud-upload-alt text-3xl text-gmarket-blue"></i>
                                    <p class="text-sm text-gray-600">Drag and drop or click to upload product image</p>
                                    <p class="text-xs text-gray-500">JPG, PNG, or GIF files (Max 2MB)</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                        <a href="../src/public/seller_dashboard.php" 
                            class="px-6 py-3 border border-gray-300 rounded-lg text-gmarket-medium font-medium btn-hover">
                            Cancel
                        </a>
                        <button type="submit" 
                            class="px-6 py-3 bg-gmarket-blue text-white rounded-lg font-medium shadow-md hover:bg-gmarket-blue/90 btn-hover flex items-center">
                            <i class="fa fa-plus mr-2"></i>
                            Add Product
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Sidebar with Helpful Information -->
            <div class="lg:col-span-4 space-y-6">
                <!-- Help Card -->
                <div class="bg-white rounded-xl shadow-md p-6 animate-slide-up card-hover" style="animation-delay: 0.2s">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 rounded-full bg-gmarket-blue/10 flex items-center justify-center text-gmarket-blue">
                            <i class="fa fa-question-circle"></i>
                        </div>
                        <h3 class="ml-3 text-lg font-semibold text-gmarket-dark">Need Help?</h3>
                    </div>
                    <ul class="space-y-3 text-sm">
                        <li class="flex items-start">
                            <i class="fa fa-check-circle text-gmarket-success mt-1 mr-2"></i>
                            <span>All fields marked with * are required</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fa fa-check-circle text-gmarket-success mt-1 mr-2"></i>
                            <span>Product images should be at least 500x500px</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fa fa-check-circle text-gmarket-success mt-1 mr-2"></i>
                            <span>Description should be clear and concise</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fa fa-check-circle text-gmarket-success mt-1 mr-2"></i>
                            <span>Categories help customers find your product</span>
                        </li>
                    </ul>
                    <button id="helpButton" class="mt-4 w-full py-2 border border-gmarket-blue text-gmarket-blue rounded-lg text-sm font-medium hover:bg-gmarket-blue/5 transition-colors duration-200">
                        Contact Support
                    </button>
                </div>
                
                <!-- Tips Card -->
                <div class="bg-white rounded-xl shadow-md p-6 animate-slide-up card-hover" style="animation-delay: 0.3s">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 rounded-full bg-gmarket-yellow/10 flex items-center justify-center text-gmarket-yellow">
                            <i class="fa fa-lightbulb"></i>
                        </div>
                        <h3 class="ml-3 text-lg font-semibold text-gmarket-dark">Selling Tips</h3>
                    </div>
                    <div class="space-y-4">
                        <div class="bg-gmarket-yellow/5 p-3 rounded-lg">
                            <h4 class="font-medium text-gmarket-dark">High Quality Images</h4>
                            <p class="text-sm text-gmarket-medium mt-1">Products with clear, well-lit images sell 3x faster</p>
                        </div>
                        <div class="bg-gmarket-yellow/5 p-3 rounded-lg">
                            <h4 class="font-medium text-gmarket-dark">Competitive Pricing</h4>
                            <p class="text-sm text-gmarket-medium mt-1">Research similar products to set a competitive price</p>
                        </div>
                        <div class="bg-gmarket-yellow/5 p-3 rounded-lg">
                            <h4 class="font-medium text-gmarket-dark">Detailed Descriptions</h4>
                            <p class="text-sm text-gmarket-medium mt-1">Include dimensions, materials, and care instructions</p>
                        </div>
                    </div>
                </div>
                
                <!-- Product Requirements Card -->
                <div class="bg-white rounded-xl shadow-md p-6 animate-slide-up card-hover" style="animation-delay: 0.4s">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 rounded-full bg-gmarket-orange/10 flex items-center justify-center text-gmarket-orange">
                            <i class="fa fa-list-check"></i>
                        </div>
                        <h3 class="ml-3 text-lg font-semibold text-gmarket-dark">Product Requirements</h3>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 mt-1">
                                <i class="fa fa-image text-gmarket-orange"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium">Images</h4>
                                <p class="text-xs text-gmarket-medium">Minimum resolution of 500x500px, max size 2MB</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 mt-1">
                                <i class="fa fa-file-text text-gmarket-orange"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium">Description</h4>
                                <p class="text-xs text-gmarket-medium">Maximum 1000 characters, no HTML or links</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 mt-1">
                                <i class="fa fa-tags text-gmarket-orange"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium">Categories</h4>
                                <p class="text-xs text-gmarket-medium">Select the most relevant category for your product</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 mt-1">
                                <i class="fa fa-shield text-gmarket-orange"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium">Policies</h4>
                                <p class="text-xs text-gmarket-medium">Products must comply with our content policies</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Sparky the Bolt Bunny Animation -->
    <div class="fixed bottom-6 right-6 z-50">
        <div id="sparkyBubble" class="hidden bg-white rounded-lg shadow-xl p-4 max-w-xs mb-4 relative animate-fade-in">
            <div class="absolute -bottom-3 -right-3 w-6 h-6 bg-white transform rotate-45 shadow-lg"></div>
            <p class="text-sm text-gmarket-dark">Hi there! I'm Sparky. Need help adding a new product? Click the button below for assistance.</p>
            <div class="mt-3 flex justify-end">
                <button id="closeBubble" class="text-xs text-gmarket-medium hover:text-gmarket-blue transition-colors duration-200">
                    Close
                </button>
            </div>
        </div>
        
        <div id="sparky" class="w-20 h-20 bg-white rounded-full shadow-lg flex items-center justify-center cursor-pointer animate-float hover:scale-110 transition-transform duration-300">
            <div class="relative w-16 h-16">
                <!-- Ears -->
                <div class="absolute w-3 h-10 bg-white border-2 border-gmarket-blue rounded-full -top-4 left-2 transform -rotate-15"></div>
                <div class="absolute w-3 h-10 bg-white border-2 border-gmarket-blue rounded-full -top-4 right-2 transform rotate-15"></div>
                
                <!-- Eyes -->
                <div class="absolute w-3 h-3 bg-gmarket-blue rounded-full top-4 left-3"></div>
                <div class="absolute w-3 h-3 bg-gmarket-blue rounded-full top-4 right-3"></div>
                
                <!-- Nose -->
                <div class="absolute w-2 h-2 bg-gmarket-orange rounded-full top-8 left-6.5"></div>
                
                <!-- Mouth -->
                <div class="absolute w-3 h-2 border-b-2 border-gmarket-orange rounded-full top-9 left-6"></div>
                
                <!-- Vest -->
                <div class="absolute w-8 h-6 bg-gmarket-blue rounded-lg bottom-1 left-4"></div>
                
                <!-- Lightning Bolt -->
                <div class="absolute w-3 h-5 bg-gmarket-yellow clip-path-polygon bottom-2 left-5.5 animate-pulse-slow"></div>
                
                <!-- Tail -->
                <div class="absolute w-4 h-4 bg-white border-2 border-gmarket-blue rounded-full border-t-0 border-r-0 transform rotate-45 bottom-1 right-1"></div>
            </div>
        </div>
    </div>

    <script>
        // Sparky animation and interaction
        document.addEventListener('DOMContentLoaded', function() {
            const sparky = document.getElementById('sparky');
            const sparkyBubble = document.getElementById('sparkyBubble');
            const closeBubble = document.getElementById('closeBubble');
            const helpButton = document.getElementById('helpButton');
            
            // Show bubble on page load
            setTimeout(() => {
                sparkyBubble.classList.remove('hidden');
            }, 3000);
            
            // Toggle bubble visibility on click
            sparky.addEventListener('click', function() {
                sparkyBubble.classList.toggle('hidden');
            });
            
            // Close bubble
            closeBubble.addEventListener('click', function() {
                sparkyBubble.classList.add('hidden');
            });
            
            // Help button action
            helpButton.addEventListener('click', function() {
                alert('Support team has been notified! We will contact you shortly.');
            });
            
            // Form field animations
            const formFields = document.querySelectorAll('input, select, textarea');
            formFields.forEach(field => {
                field.addEventListener('focus', function() {
                    this.parentElement.classList.add('scale-[1.01]');
                    this.parentElement.style.transition = 'transform 0.2s ease';
                });
                
                field.addEventListener('blur', function() {
                    this.parentElement.classList.remove('scale-[1.01]');
                });
            });
            
            // Scroll animation for Sparky
            window.addEventListener('scroll', function() {
                const scrollPosition = window.scrollY;
                if (scrollPosition > 50) {
                    sparky.classList.add('opacity-90');
                    sparky.classList.remove('opacity-100');
                } else {
                    sparky.classList.remove('opacity-90');
                    sparky.classList.add('opacity-100');
                }
            });
        });
    </script>
</body>
</html>    