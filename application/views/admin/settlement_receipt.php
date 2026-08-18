<a href="<?php echo base_url('admin/settlements'); ?>" class="btn btn-sm btn-outline-secondary mb-3 no-print"><i class="bi bi-arrow-left"></i> Back to Settlements</a>
<button type="button" class="btn btn-sm btn-dark mb-3 no-print" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>

<div class="card border-0 shadow-sm" style="max-width: 480px;">
    <div class="card-body">
        <div class="text-center mb-3">
            <h5 class="mb-0"><?php echo htmlspecialchars($closure['branch_name'] ?? 'Gold Loan Branch'); ?></h5>
            <div class="text-muted small">Loan Closure Receipt</div>
        </div>
        <hr>
        <div class="row g-2">
            <div class="col-6"><strong>Loan A/C No.:</strong></div>
            <div class="col-6"><?php echo htmlspecialchars($closure['loan_account_number'] ?? '—'); ?></div>
            <div class="col-6"><strong>Customer:</strong></div>
            <div class="col-6"><?php echo htmlspecialchars($closure['customer_name'] ?? '—'); ?></div>
            <div class="col-6"><strong>Mobile:</strong></div>
            <div class="col-6"><?php echo htmlspecialchars($closure['customer_mobile'] ?? '—'); ?></div>
            <div class="col-6"><strong>Closure Date:</strong></div>
            <div class="col-6"><?php echo htmlspecialchars(date('d-M-Y', strtotime($closure['closure_date']))); ?></div>
            <div class="col-6"><strong>Original Sanctioned Amt.:</strong></div>
            <div class="col-6">₹<?php echo number_format($closure['sanctioned_amount'], 2); ?></div>
        </div>
        <hr>
        <div class="text-center fs-5"><strong>Total Amount Collected: ₹<?php echo number_format($closure['total_amount_collected'], 2); ?></strong></div>
        <div class="alert alert-success text-center mt-3 mb-0">This loan has been fully settled and closed.<br>Jewellery release is processed separately once the release checklist is completed.</div>
        <div class="row g-4 mt-4 pt-4">
            <div class="col-6 text-center border-top pt-2">Customer Signature</div>
            <div class="col-6 text-center border-top pt-2">Authorized Signatory</div>
        </div>
    </div>
</div>
