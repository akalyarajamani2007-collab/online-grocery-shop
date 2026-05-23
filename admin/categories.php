<?php
require_once __DIR__ . '/../includes/functions.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    redirect(site_url('admin/login.php'));
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    if (empty($name)) {
        $errors[] = 'Category name is required.';
    }
    if (empty($errors)) {
        $stmt = $pdo->prepare('INSERT INTO categories (name, description) VALUES (?, ?)');
        $stmt->execute([$name, $description]);
        redirect(site_url('admin/categories.php?added=1'));
    }
}
$categoryList = $pdo->query('SELECT * FROM categories ORDER BY created_at DESC')->fetchAll();
require_once __DIR__ . '/../templates/header.php';
?>
<div class="row gy-4">
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 p-4">
            <h5 class="mb-3 text-success">Add Category</h5>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-success w-100">Save Category</button>
            </form>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-success">Category List</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categoryList as $category): ?>
                        <tr>
                            <td><?= htmlspecialchars($category['id']) ?></td>
                            <td><?= htmlspecialchars($category['name']) ?></td>
                            <td><?= htmlspecialchars($category['description']) ?></td>
                            <td><?= htmlspecialchars($category['status']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>
