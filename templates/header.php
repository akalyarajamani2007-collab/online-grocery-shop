<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/functions.php';
$categories = fetchCategories();
$currentUser = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Grocery Shop</title>
    <link href="<?= site_url('assets/css/style.css') ?>" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-white bg-white shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand text-success fw-bold" href="<?= site_url('index.php') ?>">Smart Grocery</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="<?= site_url('index.php') ?>">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= site_url('user/shop.php') ?>">Shop</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= site_url('user/orders.php') ?>">My Orders</a></li>
                <?php if ($currentUser): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= site_url('user/cart.php') ?>">Cart <span class="badge bg-success rounded-pill"><?= cartItemCount($_SESSION['user_id']) ?></span></a></li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav mb-2 mb-lg-0">
                <?php if ($currentUser): ?>
                    <li class="nav-item"><span class="nav-link">Hello, <?= htmlspecialchars($currentUser) ?></span></li>
                    <li class="nav-item"><a class="btn btn-outline-success btn-sm" href="<?= site_url('user/logout.php') ?>">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="btn btn-success btn-sm me-2" href="<?= site_url('user/login.php') ?>">Login</a></li>
                    <li class="nav-item"><a class="btn btn-outline-success btn-sm" href="<?= site_url('user/register.php') ?>">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<div class="container">
