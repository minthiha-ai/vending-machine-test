<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — VendingPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: #0d1117; min-height: 100vh; display:flex; align-items:center; }
        .login-card { border: 1px solid #30363d; background-color: #161b22; border-radius: 12px; }
        .brand-icon { font-size: 3rem; color: #58a6ff; }
    </style>
</head>
<body>
<div class="container" style="max-width:420px;">
    <div class="text-center mb-4">
        <i class="bi bi-cup-hot-fill brand-icon"></i>
        <h2 class="fw-bold mt-2 text-white">VendingPro</h2>
        <p class="text-muted">Sign in to your account</p>
    </div>

    <div class="login-card p-4 shadow-lg">

        <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= e($errors['general']) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="/login" novalidate id="loginForm">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" id="username" name="username"
                           class="form-control <?= !empty($errors['username']) ? 'is-invalid' : '' ?>"
                           value="<?= e($old_username ?? '') ?>"
                           autocomplete="username" required>
                    <?php if (!empty($errors['username'])): ?>
                    <div class="invalid-feedback"><?= e($errors['username']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" id="password" name="password"
                           class="form-control <?= !empty($errors['password']) ? 'is-invalid' : '' ?>"
                           autocomplete="current-password" required>
                    <button class="btn btn-outline-secondary" type="button" id="togglePwd">
                        <i class="bi bi-eye"></i>
                    </button>
                    <?php if (!empty($errors['password'])): ?>
                    <div class="invalid-feedback"><?= e($errors['password']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <button class="btn btn-primary w-100" type="submit">
                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
            </button>
        </form>

        <hr class="my-3">
        <p class="text-center text-muted small mb-0">
            Demo — Admin: <code>admin / admin123</code> &nbsp;|&nbsp; User: <code>user / user123</code>
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Toggle password visibility
document.getElementById('togglePwd').addEventListener('click', function () {
    const pwd = document.getElementById('password');
    const icon = this.querySelector('i');
    if (pwd.type === 'password') {
        pwd.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        pwd.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
});

// Client-side validation
document.getElementById('loginForm').addEventListener('submit', function (e) {
    let valid = true;
    ['username', 'password'].forEach(id => {
        const el = document.getElementById(id);
        if (!el.value.trim()) {
            el.classList.add('is-invalid');
            valid = false;
        } else {
            el.classList.remove('is-invalid');
        }
    });
    if (!valid) e.preventDefault();
});
</script>
</body>
</html>
