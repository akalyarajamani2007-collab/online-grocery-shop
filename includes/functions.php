<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/app.php';

function sanitize($value) {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function isLoggedIn() {
    session_start();
    return isset($_SESSION['user_id']);
}

function isAdminLoggedIn() {
    session_start();
    return isset($_SESSION['admin_id']);
}

function getUserByEmail($email) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    return $stmt->fetch();
}

function getAdminByEmail($email) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM admins WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    return $stmt->fetch();
}

function fetchCategories() {
    global $pdo;
    $stmt = $pdo->query('SELECT * FROM categories WHERE status = "active" ORDER BY name');
    return $stmt->fetchAll();
}

function fetchItems($filters = []) {
    global $pdo;
    $sql = 'SELECT items.*, categories.name AS category_name FROM items JOIN categories ON items.category_id = categories.id WHERE items.status = "available"';
    $params = [];

    if (!empty($filters['category_id'])) {
        $sql .= ' AND items.category_id = ?';
        $params[] = intval($filters['category_id']);
    }
    if (!empty($filters['search'])) {
        $sql .= ' AND (items.name LIKE ? OR items.description LIKE ?)';
        $params[] = '%' . $filters['search'] . '%';
        $params[] = '%' . $filters['search'] . '%';
    }
    if ($filters['min_price'] !== null) {
        $sql .= ' AND items.price >= ?';
        $params[] = floatval($filters['min_price']);
    }
    if ($filters['max_price'] !== null) {
        $sql .= ' AND items.price <= ?';
        $params[] = floatval($filters['max_price']);
    }

    $sql .= ' ORDER BY items.created_at DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function cartItemCount($userId) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT SUM(quantity) AS total FROM cart WHERE user_id = ?');
    $stmt->execute([$userId]);
    $row = $stmt->fetch();
    return $row['total'] ? intval($row['total']) : 0;
}

function formatCurrency($value) {
    return '₹' . number_format($value, 2);
}

function generateSlug($text) {
    $text = preg_replace('~[^\r\n\t\f\na-zA-Z0-9_]+~u', '-', $text);
    $text = trim($text, '-');
    $text = strtolower($text);
    return $text ?: 'item-' . time();
}

function findOrCreateCategory($name, $description = '') {
    global $pdo;
    $stmt = $pdo->prepare('SELECT id FROM categories WHERE name = ? LIMIT 1');
    $stmt->execute([$name]);
    $category = $stmt->fetch();
    if ($category) {
        return intval($category['id']);
    }
    $stmt = $pdo->prepare('INSERT INTO categories (name, description, status) VALUES (?, ?, "active")');
    $stmt->execute([$name, $description]);
    return intval($pdo->lastInsertId());
}

function seedDemoData() {
    global $pdo;
    $itemCount = intval($pdo->query('SELECT COUNT(*) FROM items')->fetchColumn());
    if ($itemCount > 0) {
        return;
    }

    $oilsId = findOrCreateCategory('Oils', 'Cooking oils and edible oils.');
    $fruitsId = findOrCreateCategory('Fruits', 'Fresh fruits and berries.');
    $vegId = findOrCreateCategory('Vegetables', 'Daily vegetables and greens.');

    $products = [
        [$oilsId, 'Cooking Oil', 'Premium edible cooking oil for everyday use.', 179.99, 25],
        [$fruitsId, 'Fresh Apples', 'Crisp and juicy apples sourced locally.', 119.50, 40],
        [$vegId, 'Organic Spinach', 'Healthy organic spinach full of vitamins.', 49.00, 60],
        [$vegId, 'Red Onion', 'Fresh red onions for your everyday cooking.', 39.50, 55],
        [$fruitsId, 'Banana Bunch', 'Sweet bananas perfect for breakfast and snacks.', 59.99, 50],
    ];

    $stmt = $pdo->prepare('INSERT IGNORE INTO items (category_id, name, slug, description, image, price, stock_qty, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    foreach ($products as $product) {
        [$categoryId, $name, $description, $price, $qty] = $product;
        $stmt->execute([$categoryId, $name, generateSlug($name), $description, 'default.svg', $price, $qty, 'available']);
    }
}

