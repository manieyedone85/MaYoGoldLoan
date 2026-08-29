<?php $CI =& get_instance(); $error = $CI->session->flashdata('error'); ?>
<?php if (! empty($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<a href="<?php echo base_url('admin/customers'); ?>" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Back to Customers</a>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-semibold"><?php echo htmlspecialchars($customer['name']); ?> (<?php echo htmlspecialchars($customer['customer_code']); ?>)</span>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editDetailsModal"><i class="bi bi-pencil"></i> Edit Details</button>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <strong>Photo:</strong>
                <div class="d-flex align-items-center gap-2 mt-1">
                    <?php if (! empty($customer['photo_path'])): ?>
                        <a href="<?php echo base_url('admin/customers/' . $customer['id'] . '/photo'); ?>" target="_blank">
                            <img src="<?php echo base_url('admin/customers/' . $customer['id'] . '/photo'); ?>" alt="Customer photo" class="rounded" style="height:48px;width:48px;object-fit:cover;">
                        </a>
                    <?php else: ?>
                        <div class="border rounded d-flex align-items-center justify-content-center text-muted small" style="height:48px;width:48px;">—</div>
                    <?php endif; ?>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#updatePhotoModal">
                        <i class="bi bi-camera"></i> <?php echo ! empty($customer['photo_path']) ? 'Update' : 'Upload'; ?>
                    </button>
                </div>
            </div>
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
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Aadhaar Verifications</span>
                <button type="button" class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target="#aadhaarVerifyModal"><i class="bi bi-plus-lg"></i> Verify</button>
            </div>
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
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold">PAN Verifications</span>
                <button type="button" class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target="#panVerifyModal"><i class="bi bi-plus-lg"></i> Verify</button>
            </div>
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

<div class="card border-0 shadow-sm mt-3">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Nominees</span>
        <button type="button" class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target="#addNomineeModal"><i class="bi bi-plus-lg"></i> Add Nominee</button>
    </div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead class="table-light"><tr><th>Name</th><th>Relation</th><th>Mobile</th><th>ID Proof</th></tr></thead>
            <tbody>
                <?php if (empty($nominees)): ?>
                    <tr><td colspan="4" class="text-muted text-center py-3">No nominees added.</td></tr>
                <?php else: foreach ($nominees as $n): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($n['name']); ?></td>
                        <td><?php echo htmlspecialchars($n['relation']); ?></td>
                        <td><?php echo htmlspecialchars($n['mobile'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars(trim(($n['id_proof_type'] ?? '') . ' ' . ($n['id_proof_number'] ?? '')) ?: '—'); ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="editDetailsModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="POST" action="<?php echo base_url('admin/customers/' . $customer['id'] . '/details'); ?>">
            <div class="modal-header"><h5 class="modal-title">Edit Customer Details</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($customer['email'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">PAN Number</label>
                    <input type="text" name="pan_number" class="form-control text-uppercase" maxlength="10" pattern="[A-Za-z]{5}[0-9]{4}[A-Za-z]{1}" value="<?php echo htmlspecialchars($customer['pan_number'] ?? ''); ?>" placeholder="ABCDE1234F">
                </div>
                <div class="mb-1">
                    <label class="form-label">Aadhaar Number</label>
                    <input type="text" name="aadhaar_number" class="form-control" maxlength="12" pattern="\d{12}" placeholder="Leave blank to keep unchanged">
                    <div class="form-text">Current on file: last 4 digits <?php echo htmlspecialchars($customer['aadhaar_last4'] ?? '—'); ?>. Only the last 4 digits and a hash are stored -- the full number is never persisted.</div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-dark">Save</button></div>
        </form>
    </div></div>
</div>

<div class="modal fade" id="updatePhotoModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="POST" action="<?php echo base_url('admin/customers/' . $customer['id'] . '/photo'); ?>" enctype="multipart/form-data">
            <div class="modal-header"><h5 class="modal-title">Update Customer Photo</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-1">
                    <label class="form-label">Photo</label>
                    <div class="d-flex gap-2">
                        <input type="file" name="photo" id="updatePhotoFileInput" class="form-control" accept=".jpg,.jpeg,.png,.webp" required>
                        <button type="button" class="btn btn-outline-secondary text-nowrap" data-capture-target="updatePhotoFileInput" data-capture-parent-modal="updatePhotoModal"><i class="bi bi-camera"></i> Capture</button>
                    </div>
                    <div class="form-text">JPG, JPEG, PNG, or WEBP. Max 5MB. Choose a file or capture one with your camera.</div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-dark">Upload</button></div>
        </form>
    </div></div>
</div>

<div class="modal fade" id="addNomineeModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="POST" action="<?php echo base_url('admin/customers/' . $customer['id'] . '/nominee'); ?>">
            <div class="modal-header"><h5 class="modal-title">Add Nominee</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" maxlength="150" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Relation</label>
                    <input type="text" name="relation" class="form-control" maxlength="50" placeholder="e.g. Spouse, Son, Daughter" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mobile (optional)</label>
                    <input type="text" name="mobile" class="form-control" maxlength="10" pattern="\d{10}">
                </div>
                <div class="mb-3">
                    <label class="form-label">ID Proof Type (optional)</label>
                    <input type="text" name="id_proof_type" class="form-control" placeholder="e.g. AADHAAR, PAN">
                </div>
                <div class="mb-1">
                    <label class="form-label">ID Proof Number (optional)</label>
                    <input type="text" name="id_proof_number" class="form-control">
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-dark">Add</button></div>
        </form>
    </div></div>
</div>

<div class="modal fade" id="aadhaarVerifyModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="POST" action="<?php echo base_url('admin/customers/' . $customer['id'] . '/aadhaar-verify'); ?>">
            <div class="modal-header"><h5 class="modal-title">Aadhaar Verification (QR Scan)</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Aadhaar Number</label>
                    <input type="text" name="aadhaar_number" class="form-control" maxlength="12" pattern="\d{12}" placeholder="12-digit Aadhaar number" required>
                    <div class="form-text">Only the last 4 digits and a hash are stored -- the full number is never persisted.</div>
                </div>
                <div class="mb-1">
                    <label class="form-label">UIDAI Reference ID (optional)</label>
                    <input type="text" name="uidai_reference_id" class="form-control">
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-dark">Verify</button></div>
        </form>
    </div></div>
</div>

<div class="modal fade" id="panVerifyModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="POST" action="<?php echo base_url('admin/customers/' . $customer['id'] . '/pan-verify'); ?>">
            <div class="modal-header"><h5 class="modal-title">PAN Verification</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <label class="form-label">PAN Number</label>
                <input type="text" name="pan_number" class="form-control text-uppercase" maxlength="10" pattern="[A-Za-z]{5}[0-9]{4}[A-Za-z]{1}" placeholder="ABCDE1234F" required>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-dark">Verify</button></div>
        </form>
    </div></div>
</div>
