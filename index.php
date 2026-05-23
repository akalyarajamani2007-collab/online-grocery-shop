<?php
require_once __DIR__ . '/templates/header.php';
?>
<div class="row gy-4">
    <div class="col-12">
        <div class="hero-card p-4 rounded-4 shadow-sm bg-white">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <h1 class="display-6 text-success">Fresh groceries delivered to your door.</h1>
                    <p class="lead text-secondary">Shop vegetables, fruits, pantry essentials, snacks, and more with fast delivery and easy checkout.</p>
                    <a class="btn btn-success btn-lg" href="<?= site_url('user/shop.php') ?>">Start Shopping</a>
                </div>
                <div class="col-lg-5 text-center">
                    <img src="https://images.unsplash.com/photo-1506806732259-39c2d0268443?auto=format&fit=crop&w=800&q=80" alt="Grocery" class="img-fluid rounded-4 shadow-sm">
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="card-title text-success">Easy Ordering</h5>
                <p class="card-text">Browse categories, add items to cart, and complete your order in minutes.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="card-title text-success">Secure Payment</h5>
                <p class="card-text">Cash on delivery and online payment options with order confirmation.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="card-title text-success">Fast Delivery</h5>
                <p class="card-text">Track your order with delivery status updates and timely delivery reports.</p>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/templates/footer.php'; ?>
