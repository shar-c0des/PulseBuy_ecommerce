<?php
require_once '../config/database.php';
require_once '../templates/header.php';

// Check if product ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: list.php');
    exit();
}

$product_id = (int)$_GET['id'];

// Get product details
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name 
                       FROM products p 
                       LEFT JOIN categories c ON p.category_id = c.id 
                       WHERE p.id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('Location: list.php');
    exit();
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-6">
            <?php if (!empty($product['image'])): ?>
                <img src="../uploads/products/<?php echo htmlspecialchars($product['image']); ?>" 
                     class="img-fluid rounded" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>">
            <?php else: ?>
                <img src="../assets/images/no-image.png" 
                     class="img-fluid rounded" 
                     alt="No image available">
            <?php endif; ?>
        </div>
        <div class="col-md-6">
            <h1 class="mb-3"><?php echo htmlspecialchars($product['name']); ?></h1>
            
            <div class="mb-3">
                <h4 class="text-primary">$<?php echo number_format($product['price'], 2); ?></h4>
            </div>

            <div class="mb-3">
                <p class="text-muted">Category: <?php echo htmlspecialchars($product['category_name']); ?></p>
            </div>

            <div class="mb-3">
                <h5>Description</h5>
                <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            </div>

            <div class="mb-3">
                <p><strong>Stock:</strong> <?php echo $product['stock']; ?> units</p>
            </div>

            <?php if ($product['stock'] > 0): ?>
                <form action="../cart/add.php" method="POST" class="mb-3">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <div class="input-group" style="max-width: 200px;">
                        <input type="number" name="quantity" class="form-control" value="1" min="1" max="<?php echo $product['stock']; ?>">
                        <button type="submit" class="btn btn-primary">Add to Cart</button>
                    </div>
                </form>
            <?php else: ?>
                <div class="alert alert-warning">Out of Stock</div>
            <?php endif; ?>

            <div class="mt-4">
                <a href="list.php" class="btn btn-secondary">Back to Products</a>
            </div>
        </div>
    </div>
</div>

<?php require_once '../templates/footer.php'; ?>
    