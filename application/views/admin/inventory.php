<?php $CI =& get_instance(); $error = $CI->session->flashdata('error'); ?>
<?php if (! empty($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="d-flex justify-content-between mb-3">
    <form method="GET" action="<?php echo base_url('admin/inventory'); ?>" class="d-flex gap-2">
        <input type="text" name="search" value="<?php echo htmlspecialchars($filters['search']); ?>" class="form-control w-auto" placeholder="Search packet code / barcode / vault / branch">
        <select name="branch_id" class="form-select w-auto" onchange="this.form.submit()">
            <option value="">Vault status for branch…</option>
            <?php foreach ($branches as $branch): ?>
                <option value="<?php echo (int) $branch['id']; ?>" <?php echo (string) $branch_id === (string) $branch['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($branch['name']); ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-outline-secondary">Filter</button>
    </form>
    <button type="button" class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#newPacketModal"><i class="bi bi-plus-lg"></i> New Packet</button>
</div>

<?php if ($branch_id): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold">Vault Status</div>
    <div class="card-body d-flex gap-4 flex-wrap">
        <?php if (empty($vault_status)): ?>
            <p class="text-muted mb-0">No packets in this branch's vaults.</p>
        <?php else: foreach ($vault_status as $row): ?>
            <div class="text-center">
                <div class="fs-4 fw-semibold"><?php echo (int) $row['total']; ?></div>
                <div class="small text-muted"><?php echo htmlspecialchars($row['status']); ?></div>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold">Gold Packets</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light"><tr><th>Packet Code</th><th>Jewellery</th><th>Vault</th><th>Branch</th><th>Status</th><th class="text-end">Action</th></tr></thead>
            <tbody>
                <?php if (empty($packets)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No gold packets yet.</td></tr>
                <?php else: foreach ($packets as $packet): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($packet['packet_code']); ?></td>
                        <td><?php echo htmlspecialchars($packet['barcode'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($packet['vault_name'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($packet['branch_name'] ?? '—'); ?></td>
                        <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($packet['status']); ?></span></td>
                        <td class="text-end"><button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#transferModal<?php echo (int) $packet['id']; ?>">Transfer</button></td>
                    </tr>

                    <div class="modal fade" id="transferModal<?php echo (int) $packet['id']; ?>" tabindex="-1">
                        <div class="modal-dialog"><div class="modal-content">
                            <form method="POST" action="<?php echo base_url('admin/inventory/' . $packet['id'] . '/transfer'); ?>">
                                <div class="modal-header"><h5 class="modal-title">Transfer <?php echo htmlspecialchars($packet['packet_code']); ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                <div class="modal-body">
                                    <label class="form-label">To Vault</label>
                                    <select name="to_vault_id" class="form-select" required>
                                        <?php foreach ($vaults as $v): ?>
                                            <option value="<?php echo (int) $v['id']; ?>" <?php echo (int) $v['id'] === (int) $packet['vault_id'] ? 'disabled' : ''; ?>><?php echo htmlspecialchars($v['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-dark">Transfer</button></div>
                            </form>
                        </div></div>
                    </div>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($pagination['last_page'] > 1): ?>
        <div class="card-footer bg-white">
            <ul class="pagination pagination-sm mb-0">
                <?php for ($p = 1; $p <= $pagination['last_page']; $p++): ?>
                    <li class="page-item <?php echo $p == $pagination['page'] ? 'active' : ''; ?>">
                        <a class="page-link" href="<?php echo base_url('admin/inventory?' . http_build_query(array_merge($filters, array('page' => $p)))); ?>"><?php echo $p; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="newPacketModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="POST" action="<?php echo base_url('admin/inventory/packet'); ?>">
            <div class="modal-header"><h5 class="modal-title">New Gold Packet</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Jewellery Item ID</label><input type="number" name="jewellery_item_id" class="form-control" required></div>
                <div class="mb-1">
                    <label class="form-label">Vault</label>
                    <select name="vault_id" class="form-select" required>
                        <?php foreach ($vaults as $v): ?>
                            <option value="<?php echo (int) $v['id']; ?>"><?php echo htmlspecialchars($v['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-dark">Create</button></div>
        </form>
    </div></div>
</div>

