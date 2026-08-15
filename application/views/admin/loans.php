<?php $CI =& get_instance(); $error = $CI->session->flashdata('error'); ?>
<?php if (! empty($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="d-flex justify-content-end mb-3">
    <a href="<?php echo base_url('admin/loans/create'); ?>" class="btn btn-dark"><i class="bi bi-plus-lg"></i> New Loan</a>
</div>

<form method="GET" action="<?php echo base_url('admin/loans'); ?>" class="d-flex gap-2 mb-3">
    <input type="text" name="search" value="<?php echo htmlspecialchars($filters['search']); ?>" class="form-control w-auto" placeholder="Search loan A/C number">
    <select name="status" class="form-select w-auto">
        <option value="">All statuses</option>
        <?php foreach ($statuses as $status): ?>
            <option value="<?php echo htmlspecialchars($status); ?>" <?php echo $filters['status'] === $status ? 'selected' : ''; ?>><?php echo htmlspecialchars($status); ?></option>
        <?php endforeach; ?>
    </select>
    <select name="branch_id" class="form-select w-auto">
        <option value="">All branches</option>
        <?php foreach ($branches as $branch): ?>
            <option value="<?php echo (int) $branch['id']; ?>" <?php echo (string) $filters['branch_id'] === (string) $branch['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($branch['name']); ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-outline-secondary">Filter</button>
</form>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>A/C No.</th>
                    <th>Customer</th>
                    <th>Branch</th>
                    <th>Product</th>
                    <th>Sanctioned</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($loans)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No loans found.</td></tr>
                <?php else: foreach ($loans as $loan): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($loan['loan_account_number']); ?></td>
                        <td><?php echo htmlspecialchars($loan['customer_name'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($loan['branch_name'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($loan['product_name'] ?? '—'); ?></td>
                        <td>₹<?php echo number_format($loan['sanctioned_amount'], 2); ?></td>
                        <td><?php echo ! empty($loan['due_date']) ? htmlspecialchars(date('d-M-Y', strtotime($loan['due_date']))) : '—'; ?></td>
                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($loan['status']); ?></span></td>
                        <td class="text-end">
                            <a href="<?php echo base_url('admin/loans/' . $loan['id']); ?>" class="btn btn-sm btn-outline-secondary">View</a>
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
                        <a class="page-link" href="<?php echo base_url('admin/loans?' . http_build_query(array_merge($filters, array('page' => $p)))); ?>"><?php echo $p; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>
