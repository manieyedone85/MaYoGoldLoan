<?php
// Layout modeled on docs/receipt.jpg (a reference "Gold Loan Record" mockup) --
// header band with logo/loan id/date, customer details + photos, an ornament
// specifications table, a financial summary, and a terms/declaration block.
// The reference's Tamil legal boilerplate belongs to a different company's
// template and isn't reproduced verbatim; the terms below are original
// generic gold-loan-pledge boilerplate covering the same clauses, offered
// in both English and Tamil (#terms-en / #terms-ta, toggled by printReceipt()
// before window.print() so the printed copy shows only the chosen language).
$first_item = ! empty($items) ? $items[0] : null;
$total_gross = 0.0;
$total_wastage = 0.0;
$total_net = 0.0;
$total_value = 0.0;
foreach ($items as $it) {
    $total_gross += (float) $it['gross_weight'];
    $total_wastage += (float) $it['stone_weight'];
    $total_net += (float) $it['net_weight'];
    //$total_value += (float) $it['net_weight'] * (float) $it['applied_rate'];
    $total_value += (float) $it['eligible_amount'];
}
?>
<a href="<?php echo base_url('admin/loans/' . $loan['id']); ?>" class="btn btn-sm btn-outline-secondary mb-3 no-print"><i class="bi bi-arrow-left"></i> Back to Loan</a>
<button type="button" class="btn btn-sm btn-dark mb-3 no-print" onclick="printReceipt('en')"><i class="bi bi-printer"></i> Print (English)</button>
<button type="button" class="btn btn-sm btn-outline-dark mb-3 no-print" onclick="printReceipt('ta')"><i class="bi bi-printer"></i> Print (Tamil)</button>

