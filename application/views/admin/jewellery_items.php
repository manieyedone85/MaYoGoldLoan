<?php $CI =& get_instance(); $error = $CI->session->flashdata('error'); ?>
<?php if (! empty($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="d-flex justify-content-between mb-3">
    <form method="GET" action="<?php echo base_url('admin/jewellery-items'); ?>" class="d-flex gap-2">
        <input type="text" name="search" value="<?php echo htmlspecialchars($filters['search']); ?>" class="form-control w-auto" placeholder="Search barcode / name / mobile">
        <select name="status" class="form-select w-auto" onchange="this.form.submit()">
            <option value="">All statuses</option>
            <?php foreach (array('EVALUATED', 'PLEDGED', 'RELEASED', 'AUCTIONED') as $s): ?>
                <option value="<?php echo $s; ?>" <?php echo $status === $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-outline-secondary">Filter</button>
    </form>
    <?php if ($can_evaluate): ?>
        <button type="button" class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#evaluateModal"><i class="bi bi-plus-lg"></i> Evaluate New Item</button>
    <?php endif; ?>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light"><tr><th>Barcode</th><th>Customer</th><th>Category</th><th>Purity</th><th>Net Wt.</th><th>Eligible Amt.</th><th>Status</th><th></th></tr></thead>
            <tbody>
                <?php if (empty($items)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No jewellery items found.</td></tr>
                <?php else: foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['barcode']); ?></td>
                        <td><?php echo htmlspecialchars($item['customer_name'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($item['category_name'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($item['purity_karat']); ?>K</td>
                        <td><?php echo htmlspecialchars($item['net_weight']); ?>g</td>
                        <td>₹<?php echo number_format($item['eligible_amount'], 2); ?></td>
                        <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($item['status']); ?></span></td>
                        <td class="text-end"><a href="<?php echo base_url('admin/jewellery-items/' . $item['id']); ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($pagination['last_page'] > 1): ?>
        <div class="card-footer bg-white">
            <ul class="pagination pagination-sm mb-0">
                <?php for ($p = 1; $p <= $pagination['last_page']; $p++): ?>
                    <li class="page-item <?php echo $p == $pagination['page'] ? 'active' : ''; ?>">
                        <a class="page-link" href="<?php echo base_url('admin/jewellery-items?' . http_build_query(array_merge($filters, array('page' => $p)))); ?>"><?php echo $p; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>

<?php if ($can_evaluate): ?>
<div class="modal fade" id="evaluateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?php echo base_url('admin/jewellery-items/evaluate'); ?>">
                <div class="modal-header"><h5 class="modal-title">Evaluate New Jewellery Item</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12"><label class="form-label">Customer Mobile</label><input type="text" name="customer_mobile" class="form-control" required></div>
                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select" required>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo (int) $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6"><label class="form-label">Purity (Karat)</label><input type="text" name="purity_karat" class="form-control" placeholder="e.g. 22K" required></div>
                        <div class="col-md-6"><label class="form-label">Gross Weight (g)</label><input type="number" step="0.001" name="gross_weight" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">Stone Weight (g)</label><input type="number" step="0.001" name="stone_weight" class="form-control" value="0"></div>
                        <div class="col-12 form-check">
                            <input type="checkbox" name="hallmark_flag" value="1" class="form-check-input" id="hallmark_flag">
                            <label class="form-check-label" for="hallmark_flag">Hallmarked</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-dark">Evaluate</button></div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

