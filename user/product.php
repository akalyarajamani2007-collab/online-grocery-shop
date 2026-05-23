<?php
require_once __DIR__ . '/../includes/functions.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    redirect(site_url('user/login.php'));
}

$userId = intval($_SESSION['user_id']);
$cartMessage = null;
$cartMessageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $result = addItemToCart($userId, intval($_POST['item_id']), intval($_POST['quantity']));
    $cartMessage = $result['message'];
    $cartMessageType = $result['success'] ? 'success' : 'danger';
}

$itemId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($itemId <= 0) {
    redirect(site_url('user/shop.php'));
}

$stmt = $pdo->prepare('SELECT items.*, categories.name AS category_name FROM items JOIN categories ON items.category_id = categories.id WHERE items.id = ? LIMIT 1');
$stmt->execute([$itemId]);
$item = $stmt->fetch();
if (!$item) {
    redirect(site_url('user/shop.php'));
}

$cartSummary = getCartSummary($userId);
require_once __DIR__ . '/../templates/header.php';
?>
<div class="row gy-4">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0">
            <img src="<?= site_url('assets/images/' . htmlspecialchars($item['image'])) ?>" onerror="this.onerror=null;this.src='https://via.placeholder.com/600x400?text=Product'" class="card-img-top" alt="<?= htmlspecialchars($item['name']) ?>">
            <div class="card-body">
                <h3 class="card-title text-success"><?= htmlspecialchars($item['name']) ?></h3>
                <p class="text-muted mb-3">Category: <?= htmlspecialchars($item['category_name']) ?></p>
                <p class="lead text-dark"><?= nl2br(htmlspecialchars($item['description'])) ?></p>
                <div class="d-flex align-items-center justify-content-between mt-4">
                    <div>
                        <span class="h4 text-success mb-0"><?= formatCurrency($item['price']) ?></span>
                        <span class="badge bg-<?= $item['stock_qty'] > 0 ? 'success' : 'danger' ?> ms-2">
                            <?= $item['stock_qty'] > 0 ? 'In Stock' : 'Out of Stock' ?>
                        </span>
                    </div>
                    <span class="text-secondary">Available: <?= intval($item['stock_qty']) ?></span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 p-4 h-100">
            <h5 class="text-success mb-3">Product Details</h5>
            <?php if ($cartMessage): ?>
                <div class="alert alert-<?= htmlspecialchars($cartMessageType) ?>"><?= htmlspecialchars($cartMessage) ?></div>
            <?php endif; ?>

            <ul class="list-group list-group-flush mb-4">
                <li class="list-group-item"><strong>Product ID:</strong> <?= htmlspecialchars($item['id']) ?></li>
                <li class="list-group-item"><strong>Category:</strong> <?= htmlspecialchars($item['category_name']) ?></li>
                <li class="list-group-item"><strong>Price:</strong> <?= formatCurrency($item['price']) ?></li>
                <li class="list-group-item"><strong>Stock:</strong> <?= intval($item['stock_qty']) ?></li>
                <li class="list-group-item"><strong>Status:</strong> <?= htmlspecialchars($item['status']) ?></li>
                <li class="list-group-item"><strong>Added on:</strong> <?= date('d M Y', strtotime($item['created_at'])) ?></li>
            </ul>

            <div class="card bg-light p-3 mb-4">
                <div class="small text-muted">Cart subtotal</div>
                <div class="h5 mb-0 text-success"><?= formatCurrency($cartSummary['subtotal']) ?></div>
                <div class="small text-muted"><?= $cartSummary['item_count'] ?> item<?= $cartSummary['item_count'] === 1 ? '' : 's' ?> in cart</div>
            </div>

            <form method="POST" action="">
                <input type="hidden" name="item_id" value="<?= intval($item['id']) ?>">
                <label class="form-label">Quantity</label>
                <select name="quantity" class="form-select mb-3" <?= $item['stock_qty'] <= 0 ? 'disabled' : '' ?>>
                    <?php for ($qty = 1; $qty <= min(5, max(1, intval($item['stock_qty']))); $qty++): ?>
                        <option value="<?= $qty ?>"><?= $qty ?></option>
                    <?php endfor; ?>
                </select>
                <button type="submit" name="add_to_cart" class="btn btn-success w-100 mb-2" <?= $item['stock_qty'] <= 0 ? 'disabled' : '' ?>>Add to Cart</button>
                <a href="<?= site_url('user/cart.php') ?>" class="btn btn-outline-success w-100 mb-2">View Cart</a>
                <a class="btn btn-outline-secondary w-100" href="<?= site_url('user/shop.php') ?>">Back to Shop</a>
            </form>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>
