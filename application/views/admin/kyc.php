<?php $CI =& get_instance(); $error = $CI->session->flashdata('error'); ?>
<?php if (! empty($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="d-flex justify-content-between mb-3">
    <form method="GET" action="<?php echo base_url('admin/kyc'); ?>" class="d-flex gap-2">
        <select name="status" class="form-select w-auto" onchange="this.form.submit()">
            <?php foreach (array('PENDING', 'VERIFIED', 'REJECTED', 'ALL') as $s): ?>
                <option value="<?php echo $s; ?>" <?php echo $status === $s ? 'selected' : ''; ?>><?php echo ucfirst(strtolower($s)); ?></option>
            <?php endforeach; ?>
        </select>
    </form>
    <?php if ($can_upload): ?>
        <button type="button" class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#uploadModal"><i class="bi bi-upload"></i> Upload Document</button>
    <?php endif; ?>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light"><tr><th>Customer</th><th>Document Type</th><th>Status</th><th>Uploaded</th><th class="text-end">Action</th></tr></thead>
            <tbody>
                <?php if (empty($documents)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">No KYC documents found.</td></tr>
                <?php else: foreach ($documents as $doc): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($doc['customer_name'] ?? '—'); ?> <span class="text-muted small"><?php echo htmlspecialchars($doc['customer_mobile'] ?? ''); ?></span></td>
                        <td><?php echo htmlspecialchars($doc['document_type_name'] ?? '—'); ?></td>
                        <td>
                            <span class="badge <?php echo $doc['status'] === 'VERIFIED' ? 'bg-success' : ($doc['status'] === 'REJECTED' ? 'bg-danger' : 'bg-warning text-dark'); ?>"><?php echo htmlspecialchars($doc['status']); ?></span>
                            <?php if (! empty($doc['rejection_reason'])): ?><div class="small text-muted"><?php echo htmlspecialchars($doc['rejection_reason']); ?></div><?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars(date('d-M-Y', strtotime($doc['created_at']))); ?></td>
                        <td class="text-end">
                            <a href="<?php echo base_url('admin/kyc/' . $doc['id'] . '/file'); ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                            <?php if ($can_verify && $doc['status'] === 'PENDING'): ?>
                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#verifyModal<?php echo (int) $doc['id']; ?>"><i class="bi bi-check-lg"></i></button>
                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal<?php echo (int) $doc['id']; ?>"><i class="bi bi-x-lg"></i></button>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <?php if ($can_verify): ?>
                    <div class="modal fade" id="verifyModal<?php echo (int) $doc['id']; ?>" tabindex="-1">
                        <div class="modal-dialog"><div class="modal-content">
                            <form method="POST" action="<?php echo base_url('admin/kyc/' . $doc['id'] . '/verify'); ?>">
                                <input type="hidden" name="decision" value="VERIFIED">
                                <div class="modal-header"><h5 class="modal-title">Verify KYC Document</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                <div class="modal-body">Mark this document as verified?</div>
                                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-success">Verify</button></div>
                            </form>
                        </div></div>
                    </div>
                    <div class="modal fade" id="rejectModal<?php echo (int) $doc['id']; ?>" tabindex="-1">
                        <div class="modal-dialog"><div class="modal-content">
                            <form method="POST" action="<?php echo base_url('admin/kyc/' . $doc['id'] . '/verify'); ?>">
                                <input type="hidden" name="decision" value="REJECTED">
                                <div class="modal-header"><h5 class="modal-title">Reject KYC Document</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                <div class="modal-body">
                                    <label class="form-label">Reason (required)</label>
                                    <textarea name="reason" class="form-control" rows="2" required></textarea>
                                </div>
                                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger">Reject</button></div>
                            </form>
                        </div></div>
                    </div>
                    <?php endif; ?>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($can_upload): ?>
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="POST" action="<?php echo base_url('admin/kyc/upload'); ?>" enctype="multipart/form-data">
            <div class="modal-header"><h5 class="modal-title">Upload KYC Document</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Customer Mobile</label><input type="text" name="customer_mobile" class="form-control" required></div>
                <div class="mb-3">
                    <label class="form-label">Document Type</label>
                    <select name="document_type_id" class="form-select" required>
                        <?php foreach ($document_types as $type): ?>
                            <option value="<?php echo (int) $type['id']; ?>"><?php echo htmlspecialchars($type['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-1"><label class="form-label">File</label><input type="file" name="file" class="form-control" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf" required></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-dark">Upload</button></div>
        </form>
    </div></div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
