<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login · Gold Loan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="d-flex align-items-center justify-content-center" style="min-height:100vh;">
    <div class="card shadow-sm" style="width: 380px;">
        <div class="card-body p-4">
            <h4 class="text-center mb-1"><i class="bi bi-gem"></i> Gold Loan Admin</h4>
            <p class="text-center text-muted small mb-4">Sign in to manage employees, customers, loans &amp; reports</p>

            <?php if (! empty($error)): ?>
                <div class="alert alert-danger py-2 small"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="<?php echo base_url('admin/login'); ?>">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="text" name="email" class="form-control" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-dark w-100">Sign In</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
