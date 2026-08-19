<?php $CI =& get_instance(); $error = $CI->session->flashdata('error'); ?>
<?php if (! empty($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="d-flex justify-content-between mb-3">
    <form method="GET" action="<?php echo base_url('admin/auctions'); ?>" class="d-flex gap-2">
        <input type="text" name="search" value="<?php echo htmlspecialchars($filters['search']); ?>" class="form-control w-auto" placeholder="Search branch / status">
        <button type="submit" class="btn btn-outline-secondary">Filter</button>
    </form>
    <button type="button" class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#scheduleModal"><i class="bi bi-plus-lg"></i> Schedule Auction</button>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light"><tr><th>Auction Date</th><th>Branch</th><th>Status</th><th></th></tr></thead>
            <tbody>
                <?php if (empty($schedules)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">No auctions scheduled.</td></tr>
                <?php else: foreach ($schedules as $s): ?>
                    <tr>
                        <td><?php echo htmlspecialchars(date('d-M-Y', strtotime($s['auction_date']))); ?></td>
                        <td><?php echo htmlspecialchars($s['branch_name'] ?? '—'); ?></td>
                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($s['status']); ?></span></td>
                        <td class="text-end"><a href="<?php echo base_url('admin/auctions/' . $s['id']); ?>" class="btn btn-sm btn-outline-secondary">Manage</a></td>
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
                        <a class="page-link" href="<?php echo base_url('admin/auctions?' . http_build_query(array_merge($filters, array('page' => $p)))); ?>"><?php echo $p; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="scheduleModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="POST" action="<?php echo base_url('admin/auctions/schedule'); ?>">
            <div class="modal-header"><h5 class="modal-title">Schedule Auction</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Branch</label>
                    <select name="branch_id" class="form-select" required>
                        <?php foreach ($branches as $b): ?>
                            <option value="<?php echo (int) $b['id']; ?>"><?php echo htmlspecialchars($b['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-1"><label class="form-label">Auction Date</label><input type="date" name="auction_date" class="form-control" required></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-dark">Schedule</button></div>
        </form>
    </div></div>
</div>

