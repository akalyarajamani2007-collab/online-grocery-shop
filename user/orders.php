<?php
require_once __DIR__ . '/../includes/functions.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    redirect(site_url('user/login.php'));
}

$userId = intval($_SESSION['user_id']);
$stmt = $pdo->prepare('SELECT o.*, p.status AS payment_status, p.transaction_id, p.payment_date FROM orders o LEFT JOIN payments p ON p.order_id = o.id WHERE o.user_id = ? ORDER BY o.created_at DESC');
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();
require_once __DIR__ . '/../templates/header.php';
?>
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0 p-4">
            <h4 class="text-success mb-4">My Orders</h4>

            <?php if (!empty($_SESSION['order_success_message'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['order_success_message']) ?></div>
                <?php unset($_SESSION['order_success_message']); ?>
            <?php endif; ?>

            <?php if (empty($orders)): ?>
                <div class="alert alert-info">You have not placed any orders yet.</div>
            <?php else: ?>
                <div class="d-grid gap-4">
                    <?php foreach ($orders as $order): ?>
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
                                    <div>
                                        <h5 class="mb-1">Order #<?= htmlspecialchars($order['id']) ?></h5>
                                        <div class="text-muted small">Placed on <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></div>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold text-success"><?= formatCurrency($order['total_amount']) ?></div>
                                        <div class="small text-muted">Payment: <?= htmlspecialchars($order['payment_method']) ?></div>
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-4"><strong>Payment Status:</strong> <?= htmlspecialchars($order['payment_status'] ?? $order['payment_status'] ?? 'pending') ?></div>
                                    <div class="col-md-4"><strong>Delivery Status:</strong> <?= htmlspecialchars($order['delivery_status']) ?></div>
                                    <div class="col-md-4"><strong>Order Status:</strong> <?= htmlspecialchars($order['status']) ?></div>
                                </div>

                                <div class="mb-3">
                                    <strong>Delivery Address:</strong>
                                    <div class="text-muted"><?= nl2br(htmlspecialchars($order['address'])) ?></div>
                                </div>

                                <?php $orderItems = getOrderItems($order['id']); ?>
                                <?php if (!empty($orderItems)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Item</th>
                                                    <th>Category</th>
                                                    <th>Qty</th>
                                                    <th>Price</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($orderItems as $item): ?>
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center gap-3">
                                                                <img src="<?= site_url('assets/images/' . htmlspecialchars($item['image'])) ?>" onerror="this.onerror=null;this.src='https://via.placeholder.com/60x60?text=Item'" alt="<?= htmlspecialchars($item['name']) ?>" style="width:60px;height:60px;object-fit:cover;border-radius:8px;">
                                                                <div>
                                                                    <div class="fw-semibold"><?= htmlspecialchars($item['name']) ?></div>
                                                                    <div class="small text-muted">Bill image preview</div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td><?= htmlspecialchars($item['category_name'] ?? 'Grocery') ?></td>
                                                        <td><?= intval($item['quantity']) ?></td>
                                                        <td><?= formatCurrency($item['price']) ?></td>
                                                        <td><?= formatCurrency($item['total_price']) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-warning py-2 mb-0">No line items are available for this order.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>
