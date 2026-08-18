<a href="<?php echo base_url('admin/jewellery-items'); ?>" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Back to Jewellery Items</a>

<?php $CI =& get_instance(); $error = $CI->session->flashdata('error'); ?>
<?php if (! empty($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between">
                <span><?php echo htmlspecialchars($item['barcode']); ?></span>
                <span class="badge bg-info text-dark"><?php echo htmlspecialchars($item['status']); ?></span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4"><strong>Customer:</strong> <?php echo htmlspecialchars($customer['name'] ?? '—'); ?></div>
                    <div class="col-md-4"><strong>Category:</strong> <?php echo htmlspecialchars($item['category_id']); ?></div>
                    <div class="col-md-4"><strong>Purity:</strong> <?php echo htmlspecialchars($item['purity_karat']); ?>K</div>
                    <div class="col-md-4"><strong>Gross Weight:</strong> <?php echo htmlspecialchars($item['gross_weight']); ?>g</div>
                    <div class="col-md-4"><strong>Stone Weight:</strong> <?php echo htmlspecialchars($item['stone_weight']); ?>g</div>
                    <div class="col-md-4"><strong>Net Weight:</strong> <?php echo htmlspecialchars($item['net_weight']); ?>g</div>
                    <div class="col-md-4"><strong>Applied Rate:</strong> ₹<?php echo number_format($item['applied_rate'], 2); ?>/g</div>
                    <div class="col-md-4"><strong>Eligible %:</strong> <?php echo number_format($item['eligible_percentage'], 2); ?>%</div>
                    <div class="col-md-4"><strong>Eligible Amount:</strong> ₹<?php echo number_format($item['eligible_amount'], 2); ?></div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">Valuation History</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Date</th><th>Gross</th><th>Stone</th><th>Rate</th><th>Eligible %</th><th>Eligible Amt.</th></tr></thead>
                    <tbody>
                        <?php if (empty($history)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-3">No valuation history.</td></tr>
                        <?php else: foreach ($history as $h): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(date('d-M-Y H:i', strtotime($h['created_at']))); ?></td>
                                <td><?php echo htmlspecialchars($h['gross_weight']); ?>g</td>
                                <td><?php echo htmlspecialchars($h['stone_weight']); ?>g</td>
                                <td>₹<?php echo number_format($h['applied_rate'], 2); ?></td>
                                <td><?php echo number_format($h['eligible_percentage'], 2); ?>%</td>
                                <td>₹<?php echo number_format($h['eligible_amount'], 2); ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Images</div>
            <div class="card-body d-flex flex-wrap gap-3">
                <?php if (empty($images)): ?>
                    <p class="text-muted mb-0">No images uploaded.</p>
                <?php else: foreach ($images as $img): ?>
                    <a href="<?php echo base_url('admin/jewellery-items/image/' . $img['id']); ?>" target="_blank">
                        <img src="<?php echo base_url('admin/jewellery-items/image/' . $img['id']); ?>" style="width:120px;height:120px;object-fit:cover;" class="rounded border">
                    </a>
                <?php endforeach; endif; ?>
            </div>
            <div class="card-footer bg-white">
                <form method="POST" action="<?php echo base_url('admin/jewellery-items/' . $item['id'] . '/image'); ?>" enctype="multipart/form-data" class="d-flex gap-2">
                    <input type="file" name="image" class="form-control" accept="image/*" required>
                    <button type="submit" class="btn btn-outline-dark">Upload</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <?php if ($can_evaluate && in_array($item['status'], array('EVALUATED', 'PLEDGED'), true)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Re-evaluate</div>
            <div class="card-body">
                <form method="POST" action="<?php echo base_url('admin/jewellery-items/' . $item['id'] . '/re-evaluate'); ?>">
                    <div class="mb-2"><label class="form-label small">Gross Weight (g)</label><input type="number" step="0.001" name="gross_weight" class="form-control form-control-sm" value="<?php echo htmlspecialchars($item['gross_weight']); ?>"></div>
                    <div class="mb-2"><label class="form-label small">Stone Weight (g)</label><input type="number" step="0.001" name="stone_weight" class="form-control form-control-sm" value="<?php echo htmlspecialchars($item['stone_weight']); ?>"></div>
                    <div class="mb-3"><label class="form-label small">Purity (Karat)</label><input type="text" name="purity_karat" class="form-control form-control-sm" value="<?php echo htmlspecialchars($item['purity_karat']); ?>"></div>
                    <button type="submit" class="btn btn-dark btn-sm w-100">Re-evaluate at Current Rate</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
