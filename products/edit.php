<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header('Location: ../src/public/loginSignup.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND user_id = ?");
$stmt->execute([$product_id, $user_id]);
$product = $stmt->fetch();

if (!$product) {
    echo "Product not found or access denied.";
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
        if (empty($title) || $price <= 0 || $category_id <= 0) {
            throw new Exception('Please fill in all required fields correctly.');
        }

        // Handle image upload
        $image_path = $_POST['current_image']; // Keep existing image by default
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('File upload error: ' . $_FILES['image']['error'] . '. (Check PHP upload_max_filesize and post_max_size settings)');
            }
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $filename = $_FILES['image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $fileSize = $_FILES['image']['size'];
            if (!in_array($ext, $allowed)) {
                throw new Exception('Invalid image format. Allowed formats: ' . implode(', ', $allowed));
            }
            if ($fileSize > 2 * 1024 * 1024) {
                throw new Exception('Image size must be less than 2MB. Your file is ' . round($fileSize/1024/1024,2) . 'MB.');
            }
            $new_filename = uniqid('prod_', true) . '.' . $ext;
            $upload_path = '../src/uploads/products/' . $new_filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Delete old image if exists
                if (!empty($_POST['current_image'])) {
                    $old_image_path = '../' . $_POST['current_image'];
                    if (file_exists($old_image_path)) {
                        unlink($old_image_path);
                    }
                }
                $image_path = 'src/uploads/products/' . $new_filename;
            } else {
                throw new Exception('Failed to move uploaded file. Check directory permissions for src/uploads/products/.');
            }
        }

        // Update product
        $stmt = $pdo->prepare("UPDATE products SET 
            title = ?, 
            description = ?, 
            price = ?, 
            category_id = ?, 
            status = ?,
            image_path = ?,
            updated_at = NOW()
            WHERE id = ? AND user_id = ?");
        $stmt->execute([
            $title, 
            $description, 
            $price, 
            $category_id, 
            $status,
            $image_path,
            $product_id, 
            $user_id
        ]);
        $_SESSION['success'] = 'Product updated successfully!';
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
    <title>Edit Product - PulseBuy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --gmarket-blue: #0c49c9;
            --gmarket-light-blue: #e4eaff;
            --gmarket-orange: #ff6c37;
            --gmarket-yellow: #ffc620;
            --gmarket-gray: #f5f7fa;
            --gmarket-dark: #22252a;
            --gmarket-medium: #777b8c;
            --gmarket-success: #00a870;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #fafafa;
            color: var(--gmarket-dark);
            line-height: 1.6;
        }

        .breadcrumb {
            background: white;
            padding: 18px 60px;
            border-bottom: 1px solid #eaeef4;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .breadcrumb-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            font-size: 14px;
            color: #777b8c;
        }

        .breadcrumb a {
            color: var(--gmarket-blue);
            text-decoration: none;
            transition: color 0.2s;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .breadcrumb-divider {
            margin: 0 10px;
            color: #d0d2db;
        }

        .breadcrumb-current {
            color: #43465c;
            font-weight: 600;
        }

        .edit-container {
            display: flex;
            max-width: 1200px;
            margin: 30px auto 60px;
            gap: 30px;
        }

        .edit-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.06);
            padding: 30px;
            flex-grow: 1;
            min-height: 600px;
        }

        .edit-card-header {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #f0f2f7;
        }

        .edit-card-title {
            display: flex;
            align-items: center;
            font-size: 24px;
            font-weight: 700;
            color: var(--gmarket-dark);
            gap: 10px;
        }

        .edit-card-title i {
            color: var(--gmarket-blue);
            font-size: 22px;
        }

        .edit-card-subtitle {
            font-size: 15px;
            color: var(--gmarket-medium);
            margin-top: 8px;
            margin-left: 34px;
        }

        .form-layout {
            display: grid;
            grid-template-columns: 1fr;
            gap: 25px;
        }

        @media (min-width: 992px) {
            .form-layout {
                grid-template-columns: 1fr 1fr;
            }
        }

        .form-section {
            padding-bottom: 25px;
        }

        .section-title {
            display: flex;
            align-items: center;
            font-size: 17px;
            font-weight: 600;
            margin-bottom: 18px;
            padding-bottom: 8px;
            border-bottom: 2px solid #f0f2f7;
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -2px;
            width: 80px;
            height: 2px;
            background: var(--gmarket-blue);
        }

        .section-title i {
            margin-right: 8px;
            color: var(--gmarket-blue);
        }

        .form-group {
            margin-bottom: 22px;
        }

        label {
            display: block;
            font-weight: 500;
            margin-bottom: 8px;
            color: #4a4e61;
            font-size: 14px;
        }

        .required::after {
            content: "*";
            color: var(--gmarket-orange);
            margin-left: 4px;
        }

        input[type="text"], input[type="number"], select, textarea {
            width: 100%;
            padding: 14px;
            border-radius: 10px;
            border: 1px solid #e0e3ed;
            font-size: 15px;
            background: white;
            transition: all 0.2s;
        }

        input[type="text"]:focus, input[type="number"]:focus, select:focus, textarea:focus {
            border-color: var(--gmarket-blue);
            box-shadow: 0 0 0 4px rgba(12, 73, 201, 0.1);
            outline: none;
        }

        textarea {
            height: 120px;
            resize: vertical;
        }

        .image-upload-container {
            margin-top: 15px;
            background: #f8f9ff;
            border-radius: 12px;
            padding: 20px;
            border: 1px dashed #d0d8fe;
            text-align: center;
        }

        .current-image-preview {
            width: 200px;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
            margin: 0 auto 15px;
            border: 1px solid #f0f2f7;
            background-color: #fafbff;
        }

        .image-upload-box {
            position: relative;
            margin: 10px 0;
        }

        .image-upload-input {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            opacity: 0;
            cursor: pointer;
        }

        .image-upload-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 15px;
            border-radius: 8px;
            background: white;
            border: 1px solid #e0e3ed;
            font-size: 14px;
            color: var(--gmarket-medium);
            transition: all 0.2s;
        }

        .image-upload-label:hover {
            border-color: var(--gmarket-blue);
            background: var(--gmarket-light-blue);
        }

        .image-upload-label i {
            font-size: 28px;
            color: var(--gmarket-blue);
            margin-bottom: 12px;
        }

        .image-upload-note {
            font-size: 13px;
            color: #a0a4b8;
            margin-top: 8px;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #f0f2f7;
        }

        .btn {
            padding: 14px 36px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
        }

        .btn-primary {
            background: var(--gmarket-blue);
            color: white;
            box-shadow: 0 4px 12px rgba(12, 73, 201, 0.25);
        }

        .btn-primary:hover {
            background: #0a3ab0;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(12, 73, 201, 0.3);
        }

        .btn-outline {
            background: transparent;
            color: var(--gmarket-medium);
            border: 1px solid #e0e3ed;
        }

        .btn-outline:hover {
            background: #f5f7fa;
            border-color: var(--gmarket-blue);
            color: var(--gmarket-blue);
        }

        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        .badge-active {
            background: rgba(0, 168, 112, 0.15);
            color: var(--gmarket-success);
        }

        .badge-pending {
            background: rgba(255, 108, 55, 0.15);
            color: var(--gmarket-orange);
        }

        .badge-inactive {
            background: rgba(119, 123, 140, 0.15);
            color: var(--gmarket-medium);
        }

        .alert {
            padding: 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 15px;
        }

        .alert-danger {
            background: #fff2f0;
            color: #ff4d4f;
            border: 1px solid #ffccc7;
        }
        
        @media (max-width: 768px) {
            .breadcrumb {
                padding: 15px 20px;
            }
            
            .edit-container {
                margin: 20px 15px;
                flex-direction: column;
            }
            
            .edit-card {
                padding: 25px 20px;
            }
            
            .edit-card-title {
                font-size: 20px;
            }
        }

        /* Sparky the Bolt Bunny Styles */
        .sparky-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 999;
            display: flex;
            align-items: center;
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
            box-shadow: 0 0 15px rgba(12, 73, 201, 0.3);
            cursor: pointer;
            transition: all 0.3s ease;
            animation: float 3s ease-in-out infinite;
        }

        .sparky:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(12, 73, 201, 0.5);
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }

        .ear {
            width: 10px;
            height: 40px;
            background-color: white;
            border: 1.5px solid var(--gmarket-blue);
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
            background-color: var(--gmarket-blue);
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
            background-color: var(--gmarket-orange);
            border-radius: 50%;
            position: absolute;
            top: 45px;
            left: 47.5px;
        }

        .mouth {
            width: 10px;
            height: 5px;
            border-bottom: 1.5px solid var(--gmarket-orange);
            border-radius: 50%;
            position: absolute;
            top: 50px;
            left: 45px;
        }

        .vest {
            width: 40px;
            height: 30px;
            background-color: var(--gmarket-blue);
            position: absolute;
            top: 60px;
            left: 30px;
            border-radius: 5px;
        }

        .lightning-bolt {
            width: 10px;
            height: 15px;
            background-color: var(--gmarket-yellow);
            clip-path: polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%);
            position: absolute;
            top: 67px;
            left: 45px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.6; }
            100% { opacity: 1; }
        }

        .tail {
            width: 15px;
            height: 15px;
            background-color: white;
            border: 1.5px solid var(--gmarket-blue);
            border-top: none;
            border-right: none;
            border-radius: 50%;
            position: absolute;
            bottom: 10px;
            right: 10px;
            transform: rotate(45deg);
        }
    </style>
