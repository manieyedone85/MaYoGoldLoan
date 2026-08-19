<?php $CI =& get_instance(); $error = $CI->session->flashdata('error'); ?>
<?php if (! empty($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="GET" action="<?php echo base_url('admin/settlements'); ?>" class="d-flex gap-2 mb-3">
    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" class="form-control w-auto" placeholder="Loan A/C number, customer mobile, or name — also filters history below">
    <button type="submit" class="btn btn-outline-secondary">Search</button>
</form>

<?php if ($search !== ''): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold">Search Results</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light"><tr><th>Loan A/C</th><th>Customer</th><th>Status</th><th>Sanctioned</th><th>Pending Interest</th><th>Total Payable</th><th class="text-end">Action</th></tr></thead>
            <tbody>
                <?php if (empty($matches)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No matching loan.</td></tr>
                <?php else: foreach ($matches as $loan): ?>
                    <tr>
                        <td><a href="<?php echo base_url('admin/loans/' . $loan['id']); ?>"><?php echo htmlspecialchars($loan['loan_account_number'] ?? 'Pending disbursement'); ?></a></td>
                        <td><?php echo htmlspecialchars($loan['customer_name'] ?? '—'); ?></td>
                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($loan['status']); ?></span></td>
                        <td>₹<?php echo number_format($loan['statement']['sanctioned_amount'], 2); ?></td>
                        <td>₹<?php echo number_format($loan['statement']['pending_interest'], 2); ?></td>
                        <td>₹<?php echo number_format($loan['statement']['total_payable_to_close'], 2); ?></td>
                        <td class="text-end">
                            <?php if ($loan['eligible']): ?>
                                <button type="button" class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target="#settleModal<?php echo (int) $loan['id']; ?>">Settle</button>
                            <?php else: ?>
                                <span class="text-muted small">Not eligible</span>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <?php if ($loan['eligible']): ?>
                    <div class="modal fade" id="settleModal<?php echo (int) $loan['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="<?php echo base_url('admin/settlements/' . $loan['id'] . '/settle'); ?>">
                                    <div class="modal-header"><h5 class="modal-title">Settle — <?php echo htmlspecialchars($loan['loan_account_number'] ?? 'Loan #' . $loan['id']); ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                    <div class="modal-body">
                                        <p class="text-muted small">Total payable to close: ₹<?php echo number_format($loan['statement']['total_payable_to_close'], 2); ?></p>
                                        <label class="form-label">Total Amount Collected</label>
                                        <input type="number" step="0.01" name="total_amount_collected" class="form-control" value="<?php echo htmlspecialchars($loan['statement']['total_payable_to_close']); ?>" required>
                                    </div>
                                    <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-dark">Settle</button></div>
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
    <div class="card-header bg-white fw-semibold">Recent Settlements</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead class="table-light"><tr><th>Loan A/C</th><th>Customer</th><th>Amount Collected</th><th>Closure Date</th><th></th></tr></thead>
            <tbody>
                <?php if (empty($history)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">No settlements yet.</td></tr>
                <?php else: foreach ($history as $c): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($c['loan_account_number'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($c['customer_name'] ?? '—'); ?></td>
                        <td>₹<?php echo number_format($c['total_amount_collected'], 2); ?></td>
                        <td><?php echo htmlspecialchars(date('d-M-Y', strtotime($c['closure_date']))); ?></td>
                        <td><a href="<?php echo base_url('admin/settlements/' . $c['id'] . '/receipt'); ?>" class="btn btn-sm btn-outline-secondary" target="_blank"><i class="bi bi-receipt"></i></a></td>
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
                        <a class="page-link" href="<?php echo base_url('admin/settlements?' . http_build_query(array_merge($history_filters, array('history_page' => $p)))); ?>"><?php echo $p; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>

