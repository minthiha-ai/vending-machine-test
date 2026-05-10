<?php
/**
 * Edit Product Form
 *
 * Variables: $product (array), $errors (array)
 */
?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="d-flex align-items-center gap-3 mb-4">
            <a href="/products" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h1 class="h3 fw-bold mb-0"><i class="bi bi-pencil-square me-2"></i>Edit Product</h1>
                <p class="text-muted mb-0">Update the product details below.</p>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="/products/<?= (int) $product['id'] ?>/edit" novalidate id="productForm">
                    <?php include __DIR__ . '/_form_fields.php'; ?>
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Update Product
                        </button>
                        <a href="/products" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
