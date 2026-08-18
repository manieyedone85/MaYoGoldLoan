<a href="<?php echo base_url('admin/customers'); ?>" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Back to Customers</a>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white fw-semibold"><?php echo htmlspecialchars($customer['name']); ?> (<?php echo htmlspecialchars($customer['customer_code']); ?>)</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4"><strong>Mobile:</strong> <?php echo htmlspecialchars($customer['mobile']); ?></div>
            <div class="col-md-4"><strong>Email:</strong> <?php echo htmlspecialchars($customer['email'] ?? '—'); ?></div>
            <div class="col-md-4"><strong>DOB:</strong> <?php echo ! empty($customer['dob']) ? htmlspecialchars(date('d-M-Y', strtotime($customer['dob']))) : '—'; ?></div>
            <div class="col-md-4"><strong>Aadhaar (last 4):</strong> <?php echo htmlspecialchars($customer['aadhaar_last4'] ?? '—'); ?></div>
            <div class="col-md-4"><strong>PAN:</strong> <?php echo htmlspecialchars($customer['pan_number'] ?? '—'); ?></div>
            <div class="col-md-4"><strong>KYC Status:</strong> <span class="badge bg-info text-dark"><?php echo htmlspecialchars($customer['kyc_status']); ?></span></div>
            <div class="col-md-4"><strong>Branch:</strong> <?php echo htmlspecialchars($customer['branch']['name'] ?? '—'); ?></div>
            <div class="col-md-4"><strong>Blacklisted:</strong> <?php echo $customer['is_blacklisted'] ? '<span class="badge bg-danger">Yes</span>' : 'No'; ?></div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold">Loans</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead class="table-light"><tr><th>A/C No.</th><th>Status</th><th>Sanctioned</th></tr></thead>
            <tbody>
                <?php if (empty($loans)): ?>
                    <tr><td colspan="3" class="text-muted text-center">No loans yet.</td></tr>
                <?php else: foreach ($loans as $loan): ?>
                    <tr>
                        <td><a href="<?php echo base_url('admin/loans/' . $loan['id']); ?>"><?php echo htmlspecialchars($loan['loan_account_number'] ?? 'Pending disbursement'); ?></a></td>
                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($loan['status']); ?></span></td>
                        <td>₹<?php echo number_format($loan['sanctioned_amount'], 2); ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card border-0 shadow-sm mt-3">
    <div class="card-header bg-white fw-semibold">KYC Documents</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead class="table-light"><tr><th>Type</th><th>Status</th><th>Uploaded</th><th></th></tr></thead>
            <tbody>
                <?php if (empty($kyc_documents)): ?>
                    <tr><td colspan="4" class="text-muted text-center py-3">No KYC documents uploaded.</td></tr>
                <?php else: foreach ($kyc_documents as $doc): ?>
                    <tr>
                        <td><?php echo (int) $doc['document_type_id']; ?></td>
                        <td>
                            <span class="badge <?php echo $doc['status'] === 'VERIFIED' ? 'bg-success' : ($doc['status'] === 'REJECTED' ? 'bg-danger' : 'bg-warning text-dark'); ?>"><?php echo htmlspecialchars($doc['status']); ?></span>
                            <?php if (! empty($doc['rejection_reason'])): ?><div class="small text-muted"><?php echo htmlspecialchars($doc['rejection_reason']); ?></div><?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars(date('d-M-Y', strtotime($doc['created_at']))); ?></td>
                        <td><a href="<?php echo base_url('admin/kyc/' . $doc['id'] . '/file'); ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white small text-muted">Full verify/reject queue: <a href="<?php echo base_url('admin/kyc'); ?>">KYC Verification</a></div>
</div>

<div class="row g-3 mt-1">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Aadhaar Verifications</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Method</th><th>Verified</th></tr></thead>
                    <tbody>
                        <?php if (empty($aadhaar_verifications)): ?>
                            <tr><td colspan="2" class="text-muted text-center py-3">No Aadhaar verifications.</td></tr>
                        <?php else: foreach ($aadhaar_verifications as $v): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($v['method']); ?></td>
                                <td><?php echo htmlspecialchars(date('d-M-Y H:i', strtotime($v['created_at']))); ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">PAN Verifications</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>PAN</th><th>Verified</th></tr></thead>
                    <tbody>
                        <?php if (empty($pan_verifications)): ?>
                            <tr><td colspan="2" class="text-muted text-center py-3">No PAN verifications.</td></tr>
                        <?php else: foreach ($pan_verifications as $v): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($v['pan_number'] ?? '—'); ?></td>
                                <td><?php echo htmlspecialchars(date('d-M-Y H:i', strtotime($v['created_at']))); ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
