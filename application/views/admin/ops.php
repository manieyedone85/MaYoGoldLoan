<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-notifications" type="button">Notification Log</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-templates" type="button">Templates</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-sync" type="button">Sync Queue</button></li>
</ul>

<div class="tab-content">

<div class="tab-pane fade show active" id="tab-notifications">
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="table-light"><tr><th>Customer ID</th><th>Template ID</th><th>Channel</th><th>Status</th><th>Retries</th><th>Sent</th></tr></thead>
                <tbody>
                    <?php if (empty($notification_logs)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No notifications logged.</td></tr>
                    <?php else: foreach ($notification_logs as $n): ?>
                        <tr>
                            <td><?php echo htmlspecialchars((string) ($n['customer_id'] ?? '—')); ?></td>
                            <td><?php echo (int) $n['template_id']; ?></td>
                            <td><?php echo htmlspecialchars($n['channel']); ?></td>
                            <td><span class="badge <?php echo $n['status'] === 'SENT' ? 'bg-success' : ($n['status'] === 'FAILED' ? 'bg-danger' : 'bg-warning text-dark'); ?>"><?php echo htmlspecialchars($n['status']); ?></span></td>
                            <td><?php echo (int) $n['retry_count']; ?></td>
                            <td><?php echo htmlspecialchars(date('d-M-Y H:i', strtotime($n['created_at']))); ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="tab-pane fade" id="tab-templates">
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="table-light"><tr><th>Code</th><th>Channel</th><th>Body</th></tr></thead>
                <tbody>
                    <?php if (empty($notification_templates)): ?>
                        <tr><td colspan="3" class="text-center text-muted py-4">No templates configured.</td></tr>
                    <?php else: foreach ($notification_templates as $t): ?>
                        <tr>
                            <td><code><?php echo htmlspecialchars($t['code']); ?></code></td>
                            <td><?php echo htmlspecialchars($t['channel']); ?></td>
                            <td class="small text-muted"><?php echo htmlspecialchars($t['body']); ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="tab-pane fade" id="tab-sync">
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="table-light"><tr><th>User ID</th><th>Entity Type</th><th>Status</th><th>Queued</th></tr></thead>
                <tbody>
                    <?php if (empty($sync_queue)): ?>
                        <tr><td colspan="4" class="text-center text-muted py-4">No sync queue entries.</td></tr>
                    <?php else: foreach ($sync_queue as $s): ?>
                        <tr>
                            <td><?php echo (int) $s['user_id']; ?></td>
                            <td><?php echo htmlspecialchars($s['entity_type']); ?></td>
                            <td><span class="badge <?php echo $s['status'] === 'SYNCED' ? 'bg-success' : ($s['status'] === 'CONFLICT' ? 'bg-danger' : 'bg-warning text-dark'); ?>"><?php echo htmlspecialchars($s['status']); ?></span></td>
                            <td><?php echo htmlspecialchars(date('d-M-Y H:i', strtotime($s['created_at']))); ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</div>
