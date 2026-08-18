<a href="<?php echo base_url('admin/auctions'); ?>" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Back to Auctions</a>

<?php $CI =& get_instance(); $error = $CI->session->flashdata('error'); ?>
<?php if (! empty($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<p class="mb-3">Auction date <strong><?php echo htmlspecialchars(date('d-M-Y', strtotime($schedule['auction_date']))); ?></strong> — status <span class="badge bg-secondary"><?php echo htmlspecialchars($schedule['status']); ?></span></p>

<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-notices" type="button">Notices</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-bidders" type="button">Bidders</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-bids" type="button">Bids</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-winners" type="button">Winners</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-settle" type="button">Settle</button></li>
</ul>

<div class="tab-content">

<div class="tab-pane fade show active" id="tab-notices">
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="POST" action="<?php echo base_url('admin/auctions/' . $schedule['id'] . '/notice'); ?>" class="row g-2">
                <div class="col-md-4"><input type="number" name="loan_id" class="form-control" placeholder="Loan ID" required></div>
                <div class="col-md-4">
                    <select name="channel" class="form-select" required>
                        <option value="SMS">SMS</option>
                        <option value="EMAIL">Email</option>
                        <option value="POST">Post</option>
                    </select>
                </div>
                <div class="col-md-4"><button type="submit" class="btn btn-dark w-100">Send Notice</button></div>
            </form>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="table-light"><tr><th>Loan ID</th><th>Channel</th><th>Sent At</th></tr></thead>
                <tbody>
                    <?php if (empty($notices)): ?>
                        <tr><td colspan="3" class="text-center text-muted py-3">No notices sent.</td></tr>
                    <?php else: foreach ($notices as $n): ?>
                        <tr><td><?php echo (int) $n['loan_id']; ?></td><td><?php echo htmlspecialchars($n['channel']); ?></td><td><?php echo htmlspecialchars($n['sent_at']); ?></td></tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="tab-pane fade" id="tab-bidders">
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="POST" action="<?php echo base_url('admin/auctions/' . $schedule['id'] . '/bidder'); ?>" class="row g-2">
                <div class="col-md-4"><input type="text" name="name" class="form-control" placeholder="Name" required></div>
                <div class="col-md-4"><input type="text" name="mobile" class="form-control" placeholder="Mobile" required></div>
                <div class="col-md-3"><input type="text" name="id_proof_number" class="form-control" placeholder="ID proof # (optional)"></div>
                <div class="col-md-1"><button type="submit" class="btn btn-dark w-100">Add</button></div>
            </form>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="table-light"><tr><th>ID</th><th>Name</th><th>Mobile</th><th>ID Proof</th></tr></thead>
                <tbody>
                    <?php if (empty($bidders)): ?>
                        <tr><td colspan="4" class="text-center text-muted py-3">No bidders yet.</td></tr>
                    <?php else: foreach ($bidders as $b): ?>
                        <tr><td><?php echo (int) $b['id']; ?></td><td><?php echo htmlspecialchars($b['name']); ?></td><td><?php echo htmlspecialchars($b['mobile']); ?></td><td><?php echo htmlspecialchars($b['id_proof_number'] ?? '—'); ?></td></tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="tab-pane fade" id="tab-bids">
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="POST" action="<?php echo base_url('admin/auctions/' . $schedule['id'] . '/bid'); ?>" class="row g-2">
                <div class="col-md-4"><input type="number" name="gold_packet_id" class="form-control" placeholder="Gold Packet ID" required></div>
                <div class="col-md-4"><input type="number" name="bidder_id" class="form-control" placeholder="Bidder ID" required></div>
                <div class="col-md-3"><input type="number" step="0.01" name="bid_amount" class="form-control" placeholder="Bid Amount" required></div>
                <div class="col-md-1"><button type="submit" class="btn btn-dark w-100">Bid</button></div>
            </form>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="table-light"><tr><th>Gold Packet ID</th><th>Bidder ID</th><th>Bid Amount</th></tr></thead>
                <tbody>
                    <?php if (empty($bids)): ?>
                        <tr><td colspan="3" class="text-center text-muted py-3">No bids yet.</td></tr>
                    <?php else: foreach ($bids as $bid): ?>
                        <tr><td><?php echo (int) $bid['gold_packet_id']; ?></td><td><?php echo (int) $bid['bidder_id']; ?></td><td>₹<?php echo number_format($bid['bid_amount'], 2); ?></td></tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="tab-pane fade" id="tab-winners">
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="POST" action="<?php echo base_url('admin/auctions/' . $schedule['id'] . '/winner'); ?>" class="row g-2">
                <div class="col-md-9"><input type="number" name="gold_packet_id" class="form-control" placeholder="Gold Packet ID" required></div>
                <div class="col-md-3"><button type="submit" class="btn btn-dark w-100">Declare Winner (top bid)</button></div>
            </form>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="table-light"><tr><th>Packet</th><th>Bidder</th><th>Winning Amount</th></tr></thead>
                <tbody>
                    <?php if (empty($winners)): ?>
                        <tr><td colspan="3" class="text-center text-muted py-3">No winners declared yet.</td></tr>
                    <?php else: foreach ($winners as $w): ?>
                        <tr><td><?php echo htmlspecialchars($w['packet_code'] ?? '—'); ?></td><td><?php echo htmlspecialchars($w['bidder_name'] ?? '—'); ?></td><td>₹<?php echo number_format($w['winning_amount'], 2); ?></td></tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="tab-pane fade" id="tab-settle">
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="<?php echo base_url('admin/auctions/' . $schedule['id'] . '/settle'); ?>" class="row g-3">
                <div class="col-md-6"><label class="form-label">Loan ID</label><input type="number" name="loan_id" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">Gold Packet ID</label><input type="number" name="gold_packet_id" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">Outstanding Loan Amount</label><input type="number" step="0.01" name="outstanding_loan_amount" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">Auction Amount</label><input type="number" step="0.01" name="auction_amount" class="form-control" required></div>
                <div class="col-12"><button type="submit" class="btn btn-dark">Settle Auction</button></div>
            </form>
        </div>
    </div>
</div>

</div>
