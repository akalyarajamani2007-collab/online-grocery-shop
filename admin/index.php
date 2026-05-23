<?php
require_once __DIR__ . '/../includes/functions.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    redirect(site_url('admin/login.php'));
}

$totalUsers = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$totalItems = $pdo->query('SELECT COUNT(*) FROM items')->fetchColumn();
$totalOrders = $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$revenue = $pdo->query('SELECT COALESCE(SUM(amount),0) FROM payments WHERE status = "completed"')->fetchColumn();
$pendingDeliveries = $pdo->query('SELECT COUNT(*) FROM delivery WHERE status != "delivered"')->fetchColumn();
$recentOrders = $pdo->query('SELECT orders.id, users.name AS customer_name, orders.total_amount, orders.status, orders.created_at FROM orders JOIN users ON orders.user_id = users.id ORDER BY orders.created_at DESC LIMIT 5')->fetchAll();
require_once __DIR__ . '/../templates/header.php';
?>
<div class="row gy-4">
    <div class="col-12">
        <div class="card shadow-sm border-0 p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1 text-success">Admin Dashboard</h2>
                    <p class="text-muted">Welcome back, <?= htmlspecialchars($_SESSION['admin_name']) ?>.</p>
                </div>
                <a class="btn btn-outline-success" href="<?= site_url('admin/logout.php') ?>">Logout</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 p-3">
            <h6 class="text-secondary">Total Users</h6>
            <h3 class="text-success"><?= intval($totalUsers) ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 p-3">
            <h6 class="text-secondary">Total Items</h6>
            <h3 class="text-success"><?= intval($totalItems) ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 p-3">
            <h6 class="text-secondary">Total Orders</h6>
            <h3 class="text-success"><?= intval($totalOrders) ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 p-3">
            <h6 class="text-secondary">Revenue</h6>
            <h3 class="text-success">₹<?= number_format($revenue, 2) ?></h3>
        </div>
    </div>
    <div class="col-12">
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm p-3">
                    <h6 class="text-secondary">Product Management</h6>
                    <p class="mb-3">Add, edit, or view grocery items.</p>
                    <a href="<?= site_url('admin/items.php') ?>" class="btn btn-sm btn-success">Manage Products</a>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm p-3">
                    <h6 class="text-secondary">Category Management</h6>
                    <p class="mb-3">Create and manage grocery categories.</p>
                    <a href="<?= site_url('admin/categories.php') ?>" class="btn btn-sm btn-success">Manage Categories</a>
                </div>
            </div>
        </div>
        <div class="card shadow-sm border-0 p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5>Recent Orders</h5>
                <a href="<?= site_url('admin/orders.php') ?>" class="btn btn-sm btn-success">View All Orders</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Placed</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td><?= htmlspecialchars($order['id']) ?></td>
                            <td><?= htmlspecialchars($order['customer_name']) ?></td>
                            <td>₹<?= number_format($order['total_amount'],2) ?></td>
                            <td><?= htmlspecialchars($order['status']) ?></td>
                            <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>
