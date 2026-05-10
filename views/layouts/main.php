<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vending Machine</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { min-height: 100vh; background-color: #0d1117; }
        .navbar-brand { font-weight: 700; letter-spacing: 1px; }
        .card { border: 1px solid #30363d; background-color: #161b22; }
        .table { --bs-table-bg: transparent; }
        .badge-low-stock { animation: pulse 1.5s infinite; }
        @keyframes pulse { 0%,100% { opacity:1; } 50% { opacity:.6; } }
        .product-card { transition: transform .15s ease, box-shadow .15s ease; }
        .product-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,.4); }
        .price-tag { font-size: 1.5rem; font-weight: 700; color: #58a6ff; }
        .qty-badge { font-size: .75rem; }
        footer { border-top: 1px solid #30363d; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark border-bottom border-secondary">
    <div class="container-xl">
        <a class="navbar-brand" href="/">
            <i class="bi bi-cup-hot-fill text-primary me-2"></i>VendingPro
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/"><i class="bi bi-grid me-1"></i>Store</a>
                </li>
                <?php if (($_SESSION['user']['role'] ?? '') === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="/products"><i class="bi bi-box-seam me-1"></i>Manage Products</a>
                </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav ms-auto">
                <?php if (!empty($_SESSION['user'])): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i>
                        <?= e($_SESSION['user']['username']) ?>
                        <span class="badge bg-<?= $_SESSION['user']['role'] === 'admin' ? 'danger' : 'secondary' ?> ms-1">
                            <?= e($_SESSION['user']['role']) ?>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item text-danger" href="/logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="/login"><i class="bi bi-box-arrow-in-right me-1"></i>Login</a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="container-xl py-4">

    <?php $flash = \Core\Controller::getFlash(); if ($flash): ?>
    <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
        <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-triangle' ?>-fill me-2"></i>
        <?= e($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?= $content ?>

</main>

<footer class="py-3 mt-auto text-center text-muted small">
    &copy; <?= date('Y') ?> VendingPro &mdash; Built with PHP 8.2 &amp; Bootstrap 5.3
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
