<a href="<?php echo base_url('admin/loans'); ?>" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Back to Loans</a>
<a href="<?php echo base_url('admin/loans/' . $loan['id'] . '/receipt'); ?>" class="btn btn-sm btn-outline-dark mb-3" target="_blank"><i class="bi bi-receipt"></i> Pledge Receipt</a>
<?php if ($can_cancel): ?>
    <button type="button" class="btn btn-sm btn-outline-danger mb-3" data-bs-toggle="modal" data-bs-target="#cancelLoanModal"><i class="bi bi-x-circle"></i> Cancel Loan</button>
<?php endif; ?>

<?php $CI =& get_instance(); $error = $CI->session->flashdata('error'); ?>
<?php if (! empty($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">Loan Details</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4"><strong>A/C No.:</strong> <?php echo htmlspecialchars($loan['loan_account_number'] ?? 'Pending disbursement'); ?></div>
                    <div class="col-md-4"><strong>Status:</strong> <span class="badge bg-secondary"><?php echo htmlspecialchars($loan['status']); ?></span></div>
                    <div class="col-md-4"><strong>Customer:</strong> <?php echo htmlspecialchars($loan['customer_name'] ?? '—'); ?></div>
                    <div class="col-md-4"><strong>Branch:</strong> <?php echo htmlspecialchars($loan['branch_name'] ?? '—'); ?></div>
                    <div class="col-md-4"><strong>Product:</strong> <?php echo htmlspecialchars($loan['product_name'] ?? '—'); ?></div>
                    <div class="col-md-4"><strong>Interest Rate:</strong> <?php echo htmlspecialchars($loan['interest_rate_pct']); ?>%</div>
                    <div class="col-md-4"><strong>Eligible Amount:</strong> ₹<?php echo number_format($loan['eligible_amount'], 2); ?></div>
                    <div class="col-md-4"><strong>Sanctioned Amount:</strong> ₹<?php echo number_format($loan['sanctioned_amount'], 2); ?></div>
                    <div class="col-md-4"><strong>Net Disbursed:</strong> ₹<?php echo number_format((float) $loan['net_disbursed_amount'], 2); ?></div>
                    <div class="col-md-4"><strong>Loan Date:</strong> <?php echo ! empty($loan['loan_date']) ? htmlspecialchars(date('d-M-Y', strtotime($loan['loan_date']))) : '—'; ?></div>
                    <div class="col-md-4"><strong>Due Date:</strong> <?php echo ! empty($loan['due_date']) ? htmlspecialchars(date('d-M-Y', strtotime($loan['due_date']))) : '—'; ?></div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">Jewellery Items</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Barcode</th><th>Purity</th><th>Net Wt.</th><th>Eligible Amt.</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php if (empty($jewellery_items)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-3">No jewellery items.</td></tr>
                        <?php else: foreach ($jewellery_items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['barcode']); ?></td>
                                <td><?php echo htmlspecialchars($item['purity_karat']); ?>K</td>
                                <td><?php echo htmlspecialchars($item['net_weight']); ?>g</td>
                                <td>₹<?php echo number_format($item['eligible_amount'], 2); ?></td>
                                <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($item['status']); ?></span></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">Disbursements</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Mode</th><th>Amount</th><th>Reference</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php if (empty($disbursements)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">Not yet disbursed.</td></tr>
                        <?php else: foreach ($disbursements as $d): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($d['mode']); ?></td>
                                <td>₹<?php echo number_format($d['amount'], 2); ?></td>
                                <td><?php echo htmlspecialchars($d['reference_number'] ?? '—'); ?></td>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($d['status']); ?></span></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">Interest Collections</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Amount</th><th>Mode</th><th>Receipt No.</th></tr></thead>
                    <tbody>
                        <?php if (empty($interest_collections)): ?>
                            <tr><td colspan="3" class="text-center text-muted py-3">No interest collected yet.</td></tr>
                        <?php else: foreach ($interest_collections as $ic): ?>
                            <tr>
                                <td>₹<?php echo number_format($ic['amount'], 2); ?></td>
                                <td><?php echo htmlspecialchars($ic['mode']); ?></td>
                                <td><?php echo htmlspecialchars($ic['receipt_number'] ?? '—'); ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Documents</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Type</th><th>Uploaded</th><th></th></tr></thead>
                    <tbody>
                        <?php if (empty($documents)): ?>
                            <tr><td colspan="3" class="text-center text-muted py-3">No documents uploaded. An AGREEMENT document is required before disbursement.</td></tr>
                        <?php else: foreach ($documents as $doc): ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($doc['document_type']); ?></span></td>
                                <td><?php echo htmlspecialchars(date('d-M-Y', strtotime($doc['created_at']))); ?></td>
                                <td><a href="<?php echo base_url('admin/loans/document/' . $doc['id']); ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($can_upload_document): ?>
            <div class="card-footer bg-white">
                <form method="POST" action="<?php echo base_url('admin/loans/' . $loan['id'] . '/document'); ?>" enctype="multipart/form-data" class="row g-2">
                    <div class="col-md-4">
                        <select name="document_type" class="form-select form-select-sm">
                            <option value="AGREEMENT">Agreement</option>
                            <option value="SANCTION_LETTER">Sanction Letter</option>
                            <option value="JEWELLERY_PHOTO">Jewellery Photo</option>
                            <option value="OTHER">Other</option>
                        </select>
                    </div>
                    <div class="col-md-6"><input type="file" name="file" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png" required></div>
                    <div class="col-md-2"><button type="submit" class="btn btn-outline-dark btn-sm w-100">Upload</button></div>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">Approval Workflow</div>
            <div class="card-body">
                <?php if ($approval_workflow): ?>
                    <p class="mb-1"><strong>Current Stage:</strong> <?php echo htmlspecialchars($approval_workflow['current_stage']); ?></p>
                    <p class="mb-0"><strong>Status:</strong> <span class="badge bg-warning text-dark"><?php echo htmlspecialchars($approval_workflow['status']); ?></span></p>
                <?php else: ?>
                    <p class="text-muted mb-0">No workflow started.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Approval Log</div>
            <ul class="list-group list-group-flush">
                <?php if (empty($approval_logs)): ?>
                    <li class="list-group-item text-muted">No approval activity yet.</li>
                <?php else: foreach ($approval_logs as $log): ?>
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <span class="fw-semibold"><?php echo htmlspecialchars($log['stage']); ?></span>
                            <span class="badge bg-secondary"><?php echo htmlspecialchars($log['action']); ?></span>
                        </div>
                        <?php if (! empty($log['remarks'])): ?>
                            <div class="small text-muted mt-1"><?php echo htmlspecialchars($log['remarks']); ?></div>
                        <?php endif; ?>
                    </li>
                <?php endforeach; endif; ?>
            </ul>
        </div>
    </div>
</div>

<?php if ($can_cancel): ?>
<div class="modal fade" id="cancelLoanModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="POST" action="<?php echo base_url('admin/loans/' . $loan['id'] . '/cancel'); ?>">
            <div class="modal-header"><h5 class="modal-title">Cancel Loan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <p class="text-muted small">This cancels the loan before disbursement and releases its pledged jewellery items back to available. This cannot be undone.</p>
                <label class="form-label">Reason</label>
                <textarea name="remarks" class="form-control" rows="3" required></textarea>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-danger">Cancel Loan</button></div>
        </form>
    </div></div>
</div>
<?php endif; ?>
