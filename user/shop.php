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

$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
$min_price = (isset($_GET['min_price']) && $_GET['min_price'] !== '') ? floatval($_GET['min_price']) : null;
$max_price = (isset($_GET['max_price']) && $_GET['max_price'] !== '') ? floatval($_GET['max_price']) : null;
$filters = [
    'search' => $search,
    'category_id' => $category_id,
    'min_price' => $min_price,
    'max_price' => $max_price,
];
seedDemoData();
$items = fetchItems($filters);
$cartSummary = getCartSummary($userId);
require_once __DIR__ . '/../templates/header.php';
?>
<div class="row">
    <div class="col-lg-3 mb-4">
        <div class="card shadow-sm border-0 p-3">
            <h5 class="text-success">Filter Products</h5>
            <form method="GET" action="<?= site_url('user/shop.php') ?>">
                <div class="mb-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="Search items">
                </div>
                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select">
                        <option value="0">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>" <?= $category_id === intval($category['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col">
                        <label class="form-label">Min Price</label>
                        <input type="number" step="0.01" name="min_price" value="<?= $min_price !== null ? htmlspecialchars($min_price) : '' ?>" class="form-control" placeholder="Min">
                    </div>
                    <div class="col">
                        <label class="form-label">Max Price</label>
                        <input type="number" step="0.01" name="max_price" value="<?= $max_price !== null ? htmlspecialchars($max_price) : '' ?>" class="form-control" placeholder="Max">
                    </div>
                </div>
                <button type="submit" class="btn btn-success w-100">Apply</button>
            </form>
        </div>
    </div>
    <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="mb-0 text-success">Products</h5>
                <small class="text-muted"><?= count($items) ?> item<?= count($items) === 1 ? '' : 's' ?> found</small>
            </div>
            <a href="<?= site_url('user/cart.php') ?>" class="btn btn-outline-success btn-sm">View Cart (<?= $cartSummary['item_count'] ?>)</a>
        </div>

        <?php if ($cartMessage): ?>
            <div class="alert alert-<?= htmlspecialchars($cartMessageType) ?>"><?= htmlspecialchars($cartMessage) ?></div>
        <?php endif; ?>

        <div class="card shadow-sm border-0 p-3 mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <strong><?= $cartSummary['item_count'] ?> item<?= $cartSummary['item_count'] === 1 ? '' : 's' ?> in cart</strong>
                    <div class="text-muted small">Subtotal: <?= formatCurrency($cartSummary['subtotal']) ?></div>
                </div>
                <a href="<?= site_url('user/checkout.php') ?>" class="btn btn-success btn-sm">Proceed to Checkout</a>
            </div>
        </div>

        <div class="row gy-4">
            <?php if (empty($items)): ?>
                <div class="col-12">
                    <div class="alert alert-warning">No items found. Try a different filter.</div>
                </div>
            <?php endif; ?>
            <?php foreach ($items as $item): ?>
                <div class="col-md-6 col-xl-4">
                    <div class="card product-card shadow-sm border-0 h-100">
                        <img src="<?= site_url('assets/images/' . htmlspecialchars($item['image'])) ?>" onerror="this.onerror=null;this.src='https://via.placeholder.com/400x300?text=Product'" class="card-img-top" alt="<?= htmlspecialchars($item['name']) ?>">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= htmlspecialchars($item['name']) ?></h5>
                            <p class="mb-2 text-muted small">Category: <?= htmlspecialchars($item['category_name']) ?></p>
                            <p class="mb-3 text-secondary"><?= htmlspecialchars(substr($item['description'], 0, 80)) ?>...</p>
                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="h5 text-success mb-0"><?= formatCurrency($item['price']) ?></span>
                                    <span class="badge bg-<?= $item['stock_qty'] > 0 ? 'success' : 'danger' ?> badge-status">
                                        <?= $item['stock_qty'] > 0 ? 'In Stock' : 'Out of Stock' ?>
                                    </span>
                                </div>
                                <form method="POST" action="<?= site_url('user/shop.php') ?>" class="mb-3">
                                    <input type="hidden" name="item_id" value="<?= intval($item['id']) ?>">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <select name="quantity" class="form-select form-select-sm" <?= $item['stock_qty'] <= 0 ? 'disabled' : '' ?>>
                                                <?php for ($qty = 1; $qty <= min(5, max(1, intval($item['stock_qty']))); $qty++): ?>
                                                    <option value="<?= $qty ?>"><?= $qty ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                        <div class="col-6">
                                            <button type="submit" name="add_to_cart" class="btn btn-success btn-sm w-100" <?= $item['stock_qty'] <= 0 ? 'disabled' : '' ?>>Add to Cart</button>
                                        </div>
                                    </div>
                                </form>
                                <a href="<?= site_url('user/product.php?id=' . intval($item['id'])) ?>" class="btn btn-sm btn-outline-success w-100">View Detail</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>
