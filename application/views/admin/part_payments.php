<?php $CI =& get_instance(); $error = $CI->session->flashdata('error'); ?>
<?php if (! empty($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="GET" action="<?php echo base_url('admin/part-payments'); ?>" class="d-flex gap-2 mb-3">
    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" class="form-control w-auto" placeholder="Loan A/C number or customer mobile">
    <button type="submit" class="btn btn-outline-secondary">Search</button>
</form>

<?php if ($search !== ''): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold">Search Results</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light"><tr><th>Loan A/C</th><th>Customer</th><th>Status</th><th>Sanctioned</th><th>Excess Eligible</th><th class="text-end">Action</th></tr></thead>
            <tbody>
                <?php if (empty($matches)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No matching loan.</td></tr>
                <?php else: foreach ($matches as $loan): ?>
                    <tr>
                        <td><a href="<?php echo base_url('admin/loans/' . $loan['id']); ?>"><?php echo htmlspecialchars($loan['loan_account_number'] ?? 'Pending disbursement'); ?></a></td>
                        <td><?php echo htmlspecialchars($loan['customer_name'] ?? '—'); ?></td>
                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($loan['status']); ?></span></td>
                        <td>₹<?php echo number_format($loan['sanctioned_amount'], 2); ?></td>
                        <td>₹<?php echo number_format($loan['excess_amount_eligible'], 2); ?></td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target="#payModal<?php echo (int) $loan['id']; ?>">Part Payment</button>
                            <button type="button" class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#reloadModal<?php echo (int) $loan['id']; ?>">Re-loan</button>
                        </td>
                    </tr>

                    <div class="modal fade" id="payModal<?php echo (int) $loan['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="<?php echo base_url('admin/part-payments/' . $loan['id'] . '/pay'); ?>">
                                    <div class="modal-header"><h5 class="modal-title">Part Payment — <?php echo htmlspecialchars($loan['loan_account_number'] ?? 'Loan #' . $loan['id']); ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                    <div class="modal-body">
                                        <div class="mb-3"><label class="form-label">Principal Amount</label><input type="number" step="0.01" name="principal_amount" class="form-control" value="0"></div>
                                        <div class="mb-1"><label class="form-label">Interest Amount</label><input type="number" step="0.01" name="interest_amount" class="form-control" value="0"></div>
                                    </div>
                                    <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-dark">Record Payment</button></div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="reloadModal<?php echo (int) $loan['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="<?php echo base_url('admin/part-payments/' . $loan['id'] . '/reload'); ?>">
                                    <div class="modal-header"><h5 class="modal-title">Re-loan — <?php echo htmlspecialchars($loan['loan_account_number'] ?? 'Loan #' . $loan['id']); ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                    <div class="modal-body">
                                        <p class="text-muted small">Eligible excess gold value: ₹<?php echo number_format($loan['excess_amount_eligible'], 2); ?></p>
                                        <div class="mb-1"><label class="form-label">Reload Amount</label><input type="number" step="0.01" name="reload_amount" class="form-control" max="<?php echo htmlspecialchars($loan['excess_amount_eligible']); ?>" required></div>
                                    </div>
                                    <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-dark">Re-loan</button></div>
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

<div class="row g-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Recent Part Payments</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Loan A/C</th><th>Principal</th><th>Interest</th></tr></thead>
                    <tbody>
                        <?php if (empty($payment_history)): ?>
                            <tr><td colspan="3" class="text-center text-muted py-3">No part payments yet.</td></tr>
                        <?php else: foreach ($payment_history as $p): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($p['loan_account_number'] ?? '—'); ?></td>
                                <td>₹<?php echo number_format($p['principal_amount'], 2); ?></td>
                                <td>₹<?php echo number_format($p['interest_amount'], 2); ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Recent Re-loans</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Loan A/C</th><th>Reload Amt.</th><th>Eligible</th></tr></thead>
                    <tbody>
                        <?php if (empty($reload_history)): ?>
                            <tr><td colspan="3" class="text-center text-muted py-3">No re-loans yet.</td></tr>
                        <?php else: foreach ($reload_history as $r): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($r['loan_account_number'] ?? '—'); ?></td>
                                <td>₹<?php echo number_format($r['reload_amount'], 2); ?></td>
                                <td>₹<?php echo number_format($r['excess_amount_eligible'], 2); ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
