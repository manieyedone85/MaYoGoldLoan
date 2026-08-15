<?php $CI =& get_instance(); $error = $CI->session->flashdata('error'); ?>
<?php if (! empty($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="GET" action="<?php echo base_url('admin/customers'); ?>" class="d-flex gap-2 mb-3">
    <input type="text" name="search" value="<?php echo htmlspecialchars($filters['search']); ?>" class="form-control w-auto" placeholder="Search name / code / mobile">
    <select name="kyc_status" class="form-select w-auto">
        <option value="">All KYC status</option>
        <option value="PENDING" <?php echo $filters['kyc_status'] === 'PENDING' ? 'selected' : ''; ?>>Pending</option>
        <option value="VERIFIED" <?php echo $filters['kyc_status'] === 'VERIFIED' ? 'selected' : ''; ?>>Verified</option>
        <option value="REJECTED" <?php echo $filters['kyc_status'] === 'REJECTED' ? 'selected' : ''; ?>>Rejected</option>
    </select>
    <button type="submit" class="btn btn-outline-secondary">Filter</button>
</form>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Mobile</th>
                    <th>Branch</th>
                    <th>KYC Status</th>
                    <th>Blacklisted</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($customers)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No customers found.</td></tr>
                <?php else: foreach ($customers as $customer): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($customer['customer_code']); ?></td>
                        <td><?php echo htmlspecialchars($customer['name']); ?></td>
                        <td><?php echo htmlspecialchars($customer['mobile']); ?></td>
                        <td><?php echo htmlspecialchars($customer['branch_name'] ?? '—'); ?></td>
                        <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($customer['kyc_status']); ?></span></td>
                        <td>
                            <?php if ($customer['is_blacklisted']): ?>
                                <span class="badge bg-danger">Blacklisted</span>
                            <?php else: ?>
                                <span class="text-muted small">No</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <a href="<?php echo base_url('admin/customers/' . $customer['id']); ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                            <form method="POST" action="<?php echo base_url('admin/customers/' . $customer['id'] . '/blacklist'); ?>" class="d-inline" onsubmit="return confirm('Are you sure?');">
                                <button type="submit" class="btn btn-sm btn-outline-<?php echo $customer['is_blacklisted'] ? 'success' : 'danger'; ?>">
                                    <?php echo $customer['is_blacklisted'] ? 'Unblock' : 'Blacklist'; ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($pagination['last_page'] > 1): ?>
        <div class="card-footer bg-white">
            <ul class="pagination pagination-sm mb-0">
                <?php for ($p = 1; $p <= $pagination['last_page']; $p++): ?>
                    <li class="page-item <?php echo $p == $pagination['page'] ? 'active' : ''; ?>">
                        <a class="page-link" href="<?php echo base_url('admin/customers?' . http_build_query(array_merge($filters, array('page' => $p)))); ?>"><?php echo $p; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>