</head>
<body>
    <!-- Breadcrumb Navigation -->
    <div class="breadcrumb">
        <div class="breadcrumb-inner">
            <a href="../src/public/index.php">Home</a>
            <span class="breadcrumb-divider">/</span>
            <a href="../src/public/seller_dashboard.php">Seller Dashboard</a>
            <span class="breadcrumb-divider">/</span>
            <a href="../src/public/seller_dashboard.php">Manage Products</a>
            <span class="breadcrumb-divider">/</span>
            <span class="breadcrumb-current">Edit Product</span>
        </div>
    </div>

    <div class="edit-container">
        <div class="edit-card">
            <div class="edit-card-header">
                <h1 class="edit-card-title">
                    <i class="fas fa-pencil-alt"></i>
                    Edit Product
                </h1>
                <div class="edit-card-subtitle">
                    Update your product information and ensure it's ready for customers
                </div>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($product['image_path']); ?>">
                
                <div class="form-layout">
                    <div class="form-section">
                        <h3 class="section-title"><i class="fas fa-info-circle"></i> Product Details</h3>
                        
                        <div class="form-group">
                            <label class="required">Product Title</label>
                            <input type="text" name="title" value="<?php echo htmlspecialchars($product['title']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="required">Price (R)</label>
                            <input type="number" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" step="0.01" min="0" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description"><?php echo htmlspecialchars($product['description']); ?></textarea>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3 class="section-title"><i class="fas fa-tag"></i> Category & Status</h3>
                        
                        <div class="form-group">
                            <label class="required">Category</label>
                            <select name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" <?php echo $category['id'] == $product['category_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Current Status</label>
                            <div style="padding: 15px; background: #f8f9ff; border-radius: 10px;">
                                <?php if ($product['status'] === 'active'): ?>
                                    <span class="status-badge badge-active">Active</span>
                                <?php elseif ($product['status'] === 'pending'): ?>
                                    <span class="status-badge badge-pending">Pending</span>
                                <?php else: ?>
                                    <span class="status-badge badge-inactive">Inactive</span>
                                <?php endif; ?>
                                <div style="font-size: 14px; color: var(--gmarket-medium); margin-top: 10px;">
                                    Products marked as "Active" will appear immediately in the marketplace
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Update Status</label>
                            <select name="status">
                                <option value="active" <?php echo $product['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="pending" <?php echo $product['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="inactive" <?php echo $product['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <h3 class="section-title"><i class="fas fa-image"></i> Product Image</h3>
                            <div class="image-upload-container">
                                <?php if (!empty($product['image_path'])): ?>
                                    <img src="../<?php echo htmlspecialchars($product['image_path']); ?>" class="current-image-preview">
                                <?php else: ?>
                                    <div style="padding: 30px 0; color: #a0a4b8;">
                                        <i class="fas fa-image" style="font-size: 48px; margin-bottom: 15px;"></i>
                                        <div>No image currently selected</div>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="image-upload-box">
                                    <input type="file" name="image" accept="image/*" class="image-upload-input" id="imageUpload">
                                    <label for="imageUpload" class="image-upload-label">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <span>Upload New Image</span>
                                    </label>
                                </div>
                                
                                <div class="image-upload-note">
                                    JPG, PNG, or GIF files (Max 2MB). Leave empty to keep current image.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="../src/public/seller_dashboard.php" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary" onclick="console.log('Update button clicked'); return true;">
                        <i class="fas fa-save"></i> Update Product
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Sparky the Bolt Bunny Animation -->
    <div class="sparky-container">
        <div class="sparky-bubble show">
            <p class="text-sm text-gray-700">Need help editing your product? Click on me for assistance!</p>
        </div>
        <div class="sparky">
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
        // Form submission debugging
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const submitBtn = document.querySelector('button[type="submit"]');
            
            console.log('Form found:', form);
            console.log('Submit button found:', submitBtn);
            
            // Add form submission listener
            form.addEventListener('submit', function(e) {
                console.log('Form submitted!');
                
                // Check form validation
                const title = form.querySelector('input[name="title"]').value;
                const price = form.querySelector('input[name="price"]').value;
                const category = form.querySelector('select[name="category_id"]').value;
                const status = form.querySelector('select[name="status"]').value;
                
                console.log('Form data:', {
                    title: title,
                    price: price,
                    category: category,
                    status: status
                });
                
                // Check if form is valid
                if (!title || !price || !category) {
                    console.log('Form validation failed!');
                    e.preventDefault();
                    alert('Please fill in all required fields.');
                    return false;
                }
                
                console.log('Form is valid, submitting...');
            });
            
            // Add button click listener
            submitBtn.addEventListener('click', function(e) {
                console.log('Submit button clicked!');
            });
        });

        // Sparky animation and interaction
        document.addEventListener('DOMContentLoaded', function() {
            const sparky = document.querySelector('.sparky');
            const bubble = document.querySelector('.sparky-bubble');
            let bubbleVisible = true;
            
            // Toggle bubble visibility on click
            sparky.addEventListener('click', function() {
                bubbleVisible = !bubbleVisible;
                if (bubbleVisible) {
                    bubble.classList.add('show');
                } else {
                    bubble.classList.remove('show');
                }
            });
            
            // Randomly change Sparky's expression
            setInterval(function() {
                const eyes = document.querySelectorAll('.eye');
                const mouth = document.querySelector('.mouth');
                
                // Random expression change
                const random = Math.random();
                if (random > 0.7) {
                    // Wink
                    eyes[0].style.height = '5px';
                    setTimeout(() => {
                        eyes[0].style.height = '10px';
                    }, 500);
                } else if (random < 0.3) {
                    // Smile
                    mouth.style.borderBottom = '2px solid var(--gmarket-orange)';
                    mouth.style.width = '15px';
                    mouth.style.left = '42.5px';
                    setTimeout(() => {
                        mouth.style.borderBottom = '1.5px solid var(--gmarket-orange)';
                        mouth.style.width = '10px';
                        mouth.style.left = '45px';
                    }, 1000);
                }
            }, 5000);
        });
    </script>
</body>
</html>    