<?php
require_once __DIR__ . '/../includes/functions.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    redirect(site_url('admin/login.php'));
}
$orders = $pdo->query('SELECT orders.*, users.name AS customer FROM orders JOIN users ON orders.user_id = users.id ORDER BY orders.created_at DESC')->fetchAll();
require_once __DIR__ . '/../templates/header.php';
?>
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0 p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="text-success">Order Management</h4>
                <a href="<?= site_url('admin/index.php') ?>" class="btn btn-sm btn-outline-success">Dashboard</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Delivery</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= htmlspecialchars($order['id']) ?></td>
                            <td><?= htmlspecialchars($order['customer']) ?></td>
                            <td>₹<?= number_format($order['total_amount'], 2) ?></td>
                            <td><?= htmlspecialchars($order['payment_status']) ?></td>
                            <td><?= htmlspecialchars($order['delivery_status']) ?></td>
                            <td><?= date('d M Y', strtotime($order['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>
