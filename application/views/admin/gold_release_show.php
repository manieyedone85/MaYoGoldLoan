<a href="<?php echo base_url('admin/gold-releases'); ?>" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Back to Gold Release</a>

<?php $CI =& get_instance(); $error = $CI->session->flashdata('error'); ?>
<?php if (! empty($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white fw-semibold">Loan <?php echo htmlspecialchars($loan['loan_account_number'] ?? 'Pending disbursement'); ?> <span class="badge bg-secondary"><?php echo htmlspecialchars($loan['status']); ?></span></div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold">Jewellery Items</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light"><tr><th>Barcode</th><th>ID Proof</th><th>Signature</th><th>Photo</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
                <?php if (empty($items)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No jewellery items on this loan.</td></tr>
                <?php else: foreach ($items as $item): $release = $item['release']; ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['barcode']); ?></td>
                        <td><?php echo ! empty($release['id_proof_verified']) ? '<span class="badge bg-success">Done</span>' : '<span class="badge bg-secondary">Pending</span>'; ?></td>
                        <td><?php echo ! empty($release['signature_captured']) ? '<span class="badge bg-success">Done</span>' : '<span class="badge bg-secondary">Pending</span>'; ?></td>
                        <td><?php echo ! empty($release['photo_captured']) ? '<span class="badge bg-success">Done</span>' : '<span class="badge bg-secondary">Pending</span>'; ?></td>
                        <td><span class="badge <?php echo (! empty($release['status']) && $release['status'] === 'RELEASED') ? 'bg-success' : 'bg-warning text-dark'; ?>"><?php echo htmlspecialchars($release['status'] ?? 'NOT STARTED'); ?></span></td>
                        <td class="text-end">
                            <?php if (empty($release) || $release['status'] !== 'RELEASED'): ?>
                                <?php if (empty($release['id_proof_verified'])): ?>
                                    <button type="button" class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#verifyModal<?php echo (int) $item['id']; ?>">Verify ID</button>
                                <?php endif; ?>
                                <?php if (! empty($release) && empty($release['signature_captured'])): ?>
                                    <form method="POST" action="<?php echo base_url('admin/gold-releases/release/' . $release['id'] . '/signature'); ?>" class="d-inline">
                                        <button type="submit" class="btn btn-sm btn-outline-dark">Capture Signature</button>
                                    </form>
                                <?php endif; ?>
                                <?php if (! empty($release) && empty($release['photo_captured'])): ?>
                                    <form method="POST" action="<?php echo base_url('admin/gold-releases/release/' . $release['id'] . '/photo'); ?>" class="d-inline">
                                        <button type="submit" class="btn btn-sm btn-outline-dark">Capture Photo</button>
                                    </form>
                                <?php endif; ?>
                                <?php if ($can_complete && ! empty($release) && ! empty($release['id_proof_verified']) && ! empty($release['signature_captured']) && ! empty($release['photo_captured'])): ?>
                                    <form method="POST" action="<?php echo base_url('admin/gold-releases/release/' . $release['id'] . '/complete'); ?>" class="d-inline" onsubmit="return confirm('Release this jewellery item to the customer?');">
                                        <button type="submit" class="btn btn-sm btn-success">Complete Release</button>
                                    </form>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="<?php echo base_url('admin/gold-releases/release/' . $release['id'] . '/receipt'); ?>" class="btn btn-sm btn-outline-secondary" target="_blank"><i class="bi bi-receipt"></i> Receipt</a>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <div class="modal fade" id="verifyModal<?php echo (int) $item['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="<?php echo base_url('admin/gold-releases/' . $loan['id'] . '/verify-id'); ?>">
                                    <input type="hidden" name="jewellery_item_id" value="<?php echo (int) $item['id']; ?>">
                                    <div class="modal-header"><h5 class="modal-title">Verify ID — <?php echo htmlspecialchars($item['barcode']); ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                    <div class="modal-body">
                                        <label class="form-label">Released To (name)</label>
                                        <input type="text" name="released_to" class="form-control" required>
                                    </div>
                                    <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-dark">Verify</button></div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

