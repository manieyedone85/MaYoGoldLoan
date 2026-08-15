<?php $CI =& get_instance(); $error = $CI->session->flashdata('error'); ?>

<a href="<?php echo base_url('admin/loans'); ?>" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Back to Loans</a>

<div class="alert alert-warning small">
    <i class="bi bi-exclamation-triangle"></i> This creates the loan with status <strong>APPROVED</strong> immediately — the normal Appraiser &rarr; Manager &rarr; Regional Manager maker-checker workflow is bypassed. Use only for admin-authorized direct entries.
</div>

<?php if (! empty($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="POST" action="<?php echo base_url('admin/loans/create'); ?>" id="loanCreateForm">
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white fw-semibold">Customer</div>
        <div class="card-body">
            <div class="btn-group mb-3" role="group">
                <input type="radio" class="btn-check" name="customer_mode" id="mode_existing" value="existing" checked>
                <label class="btn btn-sm btn-outline-dark" for="mode_existing">Existing Customer</label>
                <input type="radio" class="btn-check" name="customer_mode" id="mode_new" value="new">
                <label class="btn btn-sm btn-outline-dark" for="mode_new">New Customer</label>
            </div>

            <div id="existingCustomerFields" class="row g-2 align-items-end">
                <div class="col-md-9">
                    <label class="form-label">Mobile or Customer Code</label>
                    <input type="text" name="customer_search" class="form-control">
                    <div class="form-text">The exact mobile number or customer code of an existing customer.</div>
                </div>
            </div>

            <div id="newCustomerFields" class="row g-3" style="display:none;">
                <div class="col-md-4">
                    <label class="form-label">Name</label>
                    <input type="text" name="cust_name" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Mobile</label>
                    <input type="text" name="cust_mobile" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email</label>
                    <input type="email" name="cust_email" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="cust_dob" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Gender</label>
                    <select name="cust_gender" class="form-select">
                        <option value="">Select</option>
                        <option value="MALE">Male</option>
                        <option value="FEMALE">Female</option>
                        <option value="OTHER">Other</option>
                    </select>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Address Line 1</label>
                    <input type="text" name="address_line1" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">City</label>
                    <input type="text" name="address_city" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">State</label>
                    <input type="text" name="address_state" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Pincode</label>
                    <input type="text" name="address_pincode" class="form-control">
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
                            <option value="<?php echo (int) $branch['id']; ?>"><?php echo htmlspecialchars($branch['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Loan Product</label>
                    <select name="loan_product_id" class="form-select">
                        <option value="">Select product</option>
                        <?php foreach ($loan_products as $product): ?>
                            <option value="<?php echo (int) $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?> (<?php echo htmlspecialchars($product['interest_rate_pct']); ?>% / <?php echo (int) $product['tenure_months']; ?>mo)</option>
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
            <div class="row g-2 align-items-end border-bottom pb-3 mb-3 item-row">
                <div class="col-md-3">
                    <label class="form-label small">Category</label>
                    <select name="items[0][category_id]" class="form-select form-select-sm">
                        <option value="">Select</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo (int) $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Purity (e.g. 22K)</label>
                    <input type="text" name="items[0][purity_karat]" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Gross Weight (g)</label>
                    <input type="number" step="0.001" name="items[0][gross_weight]" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Stone Weight (g)</label>
                    <input type="number" step="0.001" name="items[0][stone_weight]" class="form-control form-control-sm">
                </div>
                <div class="col-md-2 form-check mb-2">
                    <input type="checkbox" name="items[0][hallmark_flag]" value="1" class="form-check-input">
                    <label class="form-check-label small">Hallmarked</label>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn" style="display:none;"><i class="bi bi-trash"></i></button>
                </div>
            </div>
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
    var itemIndex = 1;

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
