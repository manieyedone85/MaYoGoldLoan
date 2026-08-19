<a href="<?php echo base_url('admin/reports'); ?>" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> All Reports</a>

<p class="text-muted"><?php echo htmlspecialchars($def['description']); ?></p>

<div class="d-flex justify-content-between align-items-end flex-wrap gap-2 mb-3">
    <form method="GET" action="<?php echo base_url('admin/reports/view/' . $code); ?>" class="row g-2 align-items-end">
        <?php if (in_array('branch', $def['filters'], true)): ?>
            <div class="col-auto">
                <label class="form-label small mb-0">Branch</label>
                <select name="branch_id" class="form-select form-select-sm">
                    <option value="">All branches</option>
                    <?php foreach ($branches as $branch): ?>
                        <option value="<?php echo $branch['id']; ?>" <?php echo (string) $filters['branch_id'] === (string) $branch['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($branch['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
        <?php if (in_array('date', $def['filters'], true)): ?>
            <div class="col-auto">
                <label class="form-label small mb-0">Date</label>
                <input type="date" name="date" value="<?php echo htmlspecialchars($filters['date']); ?>" class="form-control form-control-sm">
            </div>
        <?php endif; ?>
        <?php if (in_array('period', $def['filters'], true)): ?>
            <div class="col-auto">
                <label class="form-label small mb-0">From</label>
                <input type="date" name="from" value="<?php echo htmlspecialchars($filters['from']); ?>" class="form-control form-control-sm">
            </div>
            <div class="col-auto">
                <label class="form-label small mb-0">To</label>
                <input type="date" name="to" value="<?php echo htmlspecialchars($filters['to']); ?>" class="form-control form-control-sm">
            </div>
        <?php endif; ?>
        <?php if (in_array('entity_type', $def['filters'], true)): ?>
            <div class="col-auto">
                <label class="form-label small mb-0">Entity type</label>
                <input type="text" name="entity_type" value="<?php echo htmlspecialchars($filters['entity_type']); ?>" class="form-control form-control-sm" placeholder="e.g. Loan">
            </div>
        <?php endif; ?>
        <div class="col-auto">
            <button type="submit" class="btn btn-sm btn-outline-secondary">Apply</button>
        </div>
    </form>

    <a href="<?php echo base_url('admin/reports/export/' . $code . '?' . http_build_query($filters)); ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-file-earmark-excel"></i> Download Excel</a>
</div>

<?php foreach ($tables as $table): ?>
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white fw-semibold"><?php echo htmlspecialchars($table['title']); ?></div>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="table-light">
                    <tr><?php foreach ($table['headers'] as $header): ?><th><?php echo htmlspecialchars($header); ?></th><?php endforeach; ?></tr>
                </thead>
                <tbody>
                    <?php if (empty($table['rows'])): ?>
                        <tr><td colspan="<?php echo count($table['headers']); ?>" class="text-center text-muted py-3">No data for the selected filters.</td></tr>
                    <?php else: foreach ($table['rows'] as $row): ?>
                        <tr>
                            <?php foreach ($row as $value): ?>
                                <td><?php echo htmlspecialchars($value === null ? '—' : (string) $value); ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endforeach; ?>
