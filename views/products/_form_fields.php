<?php
/**
 * Shared form fields partial for create/edit product forms.
 *
 * Expected vars: $errors (array), $product (array|null)
 */
$nameVal  = old('name',               $product['name']               ?? '');
$priceVal = old('price',              $product['price']              ?? '');
$qtyVal   = old('quantity_available', $product['quantity_available'] ?? '');
?>

<div class="mb-3">
    <label for="name" class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
    <input type="text" id="name" name="name"
           class="form-control <?= !empty($errors['name']) ? 'is-invalid' : '' ?>"
           value="<?= e($nameVal) ?>"
           maxlength="100"
           placeholder="e.g. Coke" required>
    <?php if (!empty($errors['name'])): ?>
        <div class="invalid-feedback"><?= e($errors['name']) ?></div>
    <?php else: ?>
        <div class="form-text">Max 100 characters.</div>
    <?php endif; ?>
</div>

<div class="mb-3">
    <label for="price" class="form-label fw-semibold">Price (USD) <span class="text-danger">*</span></label>
    <div class="input-group">
        <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
        <input type="number" id="price" name="price" step="0.001" min="0.001"
               class="form-control <?= !empty($errors['price']) ? 'is-invalid' : '' ?>"
               value="<?= e($priceVal) ?>"
               placeholder="0.000" required>
        <?php if (!empty($errors['price'])): ?>
            <div class="invalid-feedback"><?= e($errors['price']) ?></div>
        <?php endif; ?>
    </div>
    <div class="form-text">Must be greater than 0. Up to 3 decimal places (e.g. 6.885).</div>
</div>

<div class="mb-3">
    <label for="quantity_available" class="form-label fw-semibold">Quantity Available <span class="text-danger">*</span></label>
    <input type="number" id="quantity_available" name="quantity_available" min="0" step="1"
           class="form-control <?= !empty($errors['quantity_available']) ? 'is-invalid' : '' ?>"
           value="<?= e($qtyVal) ?>"
           placeholder="0" required>
    <?php if (!empty($errors['quantity_available'])): ?>
        <div class="invalid-feedback"><?= e($errors['quantity_available']) ?></div>
    <?php else: ?>
        <div class="form-text">Must be 0 or greater.</div>
    <?php endif; ?>
</div>

<script>
// Real-time JS validation
(function () {
    const form = document.getElementById('productForm');
    if (!form) return;

    const rules = {
        name: {
            test: v => v.trim().length > 0 && v.trim().length <= 100,
            msg:  'Name is required and must be 100 characters or fewer.'
        },
        price: {
            test: v => v !== '' && parseFloat(v) > 0,
            msg:  'Price must be a positive number.'
        },
        quantity_available: {
            test: v => v !== '' && Number.isInteger(parseFloat(v)) && parseInt(v) >= 0,
            msg:  'Quantity must be a non-negative whole number.'
        }
    };

    Object.entries(rules).forEach(([id, rule]) => {
        const el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('input', function () {
            const fb = this.nextElementSibling?.classList.contains('invalid-feedback')
                ? this.nextElementSibling
                : null;
            if (rule.test(this.value)) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
                if (fb) fb.textContent = '';
            } else {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
                if (fb) fb.textContent = rule.msg;
            }
        });
    });

    form.addEventListener('submit', function (e) {
        let valid = true;
        Object.entries(rules).forEach(([id, rule]) => {
            const el = document.getElementById(id);
            if (!el) return;
            if (!rule.test(el.value)) {
                el.classList.add('is-invalid');
                valid = false;
            }
        });
        if (!valid) e.preventDefault();
    });
})();
</script>
