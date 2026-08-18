<?php $CI =& get_instance(); $error = $CI->session->flashdata('error'); ?>
<?php if (! empty($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<ul class="nav nav-tabs mb-3" role="tablist">
    <?php if ($is_ops): ?>
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-branches" type="button">Branches</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-schemes" type="button">Schemes</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-roles" type="button">Roles</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-vaults" type="button">Vaults</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-gl-accounts" type="button">GL Accounts</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-approval-limits" type="button">Approval Limits</button></li>
    <?php endif; ?>
    <li class="nav-item"><button class="nav-link <?php echo $is_ops ? '' : 'active'; ?>" data-bs-toggle="tab" data-bs-target="#tab-rates" type="button">Rates</button></li>
</ul>

<div class="tab-content">

<?php if ($is_ops): ?>
<!-- ===================== Branches ===================== -->
<div class="tab-pane fade show active" id="tab-branches">
    <div class="d-flex justify-content-end mb-3">
        <button type="button" class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#createBranchModal"><i class="bi bi-plus-lg"></i> New Branch</button>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light"><tr><th>Code</th><th>Name</th><th>City</th><th>State</th><th>GST No.</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    <?php if (empty($branches)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No branches yet.</td></tr>
                    <?php else: foreach ($branches as $branch): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($branch['branch_code']); ?></td>
                            <td><?php echo htmlspecialchars($branch['name']); ?></td>
                            <td><?php echo htmlspecialchars($branch['city'] ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($branch['state'] ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($branch['gst_number'] ?? '—'); ?></td>
                            <td><span class="badge <?php echo $branch['is_active'] ? 'bg-success' : 'bg-secondary'; ?>"><?php echo $branch['is_active'] ? 'Active' : 'Inactive'; ?></span></td>
                            <td class="text-end"><button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editBranchModal<?php echo (int) $branch['id']; ?>"><i class="bi bi-pencil"></i></button></td>
                        </tr>

                        <div class="modal fade" id="editBranchModal<?php echo (int) $branch['id']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="<?php echo base_url('admin/masters/branch/' . $branch['id']); ?>">
                                        <div class="modal-header"><h5 class="modal-title">Edit Branch</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                        <div class="modal-body">
                                            <div class="row g-3">
                                                <div class="col-md-6"><label class="form-label">Branch Code</label><input type="text" name="branch_code" class="form-control" value="<?php echo htmlspecialchars($branch['branch_code']); ?>"></div>
                                                <div class="col-md-6"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($branch['name']); ?>"></div>
                                                <div class="col-md-6"><label class="form-label">City</label><input type="text" name="city" class="form-control" value="<?php echo htmlspecialchars($branch['city'] ?? ''); ?>"></div>
                                                <div class="col-md-6"><label class="form-label">State</label><input type="text" name="state" class="form-control" value="<?php echo htmlspecialchars($branch['state'] ?? ''); ?>"></div>
                                                <div class="col-md-6"><label class="form-label">GST Number</label><input type="text" name="gst_number" class="form-control" value="<?php echo htmlspecialchars($branch['gst_number'] ?? ''); ?>"></div>
                                                <div class="col-md-6 form-check ms-2 mt-4">
                                                    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="branch_active<?php echo (int) $branch['id']; ?>" <?php echo $branch['is_active'] ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="branch_active<?php echo (int) $branch['id']; ?>">Active</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-dark">Save</button></div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ===================== Schemes (Loan Products) ===================== -->
<div class="tab-pane fade" id="tab-schemes">
    <div class="d-flex justify-content-end mb-3">
        <button type="button" class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#createSchemeModal"><i class="bi bi-plus-lg"></i> New Scheme</button>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light"><tr><th>Code</th><th>Name</th><th>Interest</th><th>Tenure</th><th>Proc. Fee</th><th>GST</th><th>Insurance</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    <?php if (empty($loan_products)): ?>
                        <tr><td colspan="9" class="text-center text-muted py-4">No schemes yet.</td></tr>
                    <?php else: foreach ($loan_products as $product): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['code']); ?></td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo number_format($product['interest_rate_pct'], 2); ?>% <span class="text-muted small"><?php echo htmlspecialchars($product['interest_type']); ?></span></td>
                            <td><?php echo (int) $product['tenure_months']; ?> mo</td>
                            <td><?php echo number_format($product['processing_fee_pct'], 2); ?>%</td>
                            <td><?php echo number_format($product['gst_pct'], 2); ?>%</td>
                            <td><?php echo number_format($product['insurance_pct'], 2); ?>%</td>
                            <td><span class="badge <?php echo $product['is_active'] ? 'bg-success' : 'bg-secondary'; ?>"><?php echo $product['is_active'] ? 'Active' : 'Inactive'; ?></span></td>
                            <td class="text-end"><button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editSchemeModal<?php echo (int) $product['id']; ?>"><i class="bi bi-pencil"></i></button></td>
                        </tr>

                        <div class="modal fade" id="editSchemeModal<?php echo (int) $product['id']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="<?php echo base_url('admin/masters/loan-product/' . $product['id']); ?>">
                                        <div class="modal-header"><h5 class="modal-title">Edit Scheme</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                        <div class="modal-body">
                                            <div class="row g-3">
                                                <div class="col-md-6"><label class="form-label">Code</label><input type="text" name="code" class="form-control" value="<?php echo htmlspecialchars($product['code']); ?>"></div>
                                                <div class="col-md-6"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($product['name']); ?>"></div>
                                                <div class="col-md-6"><label class="form-label">Interest Rate %</label><input type="number" step="0.01" name="interest_rate_pct" class="form-control" value="<?php echo htmlspecialchars($product['interest_rate_pct']); ?>"></div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Interest Type</label>
                                                    <select name="interest_type" class="form-select">
                                                        <?php foreach (array('FLAT', 'REDUCING') as $type): ?>
                                                            <option value="<?php echo $type; ?>" <?php echo $product['interest_type'] === $type ? 'selected' : ''; ?>><?php echo $type; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-6"><label class="form-label">Tenure (months)</label><input type="number" name="tenure_months" class="form-control" value="<?php echo (int) $product['tenure_months']; ?>"></div>
                                                <div class="col-md-6"><label class="form-label">Processing Fee %</label><input type="number" step="0.01" name="processing_fee_pct" class="form-control" value="<?php echo htmlspecialchars($product['processing_fee_pct']); ?>"></div>
                                                <div class="col-md-6"><label class="form-label">GST %</label><input type="number" step="0.01" name="gst_pct" class="form-control" value="<?php echo htmlspecialchars($product['gst_pct']); ?>"></div>
                                                <div class="col-md-6"><label class="form-label">Insurance %</label><input type="number" step="0.01" name="insurance_pct" class="form-control" value="<?php echo htmlspecialchars($product['insurance_pct']); ?>"></div>
                                                <div class="col-md-6 form-check ms-2 mt-4">
                                                    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="scheme_active<?php echo (int) $product['id']; ?>" <?php echo $product['is_active'] ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="scheme_active<?php echo (int) $product['id']; ?>">Active</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-dark">Save</button></div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ===================== Roles ===================== -->
<div class="tab-pane fade" id="tab-roles">
    <div class="d-flex justify-content-end mb-3">
        <button type="button" class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#createRoleModal"><i class="bi bi-plus-lg"></i> New Role</button>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light"><tr><th>Code</th><th>Name</th><th>Description</th><th></th></tr></thead>
                <tbody>
                    <?php if (empty($roles)): ?>
                        <tr><td colspan="4" class="text-center text-muted py-4">No roles yet.</td></tr>
                    <?php else: foreach ($roles as $role): ?>
                        <tr>
                            <td><code><?php echo htmlspecialchars($role['code']); ?></code></td>
                            <td><?php echo htmlspecialchars($role['name']); ?></td>
                            <td class="text-muted small"><?php echo htmlspecialchars($role['description'] ?? '—'); ?></td>
                            <td class="text-end"><button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editRoleModal<?php echo (int) $role['id']; ?>"><i class="bi bi-pencil"></i></button></td>
                        </tr>

                        <div class="modal fade" id="editRoleModal<?php echo (int) $role['id']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="<?php echo base_url('admin/masters/role/' . $role['id']); ?>">
                                        <div class="modal-header"><h5 class="modal-title">Edit Role</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                        <div class="modal-body">
                                            <div class="row g-3">
                                                <div class="col-12"><label class="form-label">Code</label><input type="text" class="form-control" value="<?php echo htmlspecialchars($role['code']); ?>" disabled></div>
                                                <div class="col-12"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($role['name']); ?>"></div>
                                                <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"><?php echo htmlspecialchars($role['description'] ?? ''); ?></textarea></div>
                                            </div>
                                        </div>
                                        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-dark">Save</button></div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ===================== Vaults ===================== -->
<div class="tab-pane fade" id="tab-vaults">
    <div class="d-flex justify-content-end mb-3">
        <button type="button" class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#createVaultModal"><i class="bi bi-plus-lg"></i> New Vault</button>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light"><tr><th>Name</th><th>Branch</th><th></th></tr></thead>
                <tbody>
                    <?php if (empty($vaults)): ?>
                        <tr><td colspan="3" class="text-center text-muted py-4">No vaults yet.</td></tr>
                    <?php else: foreach ($vaults as $vault): ?>
                        <?php $vault_branch = null; foreach ($branches as $b) { if ((int) $b['id'] === (int) $vault['branch_id']) { $vault_branch = $b; break; } } ?>
                        <tr>
                            <td><?php echo htmlspecialchars($vault['name']); ?></td>
                            <td><?php echo htmlspecialchars($vault_branch['name'] ?? '—'); ?></td>
                            <td class="text-end"><button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editVaultModal<?php echo (int) $vault['id']; ?>"><i class="bi bi-pencil"></i></button></td>
                        </tr>

                        <div class="modal fade" id="editVaultModal<?php echo (int) $vault['id']; ?>" tabindex="-1">
                            <div class="modal-dialog"><div class="modal-content">
                                <form method="POST" action="<?php echo base_url('admin/masters/vault/' . $vault['id']); ?>">
                                    <div class="modal-header"><h5 class="modal-title">Edit Vault</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                    <div class="modal-body">
                                        <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($vault['name']); ?>"></div>
                                        <div class="mb-1">
                                            <label class="form-label">Branch</label>
                                            <select name="branch_id" class="form-select">
                                                <?php foreach ($branches as $b): ?>
                                                    <option value="<?php echo (int) $b['id']; ?>" <?php echo (int) $b['id'] === (int) $vault['branch_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($b['name']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-dark">Save</button></div>
                                </form>
                            </div></div>
                        </div>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ===================== GL Accounts ===================== -->
<div class="tab-pane fade" id="tab-gl-accounts">
    <div class="d-flex justify-content-end mb-3">
        <button type="button" class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#createGlAccountModal"><i class="bi bi-plus-lg"></i> New GL Account</button>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light"><tr><th>Code</th><th>Name</th><th>Type</th><th></th></tr></thead>
                <tbody>
                    <?php if (empty($gl_accounts)): ?>
                        <tr><td colspan="4" class="text-center text-muted py-4">No GL accounts yet.</td></tr>
                    <?php else: foreach ($gl_accounts as $gl): ?>
                        <tr>
                            <td><code><?php echo htmlspecialchars($gl['code']); ?></code></td>
                            <td><?php echo htmlspecialchars($gl['name']); ?></td>
                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($gl['type']); ?></span></td>
                            <td class="text-end"><button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editGlAccountModal<?php echo (int) $gl['id']; ?>"><i class="bi bi-pencil"></i></button></td>
                        </tr>

                        <div class="modal fade" id="editGlAccountModal<?php echo (int) $gl['id']; ?>" tabindex="-1">
                            <div class="modal-dialog"><div class="modal-content">
                                <form method="POST" action="<?php echo base_url('admin/masters/gl-account/' . $gl['id']); ?>">
                                    <div class="modal-header"><h5 class="modal-title">Edit GL Account</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                    <div class="modal-body">
                                        <div class="mb-3"><label class="form-label">Code</label><input type="text" name="code" class="form-control" value="<?php echo htmlspecialchars($gl['code']); ?>"></div>
                                        <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($gl['name']); ?>"></div>
                                        <div class="mb-1">
                                            <label class="form-label">Type</label>
                                            <select name="type" class="form-select">
                                                <?php foreach (array('ASSET', 'LIABILITY', 'INCOME', 'EXPENSE') as $t): ?>
                                                    <option value="<?php echo $t; ?>" <?php echo $gl['type'] === $t ? 'selected' : ''; ?>><?php echo $t; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-dark">Save</button></div>
                                </form>
                            </div></div>
                        </div>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ===================== Approval Limits ===================== -->
<div class="tab-pane fade" id="tab-approval-limits">
    <div class="d-flex justify-content-end mb-3">
        <button type="button" class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#createApprovalLimitModal"><i class="bi bi-plus-lg"></i> New Limit</button>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light"><tr><th>Role</th><th>Max Amount</th><th></th></tr></thead>
                <tbody>
                    <?php if (empty($approval_limits)): ?>
                        <tr><td colspan="3" class="text-center text-muted py-4">No approval limits configured.</td></tr>
                    <?php else: foreach ($approval_limits as $limit): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($limit['role_name'] ?? $limit['role_code'] ?? '—'); ?></td>
                            <td>₹<?php echo number_format($limit['max_amount'], 2); ?></td>
                            <td class="text-end"><button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editLimitModal<?php echo (int) $limit['id']; ?>"><i class="bi bi-pencil"></i></button></td>
                        </tr>

                        <div class="modal fade" id="editLimitModal<?php echo (int) $limit['id']; ?>" tabindex="-1">
                            <div class="modal-dialog"><div class="modal-content">
                                <form method="POST" action="<?php echo base_url('admin/masters/approval-limit/' . $limit['id']); ?>">
                                    <div class="modal-header"><h5 class="modal-title">Edit Approval Limit — <?php echo htmlspecialchars($limit['role_name'] ?? ''); ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                    <div class="modal-body">
                                        <label class="form-label">Max Amount</label>
                                        <input type="number" step="0.01" name="max_amount" class="form-control" value="<?php echo htmlspecialchars($limit['max_amount']); ?>">
                                    </div>
                                    <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-dark">Save</button></div>
                                </form>
                            </div></div>
                        </div>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ===================== Rates ===================== -->
<div class="tab-pane fade <?php echo $is_ops ? '' : 'show active'; ?>" id="tab-rates">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div class="alert alert-info small mb-0">Gold rates follow an Appraiser → Branch/Regional Manager maker-checker workflow, same as the mobile app.</div>
        <?php if ($can_propose_rate): ?>
            <button type="button" class="btn btn-dark btn-sm ms-3 flex-shrink-0" data-bs-toggle="modal" data-bs-target="#proposeRateModal"><i class="bi bi-plus-lg"></i> Propose Rate</button>
        <?php endif; ?>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="table-light"><tr><th>Karat</th><th>Rate/gram</th><th>LTV %</th><th>Effective Date</th><th>Status</th><th>Proposed By</th><th>Approved By</th><th></th></tr></thead>
                <tbody>
                    <?php if (empty($gold_rates)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">No gold rates yet.</td></tr>
                    <?php else: foreach ($gold_rates as $rate): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($rate['karat']); ?></td>
                            <td>₹<?php echo number_format($rate['rate_per_gram'], 2); ?></td>
                            <td><?php echo number_format($rate['ltv_pct'], 2); ?>%</td>
                            <td><?php echo htmlspecialchars(date('d-M-Y', strtotime($rate['effective_date']))); ?></td>
                            <td><span class="badge <?php echo $rate['status'] === 'APPROVED' ? 'bg-success' : 'bg-warning text-dark'; ?>"><?php echo htmlspecialchars($rate['status']); ?></span></td>
                            <td><?php echo htmlspecialchars((string) ($rate['proposed_by'] ?? '—')); ?></td>
                            <td><?php echo htmlspecialchars((string) ($rate['approved_by'] ?? '—')); ?></td>
                            <td class="text-end">
                                <?php if ($can_approve_rate && $rate['status'] === 'PENDING_APPROVAL'): ?>
                                    <form method="POST" action="<?php echo base_url('admin/masters/rate/' . $rate['id'] . '/approve'); ?>" class="d-inline" onsubmit="return confirm('Approve this gold rate?');">
                                        <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</div>

<!-- Create modals -->
<div class="modal fade" id="createBranchModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?php echo base_url('admin/masters/branch/create'); ?>">
                <div class="modal-header"><h5 class="modal-title">New Branch</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Branch Code</label><input type="text" name="branch_code" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Name</label><input type="text" name="name" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">City</label><input type="text" name="city" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">State</label><input type="text" name="state" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">GST Number</label><input type="text" name="gst_number" class="form-control"></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-dark">Save</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="createSchemeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?php echo base_url('admin/masters/loan-product/create'); ?>">
                <div class="modal-header"><h5 class="modal-title">New Scheme</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Code</label><input type="text" name="code" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Name</label><input type="text" name="name" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Interest Rate %</label><input type="number" step="0.01" name="interest_rate_pct" class="form-control"></div>
                        <div class="col-md-6">
                            <label class="form-label">Interest Type</label>
                            <select name="interest_type" class="form-select">
                                <option value="FLAT">FLAT</option>
                                <option value="REDUCING">REDUCING</option>
                            </select>
                        </div>
                        <div class="col-md-6"><label class="form-label">Tenure (months)</label><input type="number" name="tenure_months" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Processing Fee %</label><input type="number" step="0.01" name="processing_fee_pct" class="form-control" value="0"></div>
                        <div class="col-md-6"><label class="form-label">GST %</label><input type="number" step="0.01" name="gst_pct" class="form-control" value="18"></div>
                        <div class="col-md-6"><label class="form-label">Insurance %</label><input type="number" step="0.01" name="insurance_pct" class="form-control" value="0"></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-dark">Save</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="createRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?php echo base_url('admin/masters/role/create'); ?>">
                <div class="modal-header"><h5 class="modal-title">New Role</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12"><label class="form-label">Code</label><input type="text" name="code" class="form-control" placeholder="e.g. BRANCH_EXECUTIVE"></div>
                        <div class="col-12"><label class="form-label">Name</label><input type="text" name="name" class="form-control"></div>
                        <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-dark">Save</button></div>
            </form>
        </div>
    </div>
</div>

<?php if ($is_ops): ?>
<div class="modal fade" id="createVaultModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="POST" action="<?php echo base_url('admin/masters/vault/create'); ?>">
            <div class="modal-header"><h5 class="modal-title">New Vault</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control"></div>
                <div class="mb-1">
                    <label class="form-label">Branch</label>
                    <select name="branch_id" class="form-select">
                        <?php foreach ($branches as $b): ?>
                            <option value="<?php echo (int) $b['id']; ?>"><?php echo htmlspecialchars($b['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-dark">Save</button></div>
        </form>
    </div></div>
</div>

<div class="modal fade" id="createGlAccountModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="POST" action="<?php echo base_url('admin/masters/gl-account/create'); ?>">
            <div class="modal-header"><h5 class="modal-title">New GL Account</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Code</label><input type="text" name="code" class="form-control"></div>
                <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control"></div>
                <div class="mb-1">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="ASSET">ASSET</option>
                        <option value="LIABILITY">LIABILITY</option>
                        <option value="INCOME">INCOME</option>
                        <option value="EXPENSE">EXPENSE</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-dark">Save</button></div>
        </form>
    </div></div>
</div>

<div class="modal fade" id="createApprovalLimitModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="POST" action="<?php echo base_url('admin/masters/approval-limit/create'); ?>">
            <div class="modal-header"><h5 class="modal-title">New Approval Limit</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Role</label>
                    <select name="role_id" class="form-select">
                        <?php foreach ($roles as $r): ?>
                            <option value="<?php echo (int) $r['id']; ?>"><?php echo htmlspecialchars($r['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-1"><label class="form-label">Max Amount</label><input type="number" step="0.01" name="max_amount" class="form-control"></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-dark">Save</button></div>
        </form>
    </div></div>
</div>
<?php endif; ?>

<?php if ($can_propose_rate): ?>
<div class="modal fade" id="proposeRateModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="POST" action="<?php echo base_url('admin/masters/rate/propose'); ?>">
            <div class="modal-header"><h5 class="modal-title">Propose Gold Rate</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Karat</label><input type="text" name="karat" class="form-control" placeholder="e.g. 22K" required></div>
                    <div class="col-md-6"><label class="form-label">Rate per Gram</label><input type="number" step="0.01" name="rate_per_gram" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">LTV %</label><input type="number" step="0.01" name="ltv_pct" class="form-control" value="75.00"></div>
                    <div class="col-md-6"><label class="form-label">Effective Date</label><input type="date" name="effective_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-dark">Propose</button></div>
        </form>
    </div></div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
