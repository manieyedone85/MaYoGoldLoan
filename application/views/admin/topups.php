<?php $CI =& get_instance(); $error = $CI->session->flashdata('error'); ?>
<?php if (! empty($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php $can_add_jewellery = in_array($role_code, array('APPRAISER', 'BRANCH_MANAGER', 'ADMIN'), true); ?>
<?php $can_approve = in_array($role_code, array('BRANCH_MANAGER', 'REGIONAL_MANAGER', 'ADMIN'), true); ?>
<?php $can_disburse = in_array($role_code, array('CASHIER', 'ADMIN'), true); ?>

<form method="GET" action="<?php echo base_url('admin/topups'); ?>" class="d-flex gap-2 mb-3">
    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" class="form-control w-auto" placeholder="Loan A/C number or customer mobile">
    <button type="submit" class="btn btn-outline-secondary">Search</button>
</form>

<?php if ($search !== ''): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold">Search Results</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light"><tr><th>Loan A/C</th><th>Customer</th><th>Status</th><th>Sanctioned</th><th>Eligible Top-up</th><th class="text-end">Action</th></tr></thead>
            <tbody>
                <?php if (empty($matches)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No matching loan.</td></tr>
                <?php else: foreach ($matches as $loan): ?>
                    <tr>
                        <td><a href="<?php echo base_url('admin/loans/' . $loan['id']); ?>"><?php echo htmlspecialchars($loan['loan_account_number'] ?? 'Pending disbursement'); ?></a></td>
                        <td><?php echo htmlspecialchars($loan['customer_name'] ?? '—'); ?></td>
                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($loan['status']); ?></span></td>
                        <td>₹<?php echo number_format($loan['sanctioned_amount'], 2); ?></td>
                        <td>₹<?php echo number_format($loan['eligible_topup_amount'], 2); ?></td>
                        <td class="text-end">
                            <?php if ($can_add_jewellery && ! empty($loan['unpledged_items'])): ?>
                                <button type="button" class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#addJewelleryModal<?php echo (int) $loan['id']; ?>">Add Jewellery</button>
                            <?php endif; ?>
                            <?php if ($can_approve): ?>
                                <button type="button" class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target="#approveModal<?php echo (int) $loan['id']; ?>">Approve Top-up</button>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <?php if ($can_add_jewellery && ! empty($loan['unpledged_items'])): ?>
                    <div class="modal fade" id="addJewelleryModal<?php echo (int) $loan['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="<?php echo base_url('admin/topups/' . $loan['id'] . '/add-jewellery'); ?>">
                                    <div class="modal-header"><h5 class="modal-title">Add Jewellery — <?php echo htmlspecialchars($loan['loan_account_number'] ?? 'Loan #' . $loan['id']); ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                    <div class="modal-body">
                                        <?php foreach ($loan['unpledged_items'] as $item): ?>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="jewellery_item_ids[]" value="<?php echo (int) $item['id']; ?>" id="item<?php echo (int) $item['id']; ?>">
                                                <label class="form-check-label" for="item<?php echo (int) $item['id']; ?>"><?php echo htmlspecialchars($item['barcode']); ?> — <?php echo htmlspecialchars($item['purity_karat']); ?>K, ₹<?php echo number_format($item['eligible_amount'], 2); ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-dark">Add</button></div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($can_approve): ?>
                    <div class="modal fade" id="approveModal<?php echo (int) $loan['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="<?php echo base_url('admin/topups/' . $loan['id'] . '/approve'); ?>">
                                    <div class="modal-header"><h5 class="modal-title">Approve Top-up — <?php echo htmlspecialchars($loan['loan_account_number'] ?? 'Loan #' . $loan['id']); ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                    <div class="modal-body">
                                        <p class="text-muted small">Eligible top-up: ₹<?php echo number_format($loan['eligible_topup_amount'], 2); ?></p>
                                        <label class="form-label">Approved Amount</label>
                                        <input type="number" step="0.01" name="approved_amount" class="form-control" max="<?php echo htmlspecialchars($loan['eligible_topup_amount']); ?>" value="<?php echo htmlspecialchars($loan['eligible_topup_amount']); ?>" required>
                                    </div>
                                    <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-dark">Approve</button></div>
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

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold">Approved — Awaiting Disbursement</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead class="table-light"><tr><th>Loan A/C</th><th>Customer</th><th>Approved Amt.</th><th class="text-end">Action</th></tr></thead>
            <tbody>
                <?php if (empty($awaiting_disbursement)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-3">No top-ups awaiting disbursement.</td></tr>
                <?php else: foreach ($awaiting_disbursement as $t): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($t['loan_account_number'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($t['customer_name'] ?? '—'); ?></td>
                        <td>₹<?php echo number_format($t['approved_amount'], 2); ?></td>
                        <td class="text-end">
                            <?php if ($can_disburse): ?>
                                <form method="POST" action="<?php echo base_url('admin/topups/' . $t['loan_id'] . '/disburse'); ?>" class="d-inline" onsubmit="return confirm('Disburse this top-up?');">
                                    <button type="submit" class="btn btn-sm btn-dark">Disburse</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold">Disbursed Top-ups</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead class="table-light"><tr><th>Loan A/C</th><th>Customer</th><th>Approved Amt.</th></tr></thead>
            <tbody>
                <?php if (empty($history)): ?>
                    <tr><td colspan="3" class="text-center text-muted py-3">No disbursed top-ups yet.</td></tr>
                <?php else: foreach ($history as $t): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($t['loan_account_number'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($t['customer_name'] ?? '—'); ?></td>
                        <td>₹<?php echo number_format($t['approved_amount'], 2); ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
