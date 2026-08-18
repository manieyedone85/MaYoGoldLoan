<a href="<?php echo base_url('admin/disbursements'); ?>" class="btn btn-sm btn-outline-secondary mb-3 no-print"><i class="bi bi-arrow-left"></i> Back to Disbursements</a>
<button type="button" class="btn btn-sm btn-dark mb-3 no-print" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>

<div class="card border-0 shadow-sm" style="max-width: 480px;">
    <div class="card-body">
        <div class="text-center mb-3">
            <h5 class="mb-0"><?php echo htmlspecialchars($disbursement['branch_name'] ?? 'Gold Loan Branch'); ?></h5>
            <div class="text-muted small">Disbursement Receipt</div>
        </div>
        <hr>
        <div class="row g-2">
            <div class="col-6"><strong>Loan A/C No.:</strong></div>
            <div class="col-6"><?php echo htmlspecialchars($disbursement['loan_account_number'] ?? '—'); ?></div>
            <div class="col-6"><strong>Customer:</strong></div>
            <div class="col-6"><?php echo htmlspecialchars($disbursement['customer_name'] ?? '—'); ?></div>
            <div class="col-6"><strong>Mobile:</strong></div>
            <div class="col-6"><?php echo htmlspecialchars($disbursement['customer_mobile'] ?? '—'); ?></div>
            <div class="col-6"><strong>Disbursed Date:</strong></div>
            <div class="col-6"><?php echo htmlspecialchars(date('d-M-Y H:i', strtotime($disbursement['created_at']))); ?></div>
            <div class="col-6"><strong>Mode:</strong></div>
            <div class="col-6"><?php echo htmlspecialchars($disbursement['mode_name'] ?? $disbursement['mode_code'] ?? '—'); ?></div>
            <div class="col-6"><strong>Reference No.:</strong></div>
            <div class="col-6"><?php echo htmlspecialchars($disbursement['reference_number'] ?? '—'); ?></div>
            <div class="col-6"><strong>Status:</strong></div>
            <div class="col-6"><span class="badge bg-success"><?php echo htmlspecialchars($disbursement['status']); ?></span></div>
        </div>
        <hr>
        <div class="text-center fs-5"><strong>Amount: ₹<?php echo number_format($disbursement['amount'], 2); ?></strong></div>
        <div class="row g-4 mt-5 pt-4">
            <div class="col-6 text-center border-top pt-2">Customer Signature</div>
            <div class="col-6 text-center border-top pt-2">Cashier Signature</div>
        </div>
    </div>
</div>
