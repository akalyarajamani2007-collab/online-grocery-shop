<?php
require_once __DIR__ . '/../includes/functions.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    redirect(site_url('admin/login.php'));
}

$categories = fetchCategories();
$errors = [];
$success = null;
$editItem = null;
$formValues = [
    'category_id' => '',
    'name' => '',
    'description' => '',
    'price' => '',
    'stock_qty' => '',
    'status' => 'available',
];

if (isset($_GET['edit_id']) && intval($_GET['edit_id']) > 0) {
    $editId = intval($_GET['edit_id']);
    $stmt = $pdo->prepare('SELECT * FROM items WHERE id = ? LIMIT 1');
    $stmt->execute([$editId]);
    $editItem = $stmt->fetch();

    if ($editItem) {
        $formValues = [
            'category_id' => intval($editItem['category_id']),
            'name' => $editItem['name'],
            'description' => $editItem['description'],
            'price' => $editItem['price'],
            'stock_qty' => intval($editItem['stock_qty']),
            'status' => $editItem['status'],
        ];
    } else {
        redirect(site_url('admin/items.php'));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formValues = [
        'category_id' => intval($_POST['category_id'] ?? 0),
        'name' => sanitize($_POST['name'] ?? ''),
        'description' => sanitize($_POST['description'] ?? ''),
        'price' => floatval($_POST['price'] ?? 0),
        'stock_qty' => intval($_POST['stock_qty'] ?? 0),
        'status' => sanitize($_POST['status'] ?? 'available'),
    ];

    if (isset($_POST['delete_id'])) {
        $deleteId = intval($_POST['delete_id']);
        $stmt = $pdo->prepare('SELECT image, id FROM items WHERE id = ? LIMIT 1');
        $stmt->execute([$deleteId]);
        $deleteItem = $stmt->fetch();

        if ($deleteItem) {
            $inOrders = $pdo->prepare('SELECT COUNT(*) FROM order_items WHERE item_id = ?');
            $inOrders->execute([$deleteId]);
            $usedInOrders = intval($inOrders->fetchColumn()) > 0;

            if ($usedInOrders) {
                $stmt = $pdo->prepare('UPDATE items SET status = ? WHERE id = ?');
                $stmt->execute(['unavailable', $deleteId]);
                $success = 'Product marked unavailable because it is already linked to orders.';
            } else {
                $stmt = $pdo->prepare('DELETE FROM items WHERE id = ?');
                $stmt->execute([$deleteId]);
                $imagePath = __DIR__ . '/../assets/images/' . $deleteItem['image'];
                if (!empty($deleteItem['image']) && $deleteItem['image'] !== 'default.svg' && file_exists($imagePath)) {
                    unlink($imagePath);
                }
                $success = 'Product deleted successfully.';
            }
        }
    } elseif (isset($_POST['update_id'])) {
        $updateId = intval($_POST['update_id']);
        $stmt = $pdo->prepare('SELECT * FROM items WHERE id = ? LIMIT 1');
        $stmt->execute([$updateId]);
        $currentItem = $stmt->fetch();

        if (!$currentItem) {
            $errors[] = 'Product not found.';
        } else {
            $imageName = $currentItem['image'];

            if (empty($formValues['name']) || $formValues['price'] <= 0 || $formValues['stock_qty'] < 0) {
                $errors[] = 'Name, price, and stock quantity are required.';
            }
            if ($formValues['category_id'] <= 0) {
                $errors[] = 'Please select a valid category.';
            }

            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['png', 'jpg', 'jpeg', 'gif', 'webp'];
                $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION) ?? '');
                if (!in_array($extension, $allowed)) {
                    $errors[] = 'Invalid image type. Use PNG, JPG, JPEG, GIF, or WEBP.';
                } else {
                    $imageName = uniqid('item_') . '.' . $extension;
                    $destination = __DIR__ . '/../assets/images/' . $imageName;
                    if (!move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                        $errors[] = 'Failed to upload image.';
                        $imageName = $currentItem['image'];
                    }
                }
            }

            if (empty($errors)) {
                $slug = generateSlug($formValues['name']);
                $stmt = $pdo->prepare('UPDATE items SET category_id = ?, name = ?, slug = ?, description = ?, image = ?, price = ?, stock_qty = ?, status = ? WHERE id = ?');
                $stmt->execute([
                    $formValues['category_id'],
                    $formValues['name'],
                    $slug,
                    $formValues['description'],
                    $imageName,
                    $formValues['price'],
                    $formValues['stock_qty'],
                    $formValues['status'],
                    $updateId,
                ]);
                $success = 'Product updated successfully.';
                $editItem = null;
                $formValues = [
                    'category_id' => '',
                    'name' => '',
                    'description' => '',
                    'price' => '',
                    'stock_qty' => '',
                    'status' => 'available',
                ];
            }
        }
    } else {
        $slug = generateSlug($formValues['name']);
        $imageName = 'default.svg';

        if (empty($formValues['name']) || $formValues['price'] <= 0 || $formValues['stock_qty'] < 0) {
            $errors[] = 'Name, price, and stock quantity are required.';
        }
        if ($formValues['category_id'] <= 0) {
            $errors[] = 'Please select a valid category.';
        }

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['png', 'jpg', 'jpeg', 'gif', 'webp'];
            $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION) ?? '');
            if (!in_array($extension, $allowed)) {
                $errors[] = 'Invalid image type. Use PNG, JPG, JPEG, GIF, or WEBP.';
            } else {
                $imageName = uniqid('item_') . '.' . $extension;
                $destination = __DIR__ . '/../assets/images/' . $imageName;
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                    $errors[] = 'Failed to upload image.';
                    $imageName = 'default.svg';
                }
            }
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare('INSERT INTO items (category_id, name, slug, description, image, price, stock_qty, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $formValues['category_id'],
                $formValues['name'],
                $slug,
                $formValues['description'],
                $imageName,
                $formValues['price'],
                $formValues['stock_qty'],
                $formValues['status'],
            ]);
            $success = 'Product added successfully.';
            $formValues = [
                'category_id' => '',
                'name' => '',
                'description' => '',
                'price' => '',
                'stock_qty' => '',
                'status' => 'available',
            ];
        }
    }
}

