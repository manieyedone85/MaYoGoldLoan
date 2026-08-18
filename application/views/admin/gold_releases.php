<?php $CI =& get_instance(); $error = $CI->session->flashdata('error'); ?>
<?php if (! empty($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold">Loans Settled/Closed — Jewellery Release</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light"><tr><th>Loan A/C</th><th>Customer</th><th>Branch</th><th>Status</th><th class="text-end"></th></tr></thead>
            <tbody>
                <?php if (empty($loans)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">No loans awaiting jewellery release.</td></tr>
                <?php else: foreach ($loans as $loan): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($loan['loan_account_number'] ?? 'Pending disbursement'); ?></td>
                        <td><?php echo htmlspecialchars($loan['customer_name'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($loan['branch_name'] ?? '—'); ?></td>
                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($loan['status']); ?></span></td>
                        <td class="text-end"><a href="<?php echo base_url('admin/gold-releases/' . $loan['id']); ?>" class="btn btn-sm btn-dark">Checklist</a></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
