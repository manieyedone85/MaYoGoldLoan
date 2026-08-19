<?php $CI =& get_instance(); $error = $CI->session->flashdata('error'); ?>
<?php if (! empty($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="GET" action="<?php echo base_url('admin/interest-collections'); ?>" class="d-flex gap-2 mb-3">
    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" class="form-control w-auto" placeholder="Loan A/C number, customer mobile, or name — also filters history below">
    <button type="submit" class="btn btn-outline-secondary">Search</button>
</form>

<?php if ($search !== ''): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold">Search Results</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light"><tr><th>Loan A/C</th><th>Customer</th><th>Status</th><th>Sanctioned</th><th>Interest Due</th><th class="text-end">Action</th></tr></thead>
            <tbody>
                <?php if (empty($matches)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No matching loan.</td></tr>
                <?php else: foreach ($matches as $loan): ?>
                    <tr>
                        <td><a href="<?php echo base_url('admin/loans/' . $loan['id']); ?>"><?php echo htmlspecialchars($loan['loan_account_number'] ?? 'Pending disbursement'); ?></a></td>
                        <td><?php echo htmlspecialchars($loan['customer_name'] ?? '—'); ?></td>
                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($loan['status']); ?></span></td>
                        <td>₹<?php echo number_format($loan['sanctioned_amount'], 2); ?></td>
                        <td>₹<?php echo number_format($loan['interest_due'], 2); ?></td>
                        <td class="text-end"><button type="button" class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target="#collectModal<?php echo (int) $loan['id']; ?>">Collect</button></td>
                    </tr>

                    <div class="modal fade" id="collectModal<?php echo (int) $loan['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="<?php echo base_url('admin/interest-collections/' . $loan['id'] . '/collect'); ?>">
                                    <div class="modal-header"><h5 class="modal-title">Collect Interest — <?php echo htmlspecialchars($loan['loan_account_number'] ?? 'Loan #' . $loan['id']); ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                    <div class="modal-body">
                                        <p class="text-muted small">Interest due: ₹<?php echo number_format($loan['interest_due'], 2); ?></p>
                                        <div class="mb-3">
                                            <label class="form-label">Amount</label>
                                            <input type="number" step="0.01" name="amount" class="form-control" value="<?php echo htmlspecialchars($loan['interest_due']); ?>" required>
                                        </div>
                                        <div class="mb-1">
                                            <label class="form-label">Mode</label>
                                            <select name="mode" class="form-select" required>
                                                <option value="CASH">Cash</option>
                                                <option value="UPI">UPI</option>
                                                <option value="BANK_TRANSFER">Bank Transfer</option>
                                                <option value="CARD">Card</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-dark">Collect</button></div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold">Recent Collections</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead class="table-light"><tr><th>Loan A/C</th><th>Customer</th><th>Amount</th><th>Mode</th><th>Receipt No.</th><th></th></tr></thead>
            <tbody>
                <?php if (empty($history)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No interest collected yet.</td></tr>
                <?php else: foreach ($history as $c): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($c['loan_account_number'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($c['customer_name'] ?? '—'); ?></td>
                        <td>₹<?php echo number_format($c['amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars($c['mode']); ?></td>
                        <td><?php echo htmlspecialchars($c['receipt_number'] ?? '—'); ?></td>
                        <td><a href="<?php echo base_url('admin/interest-collections/' . $c['id'] . '/receipt'); ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-receipt"></i></a></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($history_pagination['last_page'] > 1): ?>
        <div class="card-footer bg-white">
            <ul class="pagination pagination-sm mb-0">
                <?php for ($p = 1; $p <= $history_pagination['last_page']; $p++): ?>
                    <li class="page-item <?php echo $p == $history_pagination['page'] ? 'active' : ''; ?>">
                        <a class="page-link" href="<?php echo base_url('admin/interest-collections?' . http_build_query(array_merge($history_filters, array('history_page' => $p)))); ?>"><?php echo $p; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>