<div class="card border-0 shadow-sm mx-auto" style="max-width: 800px;">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start border-bottom border-dark pb-3 mb-3">
            <div class="d-flex align-items-center gap-3">
                <img src="<?php echo base_url('assets/images/logo-aurum.png'); ?>" alt="Aurum Finance" style="height:56px;width:56px;object-fit:contain;">
                <div>
                    <div class="fw-bold fs-5 text-uppercase">Gold Loan Record</div>
                    <div class="small text-muted">
                        <?php echo htmlspecialchars($branch['name'] ?? '—'); ?><?php echo ! empty($branch['city']) ? ', ' . htmlspecialchars($branch['city']) : ''; ?><?php echo ! empty($branch['state']) ? ', ' . htmlspecialchars($branch['state']) : ''; ?>
                        <?php if (! empty($branch['gst_number'])): ?><br>GSTIN: <?php echo htmlspecialchars($branch['gst_number']); ?><?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="text-end">
                <span class="badge bg-dark">Customer Copy</span>
                <div class="mt-2"><strong>Loan ID:</strong> <?php echo htmlspecialchars($loan['loan_account_number'] ?? 'Pending disbursement'); ?></div>
                <div class="small text-muted">Date: <?php echo ! empty($loan['loan_date']) ? htmlspecialchars(date('d-m-Y', strtotime($loan['loan_date']))) : '—'; ?></div>
            </div>
        </div>

        <div class="d-flex align-items-start justify-content-between mb-3" style="gap:16px;">
            <div style="flex: 1 1 auto; min-width: 0;">
                <div class="fw-semibold text-uppercase small text-muted mb-2">Customer Details</div>
                <div class="fw-bold"><?php echo htmlspecialchars($customer['name'] ?? $loan['customer_name'] ?? '—'); ?></div>
                <div class="small"  style="font-size: 12px;">Cust ID: <?php echo htmlspecialchars($customer['customer_code'] ?? '—'); ?></div>
                <div class="small" style="font-size: 12px;">Mobile: <?php echo htmlspecialchars($customer['mobile'] ?? $loan['customer_mobile'] ?? '—'); ?></div>
                <div class="small"  style="font-size: 12px;">
                    Address:
                    <?php if ($address): ?>
                        <?php echo htmlspecialchars(implode(', ', array_filter(array($address['line1'], $address['city'], $address['state'], $address['pincode'])))); ?>
                    <?php else: ?>—<?php endif; ?>
                </div>
            </div>
            <div class="d-flex" style="flex: 0 0 auto; gap:10px;">
                <div class="text-center" style="width:80px;">
                    <?php if (! empty($customer['photo_path'])): ?>
                        <img src="<?php echo base_url('admin/customers/' . $customer['id'] . '/photo'); ?>" alt="Customer" style="display:block;height:96px;width:80px;object-fit:cover;" class="border rounded">
                    <?php else: ?>
                        <div class="border rounded d-flex align-items-center justify-content-center text-muted small" style="height:96px;width:80px;">No Photo</div>
                    <?php endif; ?>
                    <div class="small text-muted mt-1">Customer</div>
                </div>
                <div class="text-center" style="width:80px;">
                    <?php if (! empty($jewellery_photos)): ?>
                        <img src="<?php echo base_url('admin/loans/document/' . $jewellery_photos[0]['id']); ?>" alt="Asset" style="display:block;height:96px;width:80px;object-fit:cover;" class="border rounded">
                    <?php else: ?>
                        <div class="border rounded d-flex align-items-center justify-content-center text-muted small" style="height:96px;width:80px;">No Photo</div>
                    <?php endif; ?>
                    <div class="small text-muted mt-1">Asset</div>
                </div>
            </div>
        </div>

        <div class="fw-semibold text-uppercase small text-muted mb-2">Ornament Specifications</div>
        <table class="table table-sm table-bordered mb-3" style="font-size: 14px;">
            <thead class="table-light">
                <tr><th>Description</th><th class="text-end">Qty</th><th>Karat</th><th class="text-end">Gross Wt.</th><th class="text-end">Wastage</th><th class="text-end">Net Wt.</th><th class="text-end">Value</th></tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                    <tr><td colspan="7" class="text-center text-muted">No jewellery items.</td></tr>
                <?php else: foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['category_name'] ?? '—'); ?></td>
                        <td class="text-end">1</td>
                        <td><?php echo htmlspecialchars($item['purity_karat']); ?></td>
                        <td class="text-end"><?php echo number_format($item['gross_weight'], 3); ?>g</td>
                        <td class="text-end"><?php echo number_format($item['stone_weight'], 3); ?>g</td>
                        <td class="text-end"><?php echo number_format($item['net_weight'], 3); ?>g</td>
                        <td class="text-end">₹<?php echo number_format($item['eligible_amount'] , 2); ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
            <?php if (! empty($items)): ?>
            <tfoot>
                <tr class="fw-semibold table-light">
                    <td colspan="3">Total</td>
                    <td class="text-end"><?php echo number_format($total_gross, 3); ?>g</td>
                    <td class="text-end"><?php echo number_format($total_wastage, 3); ?>g</td>
                    <td class="text-end"><?php echo number_format($total_net, 3); ?>g</td>
                    <td class="text-end">₹<?php echo number_format($total_value, 2); ?></td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>

        <div class="fw-semibold text-uppercase small text-muted mb-2">Financial Summary</div>
        <table class="table table-sm table-bordered mb-3" style="max-width: 480px; font-size: 14px;">
            <tbody>
                <tr><td>Loan Scheme</td><td class="text-end"><?php echo htmlspecialchars($loan['product_name'] ?? '—'); ?> — <?php echo htmlspecialchars($loan['interest_rate_pct']); ?>%</td></tr>
                <tr><td>Gold Rate</td><td class="text-end"><?php echo $first_item ? '₹' . number_format($first_item['applied_rate'], 2) . '/g' : '—'; ?></td></tr>
                <!--<tr><td>Eligible Amount</td><td class="text-end">₹<?php echo number_format($loan['eligible_amount'], 2); ?></td></tr>-->
                <!--<tr><td class="fw-semibold">Loan (Sanctioned) Amount</td><td class="text-end fw-semibold">₹<?php //echo number_format($loan['sanctioned_amount'], 2); ?></td></tr>
                <tr><td>Processing Fee</td><td class="text-end">₹<?php echo number_format((float) $loan['processing_fee'], 2); ?></td></tr>
                <tr><td>GST</td><td class="text-end">₹<?php echo number_format((float) $loan['gst_amount'], 2); ?></td></tr>
                <tr><td>Insurance</td><td class="text-end">₹<?php echo number_format((float) $loan['insurance_amount'], 2); ?></td></tr>-->
                <tr class="fw-semibold"><td>Net Disbursed Amount</td><td class="text-end">₹<?php echo number_format((float) $loan['net_disbursed_amount'], 2); ?></td></tr>
                <!--<tr><td>Due Date</td><td class="text-end"><?php echo ! empty($loan['due_date']) ? htmlspecialchars(date('d-m-Y', strtotime($loan['due_date']))) : '—'; ?></td></tr>-->
            </tbody>
        </table>

        <div id="terms-en">
            <div class="fw-semibold text-uppercase small text-muted mb-2">Terms &amp; Conditions</div>
            <div class="text-muted" style="font-size: 10px; line-height: 1.6;">
                <ol class="mb-2">
                    <li>I confirm that the pledged gold ornament(s) described above belong to me and are pledged as security against the loan amount received.</li>
                    <li>I authorize the branch to contact me at the address and mobile number provided, and to notify me of any change through the same.</li>
                    <li>The maximum tenure of this pledge is <?php echo (int) ($loan_product['tenure_months'] ?? 0); ?> month(s) from the loan date, as per the selected scheme.</li>
                    <li>Within the tenure, the pledged ornament(s) must be redeemed in full, or renewed by paying the accrued interest, failing which the ornament(s) become eligible for auction as per policy.</li>
                    <li>Interest must be paid within the due date specified; the applicable interest rate is subject to change only per revised, duly approved scheme terms.</li>
                    <li>Government holidays and public holidays do not extend the due date.</li>
                </ol>
                <div>I have read and agree to the above terms and conditions.</div>
            </div>
        </div>

        <div id="terms-ta" class="d-none">
            <div class="fw-semibold text-uppercase small text-muted mb-2">விதிமுறைகள் மற்றும் நிபந்தனைகள்</div>
            <div class="text-muted" style="font-size: 9px; line-height: 1.6;">
                <ol class="mb-2">
                    <li>மேலே குறிப்பிடப்பட்டுள்ள அடகு வைக்கப்பட்ட தங்க நகை(கள்) எனக்கு சொந்தமானது என்றும், பெறப்பட்ட கடன் தொகைக்கு பிணையமாக அடகு வைக்கப்பட்டுள்ளது என்றும் நான் உறுதிப்படுத்துகிறேன்.</li>
                    <li>வழங்கப்பட்ட முகவரி மற்றும் மொபைல் எண்ணில் என்னைத் தொடர்பு கொள்ளவும், ஏதேனும் மாற்றத்தை அதே வழியில் எனக்கு அறிவிக்கவும் கிளைக்கு நான் அனுமதி அளிக்கிறேன்.</li>
                    <li>தேர்ந்தெடுக்கப்பட்ட திட்டத்தின்படி, இந்த அடகு வைப்பின் அதிகபட்ச காலஅளவு கடன் தேதியிலிருந்து <?php echo (int) ($loan_product['tenure_months'] ?? 0); ?> மாதம்(கள்) ஆகும்.</li>
                    <li>காலஅளவுக்குள், அடகு வைக்கப்பட்ட நகை(கள்) முழுமையாக மீட்கப்பட வேண்டும் அல்லது சேர்ந்த வட்டியைச் செலுத்தி புதுப்பிக்கப்பட வேண்டும்; இல்லையெனில், கொள்கையின்படி நகை(கள்) ஏலத்திற்கு உட்படுத்தப்படும்.</li>
                    <li>குறிப்பிட்ட தவணை தேதிக்குள் வட்டி செலுத்தப்பட வேண்டும்; பொருந்தும் வட்டி விகிதம் திருத்தப்பட்ட, முறையாக அங்கீகரிக்கப்பட்ட திட்ட விதிமுறைகளின்படி மட்டுமே மாற்றப்படும்.</li>
                    <li>அரசு விடுமுறை நாட்கள் மற்றும் பொது விடுமுறை நாட்கள் தவணை தேதியை நீட்டிக்காது.</li>
                </ol>
                <div>மேலே கூறப்பட்ட விதிமுறைகள் மற்றும் நிபந்தனைகளை நான் படித்து ஒப்புக்கொள்கிறேன்.</div>
            </div>
        </div>

        <div class="row g-3 mt-4 pt-2">
            <div class="col-6 text-center border-top pt-2">Customer Signature</div>
            <div class="col-6 text-center border-top pt-2">Authorized Signatory</div>
        </div>
    </div>
</div>

<script>
    function printReceipt(lang) {
        var en = document.getElementById('terms-en');
        var ta = document.getElementById('terms-ta');
        if (lang === 'ta') {
            en.classList.add('d-none');
            ta.classList.remove('d-none');
        } else {
            ta.classList.add('d-none');
            en.classList.remove('d-none');
        }
        window.print();
    }
</script>
