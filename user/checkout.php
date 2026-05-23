<?php
require_once __DIR__ . '/../includes/functions.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    redirect(site_url('user/login.php'));
}

$userId = intval($_SESSION['user_id']);
$cartSummary = getCartSummary($userId);

if (empty($cartSummary['items'])) {
    redirect(site_url('user/cart.php'));
}

$stmt = $pdo->prepare('SELECT id, name, email, mobile, address FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    redirect(site_url('user/login.php'));
}

$orderError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $paymentMethod = in_array($_POST['payment_method'] ?? '', ['cod', 'online']) ? $_POST['payment_method'] : 'cod';

    try {
        $pdo->beginTransaction();

        foreach ($cartSummary['items'] as $item) {
            if (intval($item['stock_qty']) < intval($item['quantity'])) {
                throw new Exception('One or more items are no longer available in the requested quantity.');
            }
        }

        $stmt = $pdo->prepare('INSERT INTO orders (user_id, total_amount, payment_method, payment_status, delivery_status, address, status) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $userId,
            $cartSummary['subtotal'],
            $paymentMethod,
            'pending',
            'pending',
            $user['address'],
            'pending',
        ]);

        $orderId = intval($pdo->lastInsertId());

        foreach ($cartSummary['items'] as $item) {
            $stmt = $pdo->prepare('INSERT INTO order_items (order_id, item_id, quantity, price, total_price) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([
                $orderId,
                intval($item['item_id']),
                intval($item['quantity']),
                floatval($item['price']),
                floatval($item['price']) * intval($item['quantity']),
            ]);

            $stmt = $pdo->prepare('UPDATE items SET stock_qty = stock_qty - ? WHERE id = ?');
            $stmt->execute([intval($item['quantity']), intval($item['item_id'])]);
        }

        $transactionId = 'PAY-' . str_pad($orderId, 6, '0', STR_PAD_LEFT);
        $stmt = $pdo->prepare('INSERT INTO payments (order_id, amount, transaction_id, payment_method, status, payment_date) VALUES (?, ?, ?, ?, ?, NOW())');
        $stmt->execute([
            $orderId,
            $cartSummary['subtotal'],
            $transactionId,
            $paymentMethod,
            'pending',
        ]);

        $stmt = $pdo->prepare('INSERT INTO delivery (order_id, status) VALUES (?, "assigned")');
        $stmt->execute([$orderId]);

        clearCart($userId);
        $pdo->commit();

        $_SESSION['order_success_message'] = 'Order confirmed successfully. Your bill and order status are now available in My Orders.';
        redirect(site_url('user/orders.php'));
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $orderError = $e->getMessage();
    }
}

require_once __DIR__ . '/../templates/header.php';
?>
<div class="row gy-4">
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 p-4">
            <h4 class="text-success mb-3">Confirm Your Order</h4>
            <p class="text-muted">Review the bill details, choose a payment option, and confirm to place the order.</p>

            <?php if ($orderError): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($orderError) ?></div>
            <?php endif; ?>

            <div class="mb-4">
                <h5 class="mb-2">Customer Details</h5>
                <p class="mb-1"><strong>Name:</strong> <?= htmlspecialchars($user['name']) ?></p>
                <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                <p class="mb-1"><strong>Mobile:</strong> <?= htmlspecialchars($user['mobile']) ?></p>
                <p class="mb-0"><strong>Shipping Address:</strong> <?= nl2br(htmlspecialchars($user['address'])) ?></p>
            </div>

            <h5 class="text-success mb-3">Bill Details</h5>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartSummary['items'] as $item): ?>
                            <tr>
                                <td>
                                    <img src="<?= site_url('assets/images/' . htmlspecialchars($item['image'])) ?>" onerror="this.onerror=null;this.src='https://via.placeholder.com/80x80?text=Item'" alt="<?= htmlspecialchars($item['name']) ?>" style="width:80px;height:80px;object-fit:cover;border-radius:8px;">
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($item['name']) ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($item['category_name']) ?></div>
                                </td>
                                <td><?= intval($item['quantity']) ?></td>
                                <td><?= formatCurrency($item['price']) ?></td>
                                <td><?= formatCurrency(floatval($item['price']) * intval($item['quantity'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card shadow-sm border-0 p-4 h-100">
            <h5 class="text-success mb-3">Payment</h5>
            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label">Choose payment mode</label>
                    <select name="payment_method" class="form-select">
                        <option value="cod">Cash on Delivery</option>
                        <option value="online">Online Payment (demo)</option>
                    </select>
                </div>

                <div class="card bg-light border-0 p-3 mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal</span>
                        <span><?= formatCurrency($cartSummary['subtotal']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Delivery</span>
                        <span>Free</span>
                    </div>
                    <div class="d-flex justify-content-between fw-bold text-success border-top pt-3">
                        <span>Total to Pay</span>
                        <span><?= formatCurrency($cartSummary['subtotal']) ?></span>
                    </div>
                </div>

                <button type="submit" name="place_order" class="btn btn-success w-100 mb-2">Confirm Order</button>
                <a href="<?= site_url('user/cart.php') ?>" class="btn btn-outline-secondary w-100">Back to Cart</a>
            </form>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>