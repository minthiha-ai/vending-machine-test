<?php
/**
 * User Storefront — product cards with confirmation modal.
 *
 * Variables: $products, $sort, $dir
 */
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 fw-bold mb-0"><i class="bi bi-shop me-2"></i>Available Products</h1>
        <p class="text-muted mb-0"><?= count($products) ?> item<?= count($products) !== 1 ? 's' : '' ?> available</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/?sort=name&dir=<?= $sort === 'name' && $dir === 'asc' ? 'desc' : 'asc' ?>"
           class="btn btn-sm btn-outline-secondary <?= $sort === 'name' ? 'active' : '' ?>">
            <i class="bi bi-sort-alpha-down me-1"></i>Name
        </a>
        <a href="/?sort=price&dir=<?= $sort === 'price' && $dir === 'asc' ? 'desc' : 'asc' ?>"
           class="btn btn-sm btn-outline-secondary <?= $sort === 'price' ? 'active' : '' ?>">
            <i class="bi bi-currency-dollar me-1"></i>Price
        </a>
    </div>
</div>

<?php if (empty($products)): ?>
<div class="text-center py-5">
    <i class="bi bi-inbox display-4 text-muted d-block mb-3"></i>
    <p class="text-muted fs-5">No products available right now.</p>
</div>
<?php else: ?>
<div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4 g-4">
    <?php foreach ($products as $product): ?>
    <div class="col">
        <div class="card h-100 product-card">
            <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h5 class="card-title fw-bold mb-0"><?= e($product['name']) ?></h5>
                    <?php if ($product['quantity_available'] === 0): ?>
                        <span class="badge bg-danger qty-badge">Out of Stock</span>
                    <?php elseif ($product['quantity_available'] < 5): ?>
                        <span class="badge bg-warning text-dark qty-badge badge-low-stock">
                            Low Stock
                        </span>
                    <?php else: ?>
                        <span class="badge bg-success qty-badge"><?= (int) $product['quantity_available'] ?> left</span>
                    <?php endif; ?>
                </div>

                <div class="mt-auto">
                    <p class="price-tag mb-3">$<?= number_format((float) $product['price'], 3) ?></p>
                    <?php if ($product['quantity_available'] > 0): ?>
                    <button type="button"
                            class="btn btn-primary w-100"
                            data-bs-toggle="modal"
                            data-bs-target="#buyModal"
                            data-id="<?= (int) $product['id'] ?>"
                            data-name="<?= e($product['name']) ?>"
                            data-price="<?= number_format((float) $product['price'], 3) ?>">
                        <i class="bi bi-cart-plus me-2"></i>Buy Now
                    </button>
                    <?php else: ?>
                    <button class="btn btn-secondary w-100" disabled>
                        <i class="bi bi-x-circle me-2"></i>Unavailable
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Purchase Confirmation Modal -->
<div class="modal fade" id="buyModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-cart-check-fill text-primary me-2"></i>Confirm Purchase</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1">You are about to purchase:</p>
                <div class="d-flex align-items-center gap-3 mt-2">
                    <i class="bi bi-cup-hot fs-2 text-primary"></i>
                    <div>
                        <div class="fw-bold fs-5" id="modalProductName"></div>
                        <div class="price-tag" id="modalProductPrice"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="buyForm" method="POST">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-bag-check me-2"></i>Confirm Purchase
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const buyModal = document.getElementById('buyModal');
buyModal.addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    document.getElementById('modalProductName').textContent = btn.dataset.name;
    document.getElementById('modalProductPrice').textContent = '$' + btn.dataset.price;
    document.getElementById('buyForm').action = '/buy/' + btn.dataset.id;
});
</script>
