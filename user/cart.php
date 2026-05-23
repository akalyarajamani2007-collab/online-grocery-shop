<?php
require_once __DIR__ . '/../includes/functions.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    redirect(site_url('user/login.php'));
}

$userId = intval($_SESSION['user_id']);
$statusMessage = null;
$statusType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_qty'])) {
        $updated = updateCartItemQuantity($userId, intval($_POST['cart_id']), intval($_POST['quantity']));
        $statusMessage = $updated ? 'Cart updated successfully.' : 'Unable to update quantity. Please check available stock.';
        $statusType = $updated ? 'success' : 'danger';
    }

    if (isset($_POST['remove_item'])) {
        $removed = removeCartItem($userId, intval($_POST['cart_id']));
        $statusMessage = $removed ? 'Item removed from cart.' : 'Unable to remove item.';
        $statusType = $removed ? 'success' : 'danger';
    }

    if (isset($_POST['clear_cart'])) {
        $cleared = clearCart($userId);
        $statusMessage = $cleared ? 'Your cart has been cleared.' : 'Unable to clear cart.';
        $statusType = $cleared ? 'success' : 'danger';
    }
}

$cartSummary = getCartSummary($userId);
require_once __DIR__ . '/../templates/header.php';
?>
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0 p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <div>
                    <h4 class="text-success mb-1">Your Cart</h4>
                    <p class="text-muted mb-0"><?= $cartSummary['item_count'] ?> item<?= $cartSummary['item_count'] === 1 ? '' : 's' ?> selected for checkout.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= site_url('user/shop.php') ?>" class="btn btn-outline-success btn-sm">Continue Shopping</a>
                    <?php if (!empty($cartSummary['items'])): ?>
                        <a href="<?= site_url('user/checkout.php') ?>" class="btn btn-success btn-sm">Proceed to Checkout</a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($statusMessage): ?>
                <div class="alert alert-<?= htmlspecialchars($statusType) ?>"><?= htmlspecialchars($statusMessage) ?></div>
            <?php endif; ?>

            <?php if (empty($cartSummary['items'])): ?>
                <div class="alert alert-info mb-0">Your cart is empty. Add products from the shop to start your order.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cartSummary['items'] as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="<?= site_url('assets/images/' . htmlspecialchars($item['image'])) ?>" onerror="this.onerror=null;this.src='https://via.placeholder.com/80x80?text=Item'" alt="<?= htmlspecialchars($item['name']) ?>" style="width:80px;height:80px;object-fit:cover;border-radius:8px;">
                                            <div>
                                                <div class="fw-semibold"><?= htmlspecialchars($item['name']) ?></div>
                                                <div class="small text-muted"><?= htmlspecialchars(substr($item['description'], 0, 90)) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($item['category_name']) ?></td>
                                    <td><?= formatCurrency($item['price']) ?></td>
                                    <td>
                                        <form method="POST" action="" class="d-flex gap-2 align-items-center">
                                            <input type="hidden" name="cart_id" value="<?= intval($item['cart_id']) ?>">
                                            <select name="quantity" class="form-select form-select-sm" style="width:90px;">
                                                <?php for ($qty = 1; $qty <= 10; $qty++): ?>
                                                    <option value="<?= $qty ?>" <?= intval($item['quantity']) === $qty ? 'selected' : '' ?>><?= $qty ?></option>
                                                <?php endfor; ?>
                                            </select>
                                            <button type="submit" name="update_qty" class="btn btn-outline-success btn-sm">Update</button>
                                        </form>
                                    </td>
                                    <td><?= formatCurrency(floatval($item['price']) * intval($item['quantity'])) ?></td>
                                    <td>
                                        <form method="POST" action="">
                                            <input type="hidden" name="cart_id" value="<?= intval($item['cart_id']) ?>">
                                            <button type="submit" name="remove_item" class="btn btn-outline-danger btn-sm">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="row mt-4">
                    <div class="col-lg-6">
                        <div class="card bg-light border-0 p-3 h-100">
                            <h5 class="text-success mb-3">Order Summary</h5>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal</span>
                                <span><?= formatCurrency($cartSummary['subtotal']) ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Delivery</span>
                                <span>Free</span>
                            </div>
                            <div class="d-flex justify-content-between fw-bold text-success border-top pt-3">
                                <span>Grand Total</span>
                                <span><?= formatCurrency($cartSummary['subtotal']) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 mt-3 mt-lg-0">
                        <div class="card bg-light border-0 p-3 h-100">
                            <h5 class="text-success mb-3">Checkout</h5>
                            <p class="text-muted mb-3">Review the items, choose a payment mode, and confirm the order in the final checkout step.</p>
                            <a href="<?= site_url('user/checkout.php') ?>" class="btn btn-success w-100 mb-2">Confirm Order</a>
                            <form method="POST" action="">
                                <button type="submit" name="clear_cart" class="btn btn-outline-danger w-100">Clear Cart</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>