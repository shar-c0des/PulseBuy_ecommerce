<?php
session_start();
require_once '../config/db.php';
require_once '../templates/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header('Location: ../users/login.php');
    exit;
}

$seller_id = $_SESSION['user_id'];
$product_id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND seller_id = ?");
$stmt->execute([$product_id, $seller_id]);
$product = $stmt->fetch();

if (!$product) {
    echo "Product not found or access denied.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category_id = intval($_POST['category_id']);
        $status = $_POST['status'];

        // Validate input
        if (empty($name) || $price <= 0 || $stock < 0) {
            throw new Exception('Please fill in all required fields correctly.');
        }

        // Handle image upload
        $image = $_POST['current_image']; // Keep existing image by default
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (!in_array($ext, $allowed)) {
                throw new Exception('Invalid image format. Allowed formats: ' . implode(', ', $allowed));
            }

            $new_filename = uniqid() . '.' . $ext;
            $upload_path = '../uploads/products/' . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Delete old image if exists
                if (!empty($_POST['current_image'])) {
                    $old_image_path = '../uploads/products/' . $_POST['current_image'];
                    if (file_exists($old_image_path)) {
                        unlink($old_image_path);
                    }
                }
                $image = $new_filename;
            }
        }

        // Update product
        $stmt = $pdo->prepare("UPDATE products SET 
            name = ?, 
            description = ?, 
            price = ?, 
            stock = ?, 
            category_id = ?, 
            status = ?,
            image = ?
            WHERE id = ? AND seller_id = ?");
        
        $stmt->execute([
            $name, 
            $description, 
            $price, 
            $stock, 
            $category_id, 
            $status,
            $image,
            $product_id, 
            $seller_id
        ]);

        // Handle attributes
        if (isset($_POST['attributes'])) {
            // Delete existing attributes
            $stmt = $pdo->prepare("DELETE FROM product_attribute_values WHERE product_id = ?");
            $stmt->execute([$product_id]);

            // Insert new attributes
            $stmt = $pdo->prepare("INSERT INTO product_attribute_values (product_id, attribute_id, value) VALUES (?, ?, ?)");
            foreach ($_POST['attributes'] as $attr_id => $value) {
                if (!empty($value)) {
                    $stmt->execute([$product_id, $attr_id, $value]);
                }
            }
        }

        $_SESSION['success'] = 'Product updated successfully!';
        header('Location: view.php?id=' . $product_id);
        exit();

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$categories = $pdo->query("SELECT id, name FROM categories")->fetchAll();

// Get product attributes
$stmt = $pdo->prepare("SELECT pa.*, pav.value 
                       FROM product_attributes pa 
                       LEFT JOIN product_attribute_values pav ON pa.id = pav.attribute_id AND pav.product_id = ?
                       ORDER BY pa.attribute_name");
$stmt->execute([$product_id]);
$attributes = $stmt->fetchAll();
?>

<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Edit Product</h1>
                <a href="view.php?id=<?php echo $product_id; ?>" class="btn btn-secondary">
                    <i class="fa fa-arrow-left mr-1"></i>
                    Back to Product
                </a>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger mb-4">
                    <?php echo htmlspecialchars($error); ?>
                        </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($product['image']); ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" 
                                   class="form-control" required>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Price *</label>
                            <input type="number" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" 
                                   class="form-control" step="0.01" min="0" required>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Stock *</label>
                            <input type="number" name="stock" value="<?php echo htmlspecialchars($product['stock']); ?>" 
                                   class="form-control" min="0" required>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select name="category_id" class="form-control">
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                            <?php echo $category['id'] == $product['category_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" class="form-control">
                                <option value="active" <?php echo $product['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="pending" <?php echo $product['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="inactive" <?php echo $product['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="description" rows="4" class="form-control"><?php echo htmlspecialchars($product['description']); ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Product Image</label>
                            <?php if (!empty($product['image'])): ?>
                                <div class="mb-2">
                                    <img src="../uploads/products/<?php echo htmlspecialchars($product['image']); ?>" 
                                         class="w-32 h-32 object-cover rounded-lg">
                                </div>
                            <?php endif; ?>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <p class="text-sm text-gray-500 mt-1">Leave empty to keep current image</p>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <h3 class="font-medium text-gray-800 mb-4">Product Attributes</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($attributes as $attr): ?>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    <?php echo htmlspecialchars($attr['attribute_name']); ?>
                                </label>
                                <input type="text" name="attributes[<?php echo $attr['id']; ?>]" 
                                       value="<?php echo htmlspecialchars($attr['value'] ?? ''); ?>" 
                                       class="form-control">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end gap-4">
                    <a href="view.php?id=<?php echo $product_id; ?>" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
            </div>
            </div>
        </div>

<?php require_once '../templates/footer.php'; ?>