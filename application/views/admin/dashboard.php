<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Employees</div>
                <div class="fs-3 fw-semibold"><?php echo (int) $stats['employees']; ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Customers</div>
                <div class="fs-3 fw-semibold"><?php echo (int) $stats['customers']; ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Active Loans</div>
                <div class="fs-3 fw-semibold text-success"><?php echo (int) $stats['active_loans']; ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Pending Approval</div>
                <div class="fs-3 fw-semibold text-warning"><?php echo (int) $stats['pending_approval']; ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">NPA Loans</div>
                <div class="fs-3 fw-semibold text-danger"><?php echo (int) $stats['npa_loans']; ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold">Recent Loans</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Loan A/C No.</th>
                    <th>Customer</th>
                    <th>Branch</th>
                    <th>Sanctioned Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recent_loans)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">No loans yet.</td></tr>
                <?php else: foreach ($recent_loans as $loan): ?>
                    <tr>
                        <td><a href="<?php echo base_url('admin/loans/' . $loan['id']); ?>"><?php echo htmlspecialchars($loan['loan_account_number'] ?? 'Pending disbursement'); ?></a></td>
                        <td><?php echo htmlspecialchars($loan['customer_name'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($loan['branch_name'] ?? '—'); ?></td>
                        <td>₹<?php echo number_format($loan['sanctioned_amount'], 2); ?></td>
                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($loan['status']); ?></span></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
