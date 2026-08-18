<a href="<?php echo base_url('admin/interest-collections'); ?>" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Back to Interest Collections</a>

<div class="card border-0 shadow-sm" style="max-width: 420px;">
    <div class="card-body font-monospace">
        <div class="text-center fw-semibold"><?php echo htmlspecialchars($branch['name'] ?? 'Gold Loan Branch'); ?></div>
        <hr>
        <div>Receipt No: <?php echo htmlspecialchars($collection['receipt_number']); ?></div>
        <div>Date: <?php echo htmlspecialchars(date('d-M-Y H:i', strtotime($collection['created_at']))); ?></div>
        <div>Loan A/C: <?php echo htmlspecialchars($loan['loan_account_number'] ?? 'Pending disbursement'); ?></div>
        <div>Customer: <?php echo htmlspecialchars($customer['name'] ?? '-'); ?></div>
        <hr>
        <div>Amount: Rs. <?php echo number_format($collection['amount'], 2); ?></div>
        <div>Mode: <?php echo htmlspecialchars($collection['mode']); ?></div>
        <hr>
        <div class="text-center">Thank you</div>
    </div>
</div>
