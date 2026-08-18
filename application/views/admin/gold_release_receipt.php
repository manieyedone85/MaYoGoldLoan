<a href="<?php echo base_url('admin/gold-releases/' . $release['loan_id']); ?>" class="btn btn-sm btn-outline-secondary mb-3 no-print"><i class="bi bi-arrow-left"></i> Back to Gold Release</a>
<button type="button" class="btn btn-sm btn-dark mb-3 no-print" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>

<div class="card border-0 shadow-sm" style="max-width: 480px;">
    <div class="card-body">
        <div class="text-center mb-3">
            <h5 class="mb-0"><?php echo htmlspecialchars($release['branch_name'] ?? 'Gold Loan Branch'); ?></h5>
            <div class="text-muted small">Jewellery Release Receipt</div>
        </div>
        <hr>
        <div class="row g-2">
            <div class="col-6"><strong>Loan A/C No.:</strong></div>
            <div class="col-6"><?php echo htmlspecialchars($release['loan_account_number'] ?? '—'); ?></div>
            <div class="col-6"><strong>Released To:</strong></div>
            <div class="col-6"><?php echo htmlspecialchars($release['released_to']); ?></div>
            <div class="col-6"><strong>Item Barcode:</strong></div>
            <div class="col-6"><?php echo htmlspecialchars($release['barcode'] ?? '—'); ?></div>
            <div class="col-6"><strong>Category:</strong></div>
            <div class="col-6"><?php echo htmlspecialchars($release['category_name'] ?? '—'); ?></div>
            <div class="col-6"><strong>Purity / Net Wt.:</strong></div>
            <div class="col-6"><?php echo htmlspecialchars($release['purity_karat'] ?? '—'); ?>K / <?php echo htmlspecialchars($release['net_weight'] ?? '—'); ?>g</div>
            <div class="col-6"><strong>Released Date:</strong></div>
            <div class="col-6"><?php echo ! empty($release['released_at']) ? htmlspecialchars(date('d-M-Y H:i', strtotime($release['released_at']))) : '—'; ?></div>
        </div>
        <hr>
        <div class="alert alert-success text-center mb-0">ID proof verified, signature captured, and photo captured. Item released to the above person.</div>
        <div class="row g-4 mt-4 pt-4">
            <div class="col-6 text-center border-top pt-2">Received By (Customer)</div>
            <div class="col-6 text-center border-top pt-2">Released By (Branch Manager)</div>
        </div>
    </div>
</div>
