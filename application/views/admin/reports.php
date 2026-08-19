<?php
$groups = array(
    'Operational' => array('loan_status', 'overdue_emi', 'daily_cash', 'daily_collection', 'jewellery_release'),
    'Performance & KPIs' => array('kpi_summary', 'branch_performance', 'employee_performance', 'renewal_topup_reloan'),
    'Compliance & Tax' => array('gst_summary', 'audit_activity'),
);
?>
<p class="text-muted">Pick a report to filter, view, and download it as Excel.</p>

<?php foreach ($groups as $group_label => $codes): ?>
    <h6 class="text-muted text-uppercase small mt-4 mb-2"><?php echo htmlspecialchars($group_label); ?></h6>
    <div class="row g-3 mb-2">
        <?php foreach ($codes as $code): if (! isset($report_defs[$code])) continue; $def = $report_defs[$code]; ?>
            <div class="col-md-6 col-lg-4">
                <a href="<?php echo base_url('admin/reports/view/' . $code); ?>" class="card border-0 shadow-sm h-100 text-decoration-none text-body">
                    <div class="card-body">
                        <div class="fw-semibold mb-1"><?php echo htmlspecialchars($def['label']); ?></div>
                        <div class="text-muted small"><?php echo htmlspecialchars($def['description']); ?></div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endforeach; ?>
