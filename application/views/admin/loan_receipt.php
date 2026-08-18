<a href="<?php echo base_url('admin/loans/' . $loan['id']); ?>" class="btn btn-sm btn-outline-secondary mb-3 no-print"><i class="bi bi-arrow-left"></i> Back to Loan</a>
<button type="button" class="btn btn-sm btn-dark mb-3 no-print" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>

<div class="card border-0 shadow-sm" style="max-width: 720px;">
    <div class="card-body">
        <div class="text-center mb-3">
            <h5 class="mb-0"><?php echo htmlspecialchars($loan['branch_name'] ?? 'Gold Loan Branch'); ?></h5>
            <div class="text-muted small">Gold Loan Pledge Receipt</div>
        </div>
        <hr>
        <div class="row g-2 mb-3">
            <div class="col-6"><strong>Loan A/C No.:</strong> <?php echo htmlspecialchars($loan['loan_account_number'] ?? 'Pending disbursement'); ?></div>
            <div class="col-6"><strong>Date:</strong> <?php echo ! empty($loan['loan_date']) ? htmlspecialchars(date('d-M-Y', strtotime($loan['loan_date']))) : '—'; ?></div>
            <div class="col-6"><strong>Customer:</strong> <?php echo htmlspecialchars($loan['customer_name'] ?? '—'); ?></div>
            <div class="col-6"><strong>Mobile:</strong> <?php echo htmlspecialchars($loan['customer_mobile'] ?? '—'); ?></div>
            <div class="col-12"><strong>Address:</strong>
                <?php if ($address): ?>
                    <?php echo htmlspecialchars(implode(', ', array_filter(array($address['line1'], $address['city'], $address['state'], $address['pincode'])))); ?>
                <?php else: ?>—<?php endif; ?>
            </div>
        </div>

        <div class="fw-semibold mb-2">Jewellery Pledged</div>
        <table class="table table-sm table-bordered mb-3">
            <thead class="table-light"><tr><th>Barcode</th><th>Category</th><th>Purity</th><th>Net Wt.</th><th class="text-end">Eligible Amt.</th></tr></thead>
            <tbody>
                <?php if (empty($items)): ?>
                    <tr><td colspan="5" class="text-center text-muted">No jewellery items.</td></tr>
                <?php else: foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['barcode']); ?></td>
                        <td><?php echo htmlspecialchars($item['category_name'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($item['purity_karat']); ?>K</td>
                        <td><?php echo htmlspecialchars($item['net_weight']); ?>g</td>
                        <td class="text-end">₹<?php echo number_format($item['eligible_amount'], 2); ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>

        <div class="row g-2 mb-3">
            <div class="col-6"><strong>Eligible Amount:</strong> ₹<?php echo number_format($loan['eligible_amount'], 2); ?></div>
            <div class="col-6"><strong>Sanctioned Amount:</strong> ₹<?php echo number_format($loan['sanctioned_amount'], 2); ?></div>
            <div class="col-6"><strong>Interest Rate:</strong> <?php echo htmlspecialchars($loan['interest_rate_pct']); ?>% p.a.</div>
            <div class="col-6"><strong>Due Date:</strong> <?php echo ! empty($loan['due_date']) ? htmlspecialchars(date('d-M-Y', strtotime($loan['due_date']))) : '—'; ?></div>
        </div>

        <?php if (! empty($charges)): ?>
        <div class="fw-semibold mb-2">Charges</div>
        <table class="table table-sm table-bordered mb-3">
            <tbody>
                <?php foreach ($charges as $charge): ?>
                    <tr><td><?php echo htmlspecialchars($charge['charge_type']); ?></td><td class="text-end">₹<?php echo number_format($charge['amount'], 2); ?></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <div class="row g-2 mb-4">
            <div class="col-12"><strong>Net Disbursed Amount:</strong> ₹<?php echo number_format((float) $loan['net_disbursed_amount'], 2); ?></div>
        </div>

        <div class="row g-4 mt-5 pt-4">
            <div class="col-6 text-center border-top pt-2">Customer Signature</div>
            <div class="col-6 text-center border-top pt-2">Authorized Signatory</div>
        </div>
    </div>
</div>
