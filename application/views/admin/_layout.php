<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($page_title); ?> · Gold Loan Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; }
        .admin-sidebar { min-height: 100vh; background: #1e2a38; }
        .admin-sidebar a { color: #c9d3dc; }
        .admin-sidebar a.active, .admin-sidebar a:hover { color: #fff; background: #2b3b4e; }
        .admin-sidebar .brand { color: #fff; }
    </style>
</head>
<body>
<div class="d-flex">
    <?php $CI =& get_instance(); $uri = $CI->uri->uri_string(); ?>
    <nav class="admin-sidebar p-3" style="width:230px;">
        <div class="brand fs-5 fw-semibold mb-4"><i class="bi bi-gem"></i> Gold Loan Admin</div>
        <div class="nav flex-column gap-1">
            <a class="nav-link rounded px-2 py-2 <?php echo strpos($uri, 'admin/dashboard') !== false ? 'active' : ''; ?>" href="<?php echo base_url('admin/dashboard'); ?>"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
            <a class="nav-link rounded px-2 py-2 <?php echo strpos($uri, 'admin/employees') !== false ? 'active' : ''; ?>" href="<?php echo base_url('admin/employees'); ?>"><i class="bi bi-people me-2"></i>Employees</a>
            <a class="nav-link rounded px-2 py-2 <?php echo strpos($uri, 'admin/customers') !== false ? 'active' : ''; ?>" href="<?php echo base_url('admin/customers'); ?>"><i class="bi bi-person-vcard me-2"></i>Customers</a>
            <a class="nav-link rounded px-2 py-2 <?php echo strpos($uri, 'admin/loans') !== false ? 'active' : ''; ?>" href="<?php echo base_url('admin/loans'); ?>"><i class="bi bi-cash-coin me-2"></i>Loans</a>
            <a class="nav-link rounded px-2 py-2 <?php echo strpos($uri, 'admin/reports') !== false ? 'active' : ''; ?>" href="<?php echo base_url('admin/reports'); ?>"><i class="bi bi-bar-chart me-2"></i>Reports</a>
        </div>
        <form method="POST" action="<?php echo base_url('admin/logout'); ?>" class="mt-4">
            <button type="submit" class="btn btn-outline-light btn-sm w-100"><i class="bi bi-box-arrow-left me-1"></i>Logout</button>
        </form>
    </nav>

    <main class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0"><?php echo htmlspecialchars($page_title); ?></h4>
            <span class="text-muted small"><?php echo htmlspecialchars($current_user['name']); ?> &middot; <?php echo htmlspecialchars($current_user['role_name']); ?></span>
        </div>
        <?php if (! empty($flash)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($flash); ?></div>
        <?php endif; ?>

        <?php $CI->load->view($content_view, $view_data); ?>
    </main>
</div>
</body>
</html>
