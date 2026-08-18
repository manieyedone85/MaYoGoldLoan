<?php $CI =& get_instance(); $error = $CI->session->flashdata('error'); ?>
<?php if (! empty($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="GET" action="<?php echo base_url('admin/loan-approvals'); ?>" class="d-flex gap-2 mb-3">
    <select name="stage" class="form-select w-auto" onchange="this.form.submit()">
        <?php foreach ($stages as $s): ?>
            <option value="<?php echo $s; ?>" <?php echo $stage === $s ? 'selected' : ''; ?>><?php echo $s; ?> stage</option>
        <?php endforeach; ?>
    </select>
    <noscript><button type="submit" class="btn btn-outline-secondary">Filter</button></noscript>
</form>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Loan</th>
                    <th>Customer</th>
                    <th>Branch</th>
                    <th>Sanctioned Amt.</th>
                    <th>Submitted</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pending)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No loans pending approval at this stage.</td></tr>
                <?php else: foreach ($pending as $loan): ?>
                    <tr>
                        <td><a href="<?php echo base_url('admin/loans/' . $loan['id']); ?>">#<?php echo (int) $loan['id']; ?></a></td>
                        <td><?php echo htmlspecialchars($loan['customer_name'] ?? '—'); ?> <span class="text-muted small"><?php echo htmlspecialchars($loan['customer_mobile'] ?? ''); ?></span></td>
                        <td><?php echo htmlspecialchars($loan['branch_name'] ?? '—'); ?></td>
                        <td>₹<?php echo number_format($loan['sanctioned_amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars(date('d-M-Y', strtotime($loan['created_at']))); ?></td>
                        <td class="text-end">
                            <form method="POST" action="<?php echo base_url('admin/loan-approvals/' . $loan['id'] . '/approve'); ?>" class="d-inline" onsubmit="return confirm('Approve loan #<?php echo (int) $loan['id']; ?>?');">
                                <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-check-lg"></i> Approve</button>
                            </form>
                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal<?php echo (int) $loan['id']; ?>"><i class="bi bi-x-lg"></i> Reject</button>
                            <?php if ($can_override): ?>
                                <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#overrideModal<?php echo (int) $loan['id']; ?>"><i class="bi bi-shield-exclamation"></i> Override</button>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <div class="modal fade" id="rejectModal<?php echo (int) $loan['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="<?php echo base_url('admin/loan-approvals/' . $loan['id'] . '/reject'); ?>">
                                    <div class="modal-header"><h5 class="modal-title">Reject Loan #<?php echo (int) $loan['id']; ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                    <div class="modal-body">
                                        <label class="form-label">Remarks (required)</label>
                                        <textarea name="remarks" class="form-control" rows="3" required></textarea>
                                    </div>
                                    <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger">Reject</button></div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <?php if ($can_override): ?>
                    <div class="modal fade" id="overrideModal<?php echo (int) $loan['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="<?php echo base_url('admin/loan-approvals/' . $loan['id'] . '/override'); ?>">
                                    <div class="modal-header"><h5 class="modal-title">Override Approval — Loan #<?php echo (int) $loan['id']; ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                    <div class="modal-body">
                                        <div class="alert alert-warning small">This forces the loan to APPROVED regardless of the current workflow stage.</div>
                                        <label class="form-label">Remarks (required)</label>
                                        <textarea name="remarks" class="form-control" rows="3" required></textarea>
                                    </div>
                                    <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-warning">Override</button></div>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