function getCartItems($userId) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT c.id AS cart_id, c.item_id, c.quantity, i.name, i.description, i.image, i.price, i.stock_qty, categories.name AS category_name FROM cart c JOIN items i ON i.id = c.item_id JOIN categories ON categories.id = i.category_id WHERE c.user_id = ? ORDER BY c.created_at ASC');
    $stmt->execute([intval($userId)]);
    return $stmt->fetchAll();
}

function getCartSummary($userId) {
    $items = getCartItems($userId);
    $itemCount = 0;
    $subtotal = 0.0;

    foreach ($items as $item) {
        $itemCount += intval($item['quantity']);
        $subtotal += floatval($item['price']) * intval($item['quantity']);
    }

    return [
        'items' => $items,
        'item_count' => $itemCount,
        'subtotal' => round($subtotal, 2),
    ];
}

function addItemToCart($userId, $itemId, $quantity = 1) {
    global $pdo;

    $userId = intval($userId);
    $itemId = intval($itemId);
    $quantity = max(1, intval($quantity));

    $stmt = $pdo->prepare('SELECT id, stock_qty, status FROM items WHERE id = ? LIMIT 1');
    $stmt->execute([$itemId]);
    $item = $stmt->fetch();

    if (!$item || $item['status'] !== 'available') {
        return ['success' => false, 'message' => 'This item is currently unavailable.'];
    }

    if (intval($item['stock_qty']) < $quantity) {
        return ['success' => false, 'message' => 'Requested quantity exceeds available stock.'];
    }

    $stmt = $pdo->prepare('INSERT INTO cart (user_id, item_id, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity), created_at = CURRENT_TIMESTAMP');
    $stmt->execute([$userId, $itemId, $quantity]);

    return ['success' => true, 'message' => 'Item added to cart.'];
}

function updateCartItemQuantity($userId, $cartId, $quantity) {
    global $pdo;

    $userId = intval($userId);
    $cartId = intval($cartId);
    $quantity = max(0, intval($quantity));

    if ($quantity === 0) {
        $stmt = $pdo->prepare('DELETE FROM cart WHERE id = ? AND user_id = ?');
        return $stmt->execute([$cartId, $userId]);
    }

    $stmt = $pdo->prepare('SELECT c.item_id, i.stock_qty FROM cart c JOIN items i ON i.id = c.item_id WHERE c.id = ? AND c.user_id = ? LIMIT 1');
    $stmt->execute([$cartId, $userId]);
    $cart = $stmt->fetch();

    if (!$cart) {
        return false;
    }

    if (intval($cart['stock_qty']) < $quantity) {
        return false;
    }

    $stmt = $pdo->prepare('UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?');
    return $stmt->execute([$quantity, $cartId, $userId]);
}

function removeCartItem($userId, $cartId) {
    global $pdo;
    $stmt = $pdo->prepare('DELETE FROM cart WHERE id = ? AND user_id = ?');
    return $stmt->execute([intval($cartId), intval($userId)]);
}

function clearCart($userId) {
    global $pdo;
    $stmt = $pdo->prepare('DELETE FROM cart WHERE user_id = ?');
    return $stmt->execute([intval($userId)]);
}

function getOrderItems($orderId) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT oi.id, oi.order_id, oi.quantity, oi.price, oi.total_price, i.name, i.image, c.name AS category_name FROM order_items oi JOIN items i ON i.id = oi.item_id LEFT JOIN categories c ON c.id = i.category_id WHERE oi.order_id = ? ORDER BY oi.id ASC');
    $stmt->execute([intval($orderId)]);
    return $stmt->fetchAll();
}
?>