$items = $pdo->query('SELECT items.*, categories.name AS category_name FROM items JOIN categories ON items.category_id = categories.id ORDER BY items.created_at DESC')->fetchAll();
require_once __DIR__ . '/../templates/header.php';
?>
<div class="row gy-4">
    <div class="col-12">
        <div class="card shadow-sm border-0 p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h2 class="mb-1 text-success">Manage Products</h2>
                    <p class="text-muted mb-0">Add, edit, or remove products and review the catalog.</p>
                </div>
                <a class="btn btn-outline-success" href="<?= site_url('admin/index.php') ?>">Back to Dashboard</a>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm border-0 p-4">
            <h5 class="mb-3 text-success"><?= $editItem ? 'Edit Product' : 'New Product' ?></h5>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <form method="POST" action="" enctype="multipart/form-data">
                <?php if ($editItem): ?>
                    <input type="hidden" name="update_id" value="<?= intval($editItem['id']) ?>">
                <?php endif; ?>
                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select" required>
                        <option value="0">Choose category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= intval($category['id']) ?>" <?= intval($formValues['category_id']) === intval($category['id']) ? 'selected' : '' ?>><?= htmlspecialchars($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($formValues['name']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($formValues['description']) ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Price</label>
                    <input type="number" step="0.01" name="price" class="form-control" value="<?= htmlspecialchars($formValues['price']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Stock Quantity</label>
                    <input type="number" name="stock_qty" class="form-control" value="<?= intval($formValues['stock_qty']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        <option value="available" <?= $formValues['status'] === 'available' ? 'selected' : '' ?>>Available</option>
                        <option value="unavailable" <?= $formValues['status'] === 'unavailable' ? 'selected' : '' ?>>Unavailable</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Image</label>
                    <input type="file" name="image" accept="image/*" class="form-control">
                    <?php if ($editItem && !empty($editItem['image'])): ?>
                        <div class="mt-2">
                            <img src="<?= site_url('assets/images/' . htmlspecialchars($editItem['image'])) ?>" onerror="this.onerror=null;this.src='https://via.placeholder.com/120x120?text=Product'" alt="<?= htmlspecialchars($editItem['name']) ?>" class="rounded" style="width:120px;height:120px;object-fit:cover;">
                        </div>
                        <small class="text-muted">Upload a new image to replace the current one.</small>
                    <?php endif; ?>
                </div>
                <button type="submit" class="btn btn-success w-100 mb-2"><?= $editItem ? 'Update Product' : 'Add Product' ?></button>
                <?php if ($editItem): ?>
                    <a href="<?= site_url('admin/items.php') ?>" class="btn btn-outline-secondary w-100">Cancel Edit</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card shadow-sm border-0 p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="text-success">Product Catalog</h5>
                <span class="badge bg-secondary"><?= count($items) ?> items</span>
            </div>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?= intval($item['id']) ?></td>
                                <td>
                                    <img src="<?= site_url('assets/images/' . htmlspecialchars($item['image'])) ?>" onerror="this.onerror=null;this.src='https://via.placeholder.com/80x80?text=Product'" alt="<?= htmlspecialchars($item['name']) ?>" class="rounded" style="width:80px;height:80px;object-fit:cover;">
                                </td>
                                <td><?= htmlspecialchars($item['name']) ?></td>
                                <td><?= htmlspecialchars($item['category_name']) ?></td>
                                <td><?= formatCurrency($item['price']) ?></td>
                                <td><?= intval($item['stock_qty']) ?></td>
                                <td><?= htmlspecialchars($item['status']) ?></td>
                                <td>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <a href="<?= site_url('admin/items.php?edit_id=' . intval($item['id'])) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                        <form method="POST" action="" onsubmit="return confirm('Delete this product?');">
                                            <input type="hidden" name="delete_id" value="<?= intval($item['id']) ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>
