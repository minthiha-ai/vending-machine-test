<?php
$nextDir = $dir === 'asc' ? 'desc' : 'asc';
$icon    = $dir === 'asc' ? 'bi-sort-up' : 'bi-sort-down';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 fw-bold"><i class="bi bi-box-seam me-2"></i>Product Management</h1>
        <p class="text-muted mb-0">Total: <?= $total ?> product<?= $total !== 1 ? 's' : '' ?></p>
    </div>
    <a href="/products/create" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Add Product
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th style="width:60px;">#</th>
                        <th>
                            <a href="/products?sort=name&dir=<?= $sort === 'name' ? $nextDir : 'asc' ?>&page=<?= $page ?>"
                               class="text-decoration-none text-white d-flex align-items-center gap-1">
                                Name
                                <?php if ($sort === 'name'): ?>
                                <i class="bi <?= $icon ?>"></i>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th>
                            <a href="/products?sort=price&dir=<?= $sort === 'price' ? $nextDir : 'asc' ?>&page=<?= $page ?>"
                               class="text-decoration-none text-white d-flex align-items-center gap-1">
                                Price
                                <?php if ($sort === 'price'): ?>
                                <i class="bi <?= $icon ?>"></i>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th>
                            <a href="/products?sort=quantity_available&dir=<?= $sort === 'quantity_available' ? $nextDir : 'asc' ?>&page=<?= $page ?>"
                               class="text-decoration-none text-white d-flex align-items-center gap-1">
                                Stock
                                <?php if ($sort === 'quantity_available'): ?>
                                <i class="bi <?= $icon ?>"></i>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>No products found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($items as $i => $product): ?>
                    <tr>
                        <td class="text-muted"><?= ($page - 1) * 10 + $i + 1 ?></td>
                        <td class="fw-semibold"><?= e($product['name']) ?></td>
                        <td><span class="text-info fw-bold">$<?= number_format((float) $product['price'], 3) ?></span></td>
                        <td>
                            <?php if ($product['quantity_available'] === 0): ?>
                                <span class="badge bg-danger">Out of Stock</span>
                            <?php elseif ($product['quantity_available'] < 5): ?>
                                <span class="badge bg-warning text-dark badge-low-stock">
                                    <i class="bi bi-exclamation-triangle me-1"></i>Low Stock (<?= (int) $product['quantity_available'] ?>)
                                </span>
                            <?php else: ?>
                                <span class="badge bg-success"><?= (int) $product['quantity_available'] ?> in stock</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <a href="/products/<?= (int) $product['id'] ?>/edit"
                               class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deleteModal"
                                    data-id="<?= (int) $product['id'] ?>"
                                    data-name="<?= e($product['name']) ?>"
                                    title="Delete">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($pages > 1): ?>
    <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-muted">Page <?= $page ?> of <?= $pages ?></small>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <?php for ($p = 1; $p <= $pages; $p++): ?>
                <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                    <a class="page-link" href="/products?page=<?= $p ?>&sort=<?= e($sort) ?>&dir=<?= e($dir) ?>">
                        <?= $p ?>
                    </a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-danger">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete <strong id="deleteProductName"></strong>? This cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash3 me-2"></i>Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const deleteModal = document.getElementById('deleteModal');
deleteModal.addEventListener('show.bs.modal', function (e) {
    const btn  = e.relatedTarget;
    const id   = btn.dataset.id;
    const name = btn.dataset.name;
    document.getElementById('deleteProductName').textContent = name;
    document.getElementById('deleteForm').action = '/products/' + id + '/delete';
});
</script>
