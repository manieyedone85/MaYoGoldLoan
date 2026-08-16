<form method="GET" action="<?php echo base_url('admin/reports'); ?>" class="row g-2 mb-4 align-items-end">
    <div class="col-auto">
        <label class="form-label small mb-0">Branch</label>
        <select name="branch_id" class="form-select form-select-sm">
            <option value="">All branches</option>
            <?php foreach ($branches as $branch): ?>
                <option value="<?php echo $branch['id']; ?>" <?php echo (string) $filters['branch_id'] === (string) $branch['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($branch['name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-auto">
        <label class="form-label small mb-0">Daily reports date</label>
        <input type="date" name="date" value="<?php echo htmlspecialchars($filters['date']); ?>" class="form-control form-control-sm">
    </div>
    <div class="col-auto">
        <label class="form-label small mb-0">Period from</label>
        <input type="date" name="from" value="<?php echo htmlspecialchars($filters['from']); ?>" class="form-control form-control-sm">
    </div>
    <div class="col-auto">
        <label class="form-label small mb-0">Period to</label>
        <input type="date" name="to" value="<?php echo htmlspecialchars($filters['to']); ?>" class="form-control form-control-sm">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-sm btn-outline-secondary">Apply</button>
    </div>
</form>

<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100"><div class="card-body">
            <div class="text-muted small">Avg. processing time</div>
            <div class="fs-4 fw-semibold"><?php echo $kpi_summary['avg_processing_time_hours'] !== null ? number_format($kpi_summary['avg_processing_time_hours'], 1) . ' hrs' : '—'; ?></div>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100"><div class="card-body">
            <div class="text-muted small">KYC completion rate</div>
            <div class="fs-4 fw-semibold"><?php echo $kpi_summary['kyc_completion_rate_pct'] !== null ? number_format($kpi_summary['kyc_completion_rate_pct'], 1) . '%' : '—'; ?></div>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100"><div class="card-body">
            <div class="text-muted small">Disbursement volume</div>
            <div class="fs-4 fw-semibold">₹<?php echo number_format($kpi_summary['disbursement_volume'], 0); ?></div>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100"><div class="card-body">
            <div class="text-muted small">Collection rate <span title="Interest collected this period vs. one month's interest accrual across all currently-servicing loans -- an approximation, no per-period interest-due ledger exists.">ⓘ</span></div>
            <div class="fs-4 fw-semibold"><?php echo $kpi_summary['collection_rate_pct'] !== null ? number_format($kpi_summary['collection_rate_pct'], 1) . '%' : '—'; ?></div>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100"><div class="card-body">
            <div class="text-muted small">Overdue rate</div>
            <div class="fs-4 fw-semibold"><?php echo $kpi_summary['overdue_rate_pct'] !== null ? number_format($kpi_summary['overdue_rate_pct'], 1) . '%' : '—'; ?></div>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100"><div class="card-body">
            <div class="text-muted small">Renewal rate</div>
            <div class="fs-4 fw-semibold"><?php echo $kpi_summary['renewal_rate_pct'] !== null ? number_format($kpi_summary['renewal_rate_pct'], 1) . '%' : '—'; ?></div>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100"><div class="card-body">
            <div class="text-muted small">Repeat-customer rate</div>
            <div class="fs-4 fw-semibold"><?php echo $kpi_summary['repeat_customer_rate_pct'] !== null ? number_format($kpi_summary['repeat_customer_rate_pct'], 1) . '%' : '—'; ?></div>
        </div></div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">Loan Portfolio Status</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Group</th><th>Count</th><th>Total Sanctioned</th></tr></thead>
                    <tbody>
                        <?php foreach ($loan_status as $label => $group): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($label); ?> <span class="text-muted small">(<?php echo implode(', ', $group['statuses']); ?>)</span></td>
                                <td><?php echo (int) $group['count']; ?></td>
                                <td>₹<?php echo number_format($group['total_sanctioned_amount'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">Daily Cash &amp; Collection — <?php echo htmlspecialchars($filters['date']); ?></div>
            <div class="card-body">
                <div class="row g-2 small">
                    <div class="col-6"><strong>Disbursed:</strong> ₹<?php echo number_format($daily_cash['disbursed_amount'], 2); ?> (<?php echo $daily_cash['disbursed_count']; ?>)</div>
                    <div class="col-6"><strong>Collected:</strong> ₹<?php echo number_format($daily_cash['collected_amount'], 2); ?> (<?php echo $daily_cash['collected_count']; ?>)</div>
                    <div class="col-6"><strong>Net cash movement:</strong> ₹<?php echo number_format($daily_cash['net_cash_movement'], 2); ?></div>
                    <div class="col-6"><strong>Grand total collected:</strong> ₹<?php echo number_format($daily_collection['grand_total_collected'], 2); ?></div>
                </div>
                <?php if (! empty($daily_collection['interest_collected']['by_mode'])): ?>
                    <table class="table table-sm mt-2 mb-0">
                        <thead class="table-light"><tr><th>Mode</th><th>Amount</th><th>Count</th></tr></thead>
                        <tbody>
                            <?php foreach ($daily_collection['interest_collected']['by_mode'] as $row): ?>
                                <tr><td><?php echo htmlspecialchars($row['mode']); ?></td><td>₹<?php echo number_format($row['total'], 2); ?></td><td><?php echo (int) $row['count']; ?></td></tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white fw-semibold d-flex justify-content-between">
        <span>Outstanding &amp; Overdue EMI</span>
        <span class="text-muted small">Total outstanding: ₹<?php echo number_format($overdue_emi['total_outstanding_amount'], 2); ?> across <?php echo $overdue_emi['count']; ?> loan(s)</span>
    </div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead class="table-light"><tr><th>A/C No.</th><th>Customer</th><th>Branch</th><th>Outstanding</th><th>Due Date</th><th>Days Overdue</th></tr></thead>
            <tbody>
                <?php if (empty($overdue_emi['data'])): ?>
                    <tr><td colspan="6" class="text-center text-muted py-3">No overdue loans.</td></tr>
                <?php else: foreach ($overdue_emi['data'] as $row): ?>
                    <tr>
                        <td><a href="<?php echo base_url('admin/loans/' . $row['id']); ?>"><?php echo htmlspecialchars($row['loan_account_number'] ?? 'Pending disbursement'); ?></a></td>
                        <td><?php echo htmlspecialchars($row['customer_name'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($row['branch_name'] ?? '—'); ?></td>
                        <td>₹<?php echo number_format($row['outstanding_amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars(date('d-M-Y', strtotime($row['due_date']))); ?></td>
                        <td><span class="badge bg-danger"><?php echo (int) $row['days_overdue']; ?></span></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">Branch Performance — <?php echo htmlspecialchars($filters['from']); ?> to <?php echo htmlspecialchars($filters['to']); ?></div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Branch</th><th>Loans</th><th>Sanctioned</th><th>Disbursed</th></tr></thead>
                    <tbody>
                        <?php foreach ($branch_performance['data'] as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['branch_name']); ?></td>
                                <td><?php echo (int) $row['loans_created']; ?></td>
                                <td>₹<?php echo number_format($row['total_sanctioned_amount'], 2); ?></td>
                                <td>₹<?php echo number_format($row['amount_disbursed'], 2); ?> (<?php echo (int) $row['disbursements_count']; ?>)</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">Employee Performance — <?php echo htmlspecialchars($filters['from']); ?> to <?php echo htmlspecialchars($filters['to']); ?></div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Employee</th><th>Loans</th><th>Disbursed</th><th>Collected</th></tr></thead>
                    <tbody>
                        <?php if (empty($employee_performance['data'])): ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">No activity in this period.</td></tr>
                        <?php else: foreach ($employee_performance['data'] as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['name'] ?? ('User #' . $row['user_id'])); ?></td>
                                <td><?php echo (int) $row['loans_created']; ?></td>
                                <td>₹<?php echo number_format($row['amount_disbursed'], 2); ?> (<?php echo (int) $row['disbursements_count']; ?>)</td>
                                <td>₹<?php echo number_format($row['amount_collected'], 2); ?> (<?php echo (int) $row['collections_count']; ?>)</td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white fw-semibold">Renewal / Top-up / Re-loan Activity — <?php echo htmlspecialchars($filters['from']); ?> to <?php echo htmlspecialchars($filters['to']); ?></div>
    <div class="card-body row g-3 small">
        <div class="col-md-4"><strong>Renewals:</strong> <?php echo $renewal_topup_reloan['renewals']['count']; ?>, interest paid ₹<?php echo number_format($renewal_topup_reloan['renewals']['total_interest_paid'], 2); ?></div>
        <div class="col-md-4"><strong>Top-ups:</strong> <?php echo $renewal_topup_reloan['topups']['count']; ?>, approved ₹<?php echo number_format($renewal_topup_reloan['topups']['total_approved_amount'], 2); ?></div>
        <div class="col-md-4"><strong>Re-loans:</strong> <?php echo $renewal_topup_reloan['reloads']['count']; ?>, total ₹<?php echo number_format($renewal_topup_reloan['reloads']['total_reload_amount'], 2); ?></div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white fw-semibold">Jewellery Release Report — <?php echo htmlspecialchars($filters['from']); ?> to <?php echo htmlspecialchars($filters['to']); ?></div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead class="table-light"><tr><th>Loan A/C</th><th>Customer</th><th>Barcode</th><th>Released To</th><th>Released At</th></tr></thead>
            <tbody>
                <?php if (empty($jewellery_release['data'])): ?>
                    <tr><td colspan="5" class="text-center text-muted py-3">No releases in this period.</td></tr>
                <?php else: foreach ($jewellery_release['data'] as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['loan_account_number'] ?? 'Pending disbursement'); ?></td>
                        <td><?php echo htmlspecialchars($row['customer_name'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($row['barcode'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($row['released_to']); ?></td>
                        <td><?php echo htmlspecialchars(date('d-M-Y H:i', strtotime($row['released_at']))); ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white fw-semibold">Audit / User Activity — <?php echo htmlspecialchars($filters['from']); ?> to <?php echo htmlspecialchars($filters['to']); ?> <span class="text-muted small">(latest 50)</span></div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead class="table-light"><tr><th>When</th><th>Entity</th><th>Action</th><th>Actor</th></tr></thead>
            <tbody>
                <?php if (empty($audit_activity['data'])): ?>
                    <tr><td colspan="4" class="text-center text-muted py-3">No audit activity in this period.</td></tr>
                <?php else: foreach ($audit_activity['data'] as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                        <td><?php echo htmlspecialchars($row['entity_type'] . ' #' . $row['entity_id']); ?></td>
                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($row['action']); ?></span></td>
                        <td><?php echo htmlspecialchars((string) ($row['actor_id'] ?? '—')); ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
