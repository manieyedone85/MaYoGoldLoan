<?php
$CI =& get_instance();
$error = $CI->session->flashdata('error');
$old = isset($old) && is_array($old) ? $old : array();
$customer_mode = isset($old['customer_mode']) && $old['customer_mode'] === 'new' ? 'new' : 'existing';
$old_items = ! empty($old['items']) && is_array($old['items']) ? array_values($old['items']) : array(array());
?>

<a href="<?php echo base_url('admin/loans'); ?>" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Back to Loans</a>

<div class="alert alert-warning small">
    <i class="bi bi-exclamation-triangle"></i> This creates the loan with status <strong>APPROVED</strong> immediately — the normal Appraiser &rarr; Manager &rarr; Regional Manager maker-checker workflow is bypassed. Use only for admin-authorized direct entries.
</div>

<?php if (! empty($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="POST" action="<?php echo base_url('admin/loans/create'); ?>" id="loanCreateForm" enctype="multipart/form-data">
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white fw-semibold">Customer</div>
        <div class="card-body">
            <div class="btn-group mb-3" role="group">
                <input type="radio" class="btn-check" name="customer_mode" id="mode_existing" value="existing" <?php echo $customer_mode === 'existing' ? 'checked' : ''; ?>>
                <label class="btn btn-sm btn-outline-dark" for="mode_existing">Existing Customer</label>
                <input type="radio" class="btn-check" name="customer_mode" id="mode_new" value="new" <?php echo $customer_mode === 'new' ? 'checked' : ''; ?>>
                <label class="btn btn-sm btn-outline-dark" for="mode_new">New Customer</label>
            </div>

            <div id="existingCustomerFields" class="row g-2 align-items-end" style="<?php echo $customer_mode === 'existing' ? '' : 'display:none;'; ?>">
                <div class="col-md-9">
                    <label class="form-label">Mobile or Customer Code</label>
                    <input type="text" name="customer_search" class="form-control" value="<?php echo htmlspecialchars(isset($old['customer_search']) ? $old['customer_search'] : ''); ?>">
                    <div class="form-text">The exact mobile number or customer code of an existing customer.</div>
                </div>
            </div>

            <div id="newCustomerFields" class="row g-3" style="<?php echo $customer_mode === 'new' ? '' : 'display:none;'; ?>">
                <div class="col-md-4">
                    <label class="form-label">Name</label>
                    <input type="text" name="cust_name" class="form-control" value="<?php echo htmlspecialchars(isset($old['cust_name']) ? $old['cust_name'] : ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Mobile</label>
                    <input type="text" name="cust_mobile" class="form-control" value="<?php echo htmlspecialchars(isset($old['cust_mobile']) ? $old['cust_mobile'] : ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email</label>
                    <input type="email" name="cust_email" class="form-control" value="<?php echo htmlspecialchars(isset($old['cust_email']) ? $old['cust_email'] : ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="cust_dob" class="form-control" value="<?php echo htmlspecialchars(isset($old['cust_dob']) ? $old['cust_dob'] : ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Gender</label>
                    <select name="cust_gender" class="form-select">
                        <option value="">Select</option>
                        <?php foreach (array('MALE' => 'Male', 'FEMALE' => 'Female', 'OTHER' => 'Other') as $value => $label): ?>
                            <option value="<?php echo $value; ?>" <?php echo (isset($old['cust_gender']) && $old['cust_gender'] === $value) ? 'selected' : ''; ?>><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Father's Name</label>
                    <input type="text" name="cust_father_name" class="form-control" value="<?php echo htmlspecialchars(isset($old['cust_father_name']) ? $old['cust_father_name'] : ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Profession Type</label>
                    <select name="cust_profession_type" class="form-select">
                        <option value="">Select</option>
                        <?php foreach (array('SALARIED', 'SELF_EMPLOYED', 'BUSINESS', 'AGRICULTURE', 'RETIRED', 'OTHER') as $value): ?>
                            <option value="<?php echo $value; ?>" <?php echo (isset($old['cust_profession_type']) && $old['cust_profession_type'] === $value) ? 'selected' : ''; ?>><?php echo ucwords(strtolower(str_replace('_', ' ', $value))); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Profession Details</label>
                    <input type="text" name="cust_profession_details" class="form-control" placeholder="e.g. employer / business name" value="<?php echo htmlspecialchars(isset($old['cust_profession_details']) ? $old['cust_profession_details'] : ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Monthly Income (₹)</label>
                    <input type="number" step="0.01" min="0" name="cust_income" class="form-control" value="<?php echo htmlspecialchars(isset($old['cust_income']) ? $old['cust_income'] : ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Photo</label>
                    <input type="file" name="cust_photo" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                </div>
                <div class="col-md-8">
                    <label class="form-label">Address Line 1</label>
                    <input type="text" name="address_line1" class="form-control" placeholder="Enter Door Number, Street Name, Village" value="<?php echo htmlspecialchars(isset($old['address_line1']) ? $old['address_line1'] : ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">City</label>
                    <input type="text" name="address_city" class="form-control" placeholder="Enter City" value="<?php echo htmlspecialchars(isset($old['address_city']) ? $old['address_city'] : ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">State</label>
                    <input type="text" name="address_state" class="form-control" placeholder="Enter State" value="<?php echo htmlspecialchars(isset($old['address_state']) ? $old['address_state'] : ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Pincode</label>
                    <input type="text" name="address_pincode" class="form-control" placeholder="Enter Pincode" value="<?php echo htmlspecialchars(isset($old['address_pincode']) ? $old['address_pincode'] : ''); ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white fw-semibold">Nominee <span class="text-muted fw-normal small">(optional)</span></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Name</label>
                    <input type="text" name="nominee_name" class="form-control" value="<?php echo htmlspecialchars(isset($old['nominee_name']) ? $old['nominee_name'] : ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Relation</label>
                    <input type="text" name="nominee_relation" class="form-control" placeholder="e.g. Spouse, Son, Daughter" value="<?php echo htmlspecialchars(isset($old['nominee_relation']) ? $old['nominee_relation'] : ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Mobile</label>
                    <input type="text" name="nominee_mobile" class="form-control" value="<?php echo htmlspecialchars(isset($old['nominee_mobile']) ? $old['nominee_mobile'] : ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">ID Proof Type</label>
                    <input type="text" name="nominee_id_proof_type" class="form-control" placeholder="e.g. AADHAAR, PAN" value="<?php echo htmlspecialchars(isset($old['nominee_id_proof_type']) ? $old['nominee_id_proof_type'] : ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">ID Proof Number</label>
                    <input type="text" name="nominee_id_proof_number" class="form-control" value="<?php echo htmlspecialchars(isset($old['nominee_id_proof_number']) ? $old['nominee_id_proof_number'] : ''); ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white fw-semibold">Loan Details</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Branch</label>
                    <select name="branch_id" class="form-select">
                        <option value="">Select branch</option>
                        <?php foreach ($branches as $branch): ?>
                            <option value="<?php echo (int) $branch['id']; ?>" <?php echo (isset($old['branch_id']) && (string) $old['branch_id'] === (string) $branch['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($branch['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Loan Product</label>
                    <select name="loan_product_id" class="form-select">
                        <option value="">Select product</option>
                        <?php foreach ($loan_products as $product): ?>
                            <option value="<?php echo (int) $product['id']; ?>" <?php echo (isset($old['loan_product_id']) && (string) $old['loan_product_id'] === (string) $product['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($product['name']); ?> (<?php echo htmlspecialchars($product['interest_rate_pct']); ?>% / <?php echo (int) $product['tenure_months']; ?>mo)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Jewellery Items</span>
            <button type="button" class="btn btn-sm btn-outline-dark" id="addItemBtn"><i class="bi bi-plus-lg"></i> Add Item</button>
        </div>
        <div class="card-body" id="itemRows">
            <?php foreach ($old_items as $idx => $item): ?>
            <div class="row g-2 align-items-end border-bottom pb-3 mb-3 item-row">
                <div class="col-md-3">
                    <label class="form-label small">Category</label>
                    <select name="items[<?php echo (int) $idx; ?>][category_id]" class="form-select form-select-sm">
                        <option value="">Select</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo (int) $category['id']; ?>" <?php echo (isset($item['category_id']) && (string) $item['category_id'] === (string) $category['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Purity (e.g. 22K)</label>
                    <input type="text" name="items[<?php echo (int) $idx; ?>][purity_karat]" class="form-control form-control-sm" value="<?php echo htmlspecialchars(isset($item['purity_karat']) ? $item['purity_karat'] : ''); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Gross Weight (g)</label>
                    <input type="number" step="0.001" name="items[<?php echo (int) $idx; ?>][gross_weight]" class="form-control form-control-sm" value="<?php echo htmlspecialchars(isset($item['gross_weight']) ? $item['gross_weight'] : ''); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Stone Weight (g)</label>
                    <input type="number" step="0.001" name="items[<?php echo (int) $idx; ?>][stone_weight]" class="form-control form-control-sm" value="<?php echo htmlspecialchars(isset($item['stone_weight']) ? $item['stone_weight'] : ''); ?>">
                </div>
                <div class="col-md-2 form-check mb-2">
                    <input type="checkbox" name="items[<?php echo (int) $idx; ?>][hallmark_flag]" value="1" class="form-check-input" <?php echo ! empty($item['hallmark_flag']) ? 'checked' : ''; ?>>
                    <label class="form-check-label small">Hallmarked</label>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn" style="<?php echo count($old_items) > 1 ? '' : 'display:none;'; ?>"><i class="bi bi-trash"></i></button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white fw-semibold">Jewellery Photos</div>
        <div class="card-body">
            <label class="form-label">Photos of the pledged jewellery</label>
            <input type="file" name="jewellery_photos[]" class="form-control" accept=".jpg,.jpeg,.png,.gif,.webp" multiple>
            <div class="form-text">Optional. One or more photos covering the whole pledge (not per item) -- stored against the loan, viewable from the loan's Documents section.</div>
        </div>
    </div>

    <div class="text-end">
        <button type="submit" class="btn btn-dark btn-lg">Create &amp; Approve Loan</button>
    </div>
</form>

<template id="itemRowTemplate">
    <div class="row g-2 align-items-end border-bottom pb-3 mb-3 item-row">
        <div class="col-md-3">
            <label class="form-label small">Category</label>
            <select name="items[__INDEX__][category_id]" class="form-select form-select-sm">
                <option value="">Select</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo (int) $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small">Purity (e.g. 22K)</label>
            <input type="text" name="items[__INDEX__][purity_karat]" class="form-control form-control-sm">
        </div>
        <div class="col-md-2">
            <label class="form-label small">Gross Weight (g)</label>
            <input type="number" step="0.001" name="items[__INDEX__][gross_weight]" class="form-control form-control-sm">
        </div>
        <div class="col-md-2">
            <label class="form-label small">Stone Weight (g)</label>
            <input type="number" step="0.001" name="items[__INDEX__][stone_weight]" class="form-control form-control-sm">
        </div>
        <div class="col-md-2 form-check mb-2">
            <input type="checkbox" name="items[__INDEX__][hallmark_flag]" value="1" class="form-check-input">
            <label class="form-check-label small">Hallmarked</label>
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn"><i class="bi bi-trash"></i></button>
        </div>
    </div>
</template>

<script>
(function () {
    var existingFields = document.getElementById('existingCustomerFields');
    var newFields = document.getElementById('newCustomerFields');
    document.getElementById('mode_existing').addEventListener('change', function () {
        existingFields.style.display = '';
        newFields.style.display = 'none';
    });
    document.getElementById('mode_new').addEventListener('change', function () {
        existingFields.style.display = 'none';
        newFields.style.display = '';
    });

    var itemRows = document.getElementById('itemRows');
    var template = document.getElementById('itemRowTemplate');
    var itemIndex = <?php echo (int) count($old_items); ?>;

    document.getElementById('addItemBtn').addEventListener('click', function () {
        var html = template.innerHTML.split('__INDEX__').join(itemIndex);
        var wrapper = document.createElement('div');
        wrapper.innerHTML = html.trim();
        itemRows.appendChild(wrapper.firstChild);
        itemIndex++;
        toggleRemoveButtons();
    });

    itemRows.addEventListener('click', function (e) {
        var btn = e.target.closest('.remove-item-btn');
        if (! btn) {
            return;
        }
        var row = btn.closest('.item-row');
        row.parentNode.removeChild(row);
        toggleRemoveButtons();
    });

    function toggleRemoveButtons() {
        var rows = itemRows.querySelectorAll('.item-row');
        rows.forEach(function (row, idx) {
            var removeBtn = row.querySelector('.remove-item-btn');
            removeBtn.style.display = rows.length > 1 ? '' : 'none';
        });
    }
})();
</script>
