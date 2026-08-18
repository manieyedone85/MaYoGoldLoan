<?php $CI =& get_instance(); $error = $CI->session->flashdata('error'); ?>
<?php if (! empty($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold">Awaiting Disbursement</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light"><tr><th>Loan</th><th>Customer</th><th>Branch</th><th>Net Disbursed Amt.</th><th class="text-end">Action</th></tr></thead>
            <tbody>
                <?php if (empty($pending)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">No approved loans awaiting disbursement.</td></tr>
                <?php else: foreach ($pending as $loan): ?>
                    <tr>
                        <td><a href="<?php echo base_url('admin/loans/' . $loan['id']); ?>">#<?php echo (int) $loan['id']; ?></a></td>
                        <td><?php echo htmlspecialchars($loan['customer_name'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($loan['branch_name'] ?? '—'); ?></td>
                        <td>₹<?php echo number_format((float) $loan['net_disbursed_amount'], 2); ?></td>
                        <td class="text-end"><button type="button" class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target="#disburseModal<?php echo (int) $loan['id']; ?>">Disburse</button></td>
                    </tr>

                    <div class="modal fade" id="disburseModal<?php echo (int) $loan['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="<?php echo base_url('admin/disbursements/' . $loan['id'] . '/disburse'); ?>">
                                    <div class="modal-header"><h5 class="modal-title">Disburse Loan #<?php echo (int) $loan['id']; ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                    <div class="modal-body">
                                        <p class="mb-3">Net disbursed amount: <strong>₹<?php echo number_format((float) $loan['net_disbursed_amount'], 2); ?></strong></p>
                                        <div class="mb-3">
                                            <label class="form-label">Mode</label>
                                            <select name="mode" class="form-select" required>
                                                <option value="">Select mode</option>
                                                <?php foreach ($modes as $mode): ?>
                                                    <option value="<?php echo htmlspecialchars($mode['code']); ?>"><?php echo htmlspecialchars($mode['name']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="mb-1">
                                            <label class="form-label">Reference Number</label>
                                            <input type="text" name="reference_number" class="form-control">
                                        </div>
                                    </div>
                                    <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-dark">Disburse</button></div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold">Recent Disbursements</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead class="table-light"><tr><th>Loan A/C</th><th>Customer</th><th>Mode</th><th>Amount</th><th>Reference</th><th>Status</th><th></th></tr></thead>
            <tbody>
                <?php if (empty($history)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No disbursements yet.</td></tr>
                <?php else: foreach ($history as $d): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($d['loan_account_number'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($d['customer_name'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($d['mode_name'] ?? $d['mode_code'] ?? '—'); ?></td>
                        <td>₹<?php echo number_format($d['amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars($d['reference_number'] ?? '—'); ?></td>
                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($d['status']); ?></span></td>
                        <td><a href="<?php echo base_url('admin/disbursements/' . $d['id'] . '/receipt'); ?>" class="btn btn-sm btn-outline-secondary" target="_blank"><i class="bi bi-receipt"></i></a></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
