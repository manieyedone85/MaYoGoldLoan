<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($page_title); ?> · Aurum Finance</title>
    <link rel="icon" type="image/jpeg" href="<?php echo base_url('assets/images/logo-aurum-fab.jpg'); ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; }
        .admin-sidebar { min-height: 100vh; width: 260px; flex: 0 0 260px; background: #1e2a38; overflow-y: auto; }
        .admin-sidebar a { color: #c9d3dc; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .admin-sidebar a.active, .admin-sidebar a:hover { color: #fff; background: #2b3b4e; }
        .admin-sidebar .brand { color: #fff; }
        .admin-sidebar .nav-group-toggle { color: #c9d3dc; cursor: pointer; }
        .admin-sidebar .nav-group-toggle:hover { color: #fff; background: #2b3b4e; }
        .admin-sidebar .nav-group-toggle.active-group { color: #fff; }
        .admin-sidebar .nav-group-toggle .chevron { transition: transform 0.2s ease; margin-left: auto; font-size: 0.75rem; }
        .admin-sidebar .nav-group-toggle[aria-expanded="true"] .chevron { transform: rotate(90deg); }
        .admin-sidebar .nav-subitem { padding-left: 2.25rem !important; font-size: 0.925rem; }
        /* Printable receipts (loan_receipt, disbursement_receipt, settlement_receipt,
           gold_release_receipt, interest_receipt) mark chrome as .no-print so a
           browser "Print" button on the page produces a clean receipt only. */
        @media print {
            @page { size: A4 portrait; margin: 10; }
            .admin-sidebar, .no-print { display: none !important; }
            main { padding: 0 !important; }
            body { background: #fff !important; }
        }
    </style>
</head>
<body>
<div class="d-flex">
    <?php
        $CI =& get_instance();
        $uri = $CI->uri->uri_string();
        $role_code = $current_user['role_code'] ?? null;

        // Nav items are gated to the same role list its controller enforces
        // (ADMIN always passes -- see Admin_Controller::require_admin_role()).
        // `roles => null` means open to every logged-in staff role.
        // Standalone items render as a plain link; grouped items render under a
        // collapsible group header, auto-expanded when the active page is inside it.
        $nav_items = array(
            array('path' => 'admin/dashboard', 'icon' => 'bi-speedometer2', 'label' => 'Dashboard', 'roles' => null),
            array(
                'group' => 'Customers & KYC', 'icon' => 'bi-person-vcard',
                'items' => array(
                    array('path' => 'admin/customers', 'icon' => 'bi-person-vcard', 'label' => 'Customers', 'roles' => null),
                    array('path' => 'admin/kyc', 'icon' => 'bi-person-check', 'label' => 'KYC Verification', 'roles' => array('BRANCH_EXECUTIVE', 'BRANCH_MANAGER', 'REGIONAL_MANAGER')),
                ),
            ),
            array(
                'group' => 'Loan Management', 'icon' => 'bi-cash-coin',
                'items' => array(
                    array('path' => 'admin/loans', 'icon' => 'bi-cash-coin', 'label' => 'Loans', 'roles' => null),
                    array('path' => 'admin/loan-approvals', 'icon' => 'bi-check2-square', 'label' => 'Loan Approvals', 'roles' => array('APPRAISER', 'BRANCH_MANAGER', 'REGIONAL_MANAGER', 'OPERATIONS')),
                    array('path' => 'admin/topups', 'icon' => 'bi-plus-circle', 'label' => 'Top-ups', 'roles' => array('APPRAISER', 'BRANCH_MANAGER', 'REGIONAL_MANAGER', 'CASHIER')),
                    array('path' => 'admin/renewals', 'icon' => 'bi-arrow-repeat', 'label' => 'Renewals', 'roles' => array('CASHIER')),
                    array('path' => 'admin/settlements', 'icon' => 'bi-flag', 'label' => 'Settlements', 'roles' => array('CASHIER', 'BRANCH_MANAGER')),
                    array('path' => 'admin/gold-releases', 'icon' => 'bi-unlock', 'label' => 'Gold Release', 'roles' => null),
                ),
            ),
            array(
                'group' => 'Cashier Operations', 'icon' => 'bi-cash-stack',
                'items' => array(
                    array('path' => 'admin/disbursements', 'icon' => 'bi-cash-stack', 'label' => 'Disbursements', 'roles' => array('CASHIER')),
                    array('path' => 'admin/interest-collections', 'icon' => 'bi-receipt', 'label' => 'Interest Collections', 'roles' => array('CASHIER')),
                    array('path' => 'admin/part-payments', 'icon' => 'bi-cash', 'label' => 'Part Payments', 'roles' => array('CASHIER')),
                ),
            ),
            array(
                'group' => 'Jewellery & Inventory', 'icon' => 'bi-gem',
                'items' => array(
                    array('path' => 'admin/jewellery-items', 'icon' => 'bi-gem', 'label' => 'Jewellery Items', 'roles' => array('APPRAISER', 'BRANCH_MANAGER', 'BRANCH_EXECUTIVE', 'OPERATIONS')),
                    array('path' => 'admin/inventory', 'icon' => 'bi-box-seam', 'label' => 'Inventory', 'roles' => null),
                    array('path' => 'admin/auctions', 'icon' => 'bi-hammer', 'label' => 'Auctions', 'roles' => array('BRANCH_MANAGER', 'REGIONAL_MANAGER')),
                ),
            ),
            array(
                'group' => 'Finance', 'icon' => 'bi-journal-text',
                'items' => array(
                    array('path' => 'admin/accounting', 'icon' => 'bi-journal-text', 'label' => 'Accounting', 'roles' => array('FINANCE')),
                    array('path' => 'admin/reports', 'icon' => 'bi-bar-chart', 'label' => 'Reports', 'roles' => null),
                ),
            ),
            array(
                'group' => 'Administration', 'icon' => 'bi-gear',
                'items' => array(
                    array('path' => 'admin/employees', 'icon' => 'bi-people', 'label' => 'Employees', 'roles' => array('OPERATIONS')),
                    array('path' => 'admin/masters', 'icon' => 'bi-sliders', 'label' => 'Masters', 'roles' => array('OPERATIONS', 'APPRAISER', 'BRANCH_MANAGER', 'REGIONAL_MANAGER')),
                    array('path' => 'admin/ops', 'icon' => 'bi-gear', 'label' => 'Ops', 'roles' => array('OPERATIONS')),
                ),
            ),
        );

        $can_see = function ($item) use ($role_code) {
            return $role_code === 'ADMIN' || $item['roles'] === null || in_array($role_code, $item['roles'], true);
        };
    ?>
    <nav class="admin-sidebar p-3">
        <div class="brand fs-5 fw-semibold mb-4"><i class="bi bi-gem"></i> Aurum Finance</div>
        <div class="nav flex-column gap-1">
            <?php foreach ($nav_items as $g => $entry): ?>
                <?php if (! isset($entry['group'])): ?>
                    <?php if (! $can_see($entry)) continue; ?>
                    <a class="nav-link rounded px-2 py-2 <?php echo strpos($uri, $entry['path']) !== false ? 'active' : ''; ?>" href="<?php echo base_url($entry['path']); ?>"><i class="bi <?php echo $entry['icon']; ?> me-2"></i><?php echo $entry['label']; ?></a>
                <?php else: ?>
                    <?php
                        $visible_items = array_values(array_filter($entry['items'], $can_see));
                        if (empty($visible_items)) continue;
                        $group_id = 'nav-group-' . $g;
                        $group_has_active = false;
                        foreach ($visible_items as $it) {
                            if (strpos($uri, $it['path']) !== false) { $group_has_active = true; break; }
                        }
                    ?>
                    <a class="nav-link nav-group-toggle rounded px-2 py-2 d-flex align-items-center <?php echo $group_has_active ? 'active-group' : ''; ?>"
                       href="#<?php echo $group_id; ?>" data-bs-toggle="collapse" role="button"
                       aria-expanded="<?php echo $group_has_active ? 'true' : 'false'; ?>" aria-controls="<?php echo $group_id; ?>">
                        <i class="bi <?php echo $entry['icon']; ?> me-2"></i><?php echo $entry['group']; ?>
                        <i class="bi bi-chevron-right chevron"></i>
                    </a>
                    <div class="collapse <?php echo $group_has_active ? 'show' : ''; ?>" id="<?php echo $group_id; ?>">
                        <div class="nav flex-column gap-1">
                            <?php foreach ($visible_items as $it): ?>
                                <a class="nav-link nav-subitem rounded px-2 py-2 <?php echo strpos($uri, $it['path']) !== false ? 'active' : ''; ?>" href="<?php echo base_url($it['path']); ?>"><i class="bi <?php echo $it['icon']; ?> me-2"></i><?php echo $it['label']; ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <form method="POST" action="<?php echo base_url('admin/logout'); ?>" class="mt-4">
            <button type="submit" class="btn btn-outline-light btn-sm w-100"><i class="bi bi-box-arrow-left me-1"></i>Logout</button>
        </form>
    </nav>

    <main class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <h4 class="mb-0"><?php echo htmlspecialchars($page_title); ?></h4>
            <span class="text-muted small"><?php echo htmlspecialchars($current_user['name']); ?> &middot; <?php echo htmlspecialchars($current_user['role_name']); ?></span>
        </div>
        <?php if (! empty($flash)): ?>
            <div class="alert alert-success no-print"><?php echo htmlspecialchars($flash); ?></div>
        <?php endif; ?>

        <?php $CI->load->view($content_view, $view_data); ?>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
