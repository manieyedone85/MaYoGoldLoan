<?php $CI =& get_instance(); $error = $CI->session->flashdata('error'); ?>
<?php if (! empty($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="d-flex justify-content-between mb-3">
    <form method="GET" action="<?php echo base_url('admin/employees'); ?>" class="d-flex gap-2">
        <input type="text" name="search" value="<?php echo htmlspecialchars($filters['search']); ?>" class="form-control w-auto" placeholder="Search name / code / mobile">
        <button type="submit" class="btn btn-outline-secondary">Search</button>
    </form>
    <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#createEmployeeModal"><i class="bi bi-plus-lg"></i> New Employee</button>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Mobile</th>
                    <th>Role</th>
                    <th>Branch</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($employees)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No employees found.</td></tr>
                <?php else: foreach ($employees as $employee): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($employee['employee_code'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($employee['name']); ?></td>
                        <td><?php echo htmlspecialchars($employee['mobile']); ?></td>
                        <td><?php echo htmlspecialchars($employee['role_name'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($employee['branch_name'] ?? '—'); ?></td>
                        <td>
                            <span class="badge <?php echo $employee['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                <?php echo $employee['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editEmployeeModal<?php echo (int) $employee['id']; ?>"><i class="bi bi-pencil"></i></button>
                            <form method="POST" action="<?php echo base_url('admin/employees/' . $employee['id'] . '/toggle'); ?>" class="d-inline" onsubmit="return confirm('Are you sure?');">
                                <button type="submit" class="btn btn-sm btn-outline-<?php echo $employee['is_active'] ? 'danger' : 'success'; ?>"><i class="bi bi-power"></i></button>
                            </form>
                        </td>
                    </tr>

                    <!-- Edit modal for this employee -->
                    <div class="modal fade" id="editEmployeeModal<?php echo (int) $employee['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="<?php echo base_url('admin/employees/' . $employee['id']); ?>">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Employee</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Employee Code</label>
                                                <input type="text" name="employee_code" class="form-control" value="<?php echo htmlspecialchars($employee['employee_code'] ?? ''); ?>">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Name</label>
                                                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($employee['name']); ?>">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Mobile</label>
                                                <input type="text" name="mobile" class="form-control" value="<?php echo htmlspecialchars($employee['mobile']); ?>">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Email</label>
                                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($employee['email'] ?? ''); ?>">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Password (leave blank to keep)</label>
                                                <input type="password" name="password" class="form-control">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Role</label>
                                                <select name="role_id" class="form-select">
                                                    <option value="">Select role</option>
                                                    <?php foreach ($roles as $role): ?>
                                                        <option value="<?php echo (int) $role['id']; ?>" <?php echo ($role['id'] == $employee['role_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($role['name']); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Branch</label>
                                                <select name="branch_id" class="form-select">
                                                    <option value="">Unassigned</option>
                                                    <?php foreach ($branches as $branch): ?>
                                                        <option value="<?php echo (int) $branch['id']; ?>" <?php echo ($branch['id'] == $employee['branch_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($branch['name']); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6 form-check ms-2 mt-4">
                                                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active<?php echo (int) $employee['id']; ?>" <?php echo $employee['is_active'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="is_active<?php echo (int) $employee['id']; ?>">Active</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-dark">Save</button>
                                    </div>
                                </form>
                            </div>
                        </div>
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
                        <a class="page-link" href="<?php echo base_url('admin/employees?' . http_build_query(array_merge($filters, array('page' => $p)))); ?>"><?php echo $p; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>

<!-- Create modal -->
<div class="modal fade" id="createEmployeeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?php echo base_url('admin/employees/create'); ?>">
                <div class="modal-header">
                    <h5 class="modal-title">New Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Employee Code</label>
                            <input type="text" name="employee_code" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mobile</label>
                            <input type="text" name="mobile" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Role</label>
                            <select name="role_id" class="form-select">
                                <option value="">Select role</option>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?php echo (int) $role['id']; ?>"><?php echo htmlspecialchars($role['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Branch</label>
                            <select name="branch_id" class="form-select">
                                <option value="">Unassigned</option>
                                <?php foreach ($branches as $branch): ?>
                                    <option value="<?php echo (int) $branch['id']; ?>"><?php echo htmlspecialchars($branch['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 form-check ms-2 mt-4">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active_new" checked>
                            <label class="form-check-label" for="is_active_new">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
