<?php $CI =& get_instance(); $error = $CI->session->flashdata('error'); ?>
<?php if (! empty($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="d-flex justify-content-between mb-3">
    <form method="GET" action="<?php echo base_url('admin/accounting'); ?>" class="d-flex gap-2">
        <input type="text" name="customer_mobile" value="<?php echo htmlspecialchars($customer_mobile); ?>" class="form-control w-auto" placeholder="Customer mobile — view ledger">
        <button type="submit" class="btn btn-outline-secondary">Search Ledger</button>
    </form>
    <button type="button" class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#voucherModal"><i class="bi bi-plus-lg"></i> New Voucher</button>
</div>

<?php if ($customer_mobile !== ''): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold">Customer Ledger <?php echo $ledger_customer ? '— ' . htmlspecialchars($ledger_customer['name']) : ''; ?></div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead class="table-light"><tr><th>Date</th><th>Description</th><th>Debit</th><th>Credit</th><th>Balance</th></tr></thead>
            <tbody>
                <?php if (! $ledger_customer): ?>
                    <tr><td colspan="5" class="text-center text-muted py-3">No customer found with that mobile number.</td></tr>
                <?php elseif (empty($ledger)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-3">No ledger entries.</td></tr>
                <?php else: $running = 0; foreach ($ledger as $l): $running += (float) $l['debit'] - (float) $l['credit']; ?>
                    <tr>
                        <td><?php echo htmlspecialchars(date('d-M-Y', strtotime($l['created_at']))); ?></td>
                        <td><?php echo htmlspecialchars($l['particulars'] ?? '—'); ?></td>
                        <td>₹<?php echo number_format($l['debit'], 2); ?></td>
                        <td>₹<?php echo number_format($l['credit'], 2); ?></td>
                        <td>₹<?php echo number_format($running, 2); ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold">Vouchers</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light"><tr><th>Voucher No.</th><th>Branch</th><th>Type</th><th>Date</th><th>Lines</th></tr></thead>
            <tbody>
                <?php if (empty($vouchers)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">No vouchers recorded yet.</td></tr>
                <?php else: foreach ($vouchers as $v): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($v['voucher_number']); ?></td>
                        <td><?php echo htmlspecialchars($v['branch_name'] ?? '—'); ?></td>
                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($v['type']); ?></span></td>
                        <td><?php echo htmlspecialchars(date('d-M-Y', strtotime($v['voucher_date']))); ?></td>
                        <td class="small text-muted">
                            <?php foreach ($v['details'] as $d): ?>
                                GL#<?php echo (int) $d['gl_account_id']; ?>: Dr ₹<?php echo number_format($d['debit'], 2); ?> / Cr ₹<?php echo number_format($d['credit'], 2); ?><br>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="voucherModal" tabindex="-1">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <form method="POST" action="<?php echo base_url('admin/accounting/voucher'); ?>">
            <div class="modal-header"><h5 class="modal-title">New Voucher</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Branch</label>
                        <select name="branch_id" class="form-select" required>
                            <?php foreach ($branches as $b): ?>
                                <option value="<?php echo (int) $b['id']; ?>"><?php echo htmlspecialchars($b['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select" required>
                            <option value="RECEIPT">Receipt</option>
                            <option value="PAYMENT">Payment</option>
                            <option value="JOURNAL">Journal</option>
                            <option value="CONTRA">Contra</option>
                        </select>
                    </div>
                    <div class="col-md-4"><label class="form-label">Voucher Date</label><input type="date" name="voucher_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required></div>
                </div>

                <p class="small text-muted mb-1">Lines (at least 2; total debit must equal total credit):</p>
                <?php for ($i = 0; $i < 4; $i++): ?>
                <div class="row g-2 mb-2">
                    <div class="col-md-6">
                        <select name="gl_account_id[]" class="form-select form-select-sm">
                            <option value="">— GL Account —</option>
                            <?php foreach ($gl_accounts as $gl): ?>
                                <option value="<?php echo (int) $gl['id']; ?>"><?php echo htmlspecialchars($gl['code'] . ' — ' . $gl['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3"><input type="number" step="0.01" name="debit[]" class="form-control form-control-sm" placeholder="Debit"></div>
                    <div class="col-md-3"><input type="number" step="0.01" name="credit[]" class="form-control form-control-sm" placeholder="Credit"></div>
                </div>
                <?php endfor; ?>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-dark">Save Voucher</button></div>
        </form>
    </div></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
