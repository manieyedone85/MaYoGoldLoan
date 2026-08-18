<?php $CI =& get_instance(); $error = $CI->session->flashdata('error'); ?>
<?php if (! empty($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="GET" action="<?php echo base_url('admin/renewals'); ?>" class="d-flex gap-2 mb-3">
    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" class="form-control w-auto" placeholder="Loan A/C number or customer mobile">
    <button type="submit" class="btn btn-outline-secondary">Search</button>
</form>

<?php if ($search !== ''): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold">Search Results</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light"><tr><th>Loan A/C</th><th>Customer</th><th>Status</th><th>Interest Due</th><th>Due Date</th><th class="text-end">Action</th></tr></thead>
            <tbody>
                <?php if (empty($matches)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No matching loan.</td></tr>
                <?php else: foreach ($matches as $loan): ?>
                    <tr>
                        <td><a href="<?php echo base_url('admin/loans/' . $loan['id']); ?>"><?php echo htmlspecialchars($loan['loan_account_number'] ?? 'Pending disbursement'); ?></a></td>
                        <td><?php echo htmlspecialchars($loan['customer_name'] ?? '—'); ?></td>
                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($loan['status']); ?></span></td>
                        <td>₹<?php echo number_format($loan['interest_due'], 2); ?></td>
                        <td><?php echo ! empty($loan['due_date']) ? htmlspecialchars(date('d-M-Y', strtotime($loan['due_date']))) : '—'; ?></td>
                        <td class="text-end">
                            <?php if ($loan['eligible']): ?>
                                <button type="button" class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target="#renewModal<?php echo (int) $loan['id']; ?>">Renew</button>
                            <?php else: ?>
                                <span class="text-muted small">Not eligible</span>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <?php if ($loan['eligible']): ?>
                    <div class="modal fade" id="renewModal<?php echo (int) $loan['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="<?php echo base_url('admin/renewals/' . $loan['id'] . '/renew'); ?>">
                                    <div class="modal-header"><h5 class="modal-title">Renew — <?php echo htmlspecialchars($loan['loan_account_number'] ?? 'Loan #' . $loan['id']); ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                    <div class="modal-body">
                                        <p class="text-muted small">Interest due: ₹<?php echo number_format($loan['interest_due'], 2); ?> (must be fully paid to renew)</p>
                                        <div class="mb-3"><label class="form-label">Interest Paid</label><input type="number" step="0.01" name="interest_paid" class="form-control" value="<?php echo htmlspecialchars($loan['interest_due']); ?>" required></div>
                                        <div class="mb-1"><label class="form-label">Renewal Charges</label><input type="number" step="0.01" name="renewal_charges" class="form-control" value="0"></div>
                                    </div>
                                    <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-dark">Renew</button></div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold">Recent Renewals</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead class="table-light"><tr><th>Loan A/C</th><th>Customer</th><th>Interest Paid</th><th>Charges</th><th>New Due Date</th><th>Previous Due Date</th></tr></thead>
            <tbody>
                <?php if (empty($history)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No renewals yet.</td></tr>
                <?php else: foreach ($history as $r): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($r['loan_account_number'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($r['customer_name'] ?? '—'); ?></td>
                        <td>₹<?php echo number_format($r['interest_paid'], 2); ?></td>
                        <td>₹<?php echo number_format($r['renewal_charges'], 2); ?></td>
                        <td><?php echo htmlspecialchars(date('d-M-Y', strtotime($r['new_due_date']))); ?></td>
                        <td><?php echo ! empty($r['previous_due_date']) ? htmlspecialchars(date('d-M-Y', strtotime($r['previous_due_date']))) : '—'; ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
