<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin panel: Loans (list / detail / direct create).
 * Port of App\Http\Livewire\Admin\Loans\Index, Show, and Create.
 *
 * See routes.php: admin/loans (index), admin/loans/create GET (create_form)
 * / POST (store), admin/loans/(:num) (show).
 *
 * `store()` is the most important method here: it ports Loans\Create::save()
 * verbatim — it creates a loan directly with status APPROVED, bypassing the
 * normal Appraiser -> Manager -> Regional Manager maker-checker workflow.
 * This is intentional/audited (see loan_approval_workflows/loan_approval_logs
 * rows it writes with stage ADMIN_DIRECT), not a bug — do not "fix" it.
 */
class Loans extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Loan_model', 'loans');
        $this->load->model('Customer_model', 'customers');
        $this->load->model('Branch_model', 'branches');
        $this->load->model('Loan_product_model', 'loan_products');
        $this->load->model('Jewellery_category_model', 'jewellery_categories');
        $this->load->model('Jewellery_item_model', 'jewellery_items');
        $this->load->model('Gold_rate_model', 'gold_rates');
        $this->load->model('Loan_charge_model', 'loan_charges');
        $this->load->model('Loan_approval_workflow_model', 'loan_approval_workflows');
        $this->load->model('Loan_approval_log_model', 'loan_approval_logs');
        $this->load->model('Loan_disbursement_model', 'loan_disbursements');
        $this->load->model('Interest_collection_model', 'interest_collections');
        $this->load->model('Customer_address_model', 'customer_addresses');
        $this->load->model('Jewellery_valuation_history_model', 'valuation_history');
        $this->load->model('Loan_document_model', 'loan_documents');
    }

    /** GET /admin/loans */
    public function index()
    {
        $search = trim((string) $this->input->get('search'));
        $status = trim((string) $this->input->get('status'));
        $branch_id = trim((string) $this->input->get('branch_id'));
        $page = max(1, (int) $this->input->get('page'));

        $result = $this->loans->admin_search($search, $status, $branch_id, 12, $page);

        $this->render('loans', array(
            'page_title' => 'Loans',
            'loans' => $result['data'],
            'pagination' => $result,
            'filters' => array('search' => $search, 'status' => $status, 'branch_id' => $branch_id),
            'branches' => $this->branches->all(array(), 'name ASC'),
            'statuses' => array(
                'DRAFT', 'PENDING_APPROVAL', 'APPROVED', 'REJECTED', 'DISBURSED',
                'ACTIVE', 'PART_PAID', 'RENEWED', 'SETTLED', 'NPA',
                'AUCTION_ELIGIBLE', 'AUCTIONED', 'CLOSED',
            ),
        ));
    }

    /** GET /admin/loans/(:num) */
    public function show($id)
    {
        $loan = $this->loans->find_with_relations($id);

        if (! $loan) {
            show_404();

            return;
        }

        $this->render('loans_show', array(
            'page_title' => 'Loan ' . $loan['loan_account_number'],
            'loan' => $loan,
            'jewellery_items' => $this->jewellery_items->for_loan($id),
            'disbursements' => $this->loan_disbursements->all(array('loan_id' => $id)),
            'interest_collections' => $this->interest_collections->all(array('loan_id' => $id)),
            'approval_workflow' => $this->loan_approval_workflows->for_loan($id),
            'approval_logs' => $this->loan_approval_logs->for_loan($id),
            'documents' => $this->loan_documents->for_loan($id),
            'can_upload_document' => in_array($this->user['role_code'], array('BRANCH_EXECUTIVE', 'BRANCH_MANAGER'), true),
        ));
    }

    /**
     * GET /admin/loans/(:num)/receipt
     * Printable loan/pledge receipt -- the customer's copy of what was
     * pledged and sanctioned at loan creation. Not a Laravel port; added for
     * the BRD audit's "jewellery receipt" gap (docs/BRD_COVERAGE_AUDIT.md
     * §10 "Show loan agreement, KYC refs, jewellery receipt, documents").
     */
    public function receipt($id)
    {
        $loan = $this->loans->find_with_relations($id);
        if (! $loan) {
            show_404();

            return;
        }

        $this->render('loan_receipt', array(
            'page_title' => 'Pledge Receipt — ' . ($loan['loan_account_number'] ?? 'Loan #' . $id),
            'loan' => $loan,
            'items' => $this->jewellery_items->with_relations_limited(array('jewellery_items.loan_id' => $id), 100),
            'address' => $this->customer_addresses->first(array('customer_id' => $loan['customer_id'])),
            'charges' => $this->loan_charges->all(array('loan_id' => $id)),
        ));
    }

    /**
     * POST /admin/loans/(:num)/document
     * Ports application/controllers/api/v1/Loan_document.php::store() --
     * surfaced here (not a standalone module) since it's always used in the
     * context of one loan already being viewed, and this is what
     * Disbursement::disburse() checks for before allowing disbursement.
     */
    public function upload_document($id)
    {
        if (! $this->require_admin_role(array('BRANCH_EXECUTIVE', 'BRANCH_MANAGER'))) {
            return;
        }

        $loan = $this->loans->find($id);
        if (! $loan) {
            show_404();

            return;
        }

        $document_type = trim((string) $this->input->post('document_type')) ?: 'AGREEMENT';
        if (! in_array($document_type, array('AGREEMENT', 'SANCTION_LETTER', 'OTHER'), true)) {
            return $this->_fail_document($id, 'Document type must be one of AGREEMENT, SANCTION_LETTER, OTHER.');
        }

        if (empty($_FILES['file']) || empty($_FILES['file']['name'])) {
            return $this->_fail_document($id, 'A file is required.');
        }
        if ($_FILES['file']['size'] > 5120 * 1024) {
            return $this->_fail_document($id, 'File must not be greater than 5120 kilobytes.');
        }

        $upload_dir = FCPATH . 'uploads/loan-documents';
        if (! is_dir($upload_dir)) {
            mkdir($upload_dir, 0775, true);
        }

        $this->load->library('upload', array(
            'upload_path' => $upload_dir,
            'allowed_types' => 'pdf|jpg|jpeg|png',
            'max_size' => 5120,
            'encrypt_name' => true,
        ));

        if (! $this->upload->do_upload('file')) {
            return $this->_fail_document($id, $this->upload->display_errors('', ''));
        }

        $uploaded = $this->upload->data();
        $file_ref = 'loan-documents/' . $uploaded['file_name'];

        $document_id = $this->loan_documents->insert(array(
            'loan_id' => $id,
            'document_type' => $document_type,
            'file_ref' => $file_ref,
            'uploaded_by' => $this->user['id'],
        ));

        $this->audit_log('Loan', $id, 'LOAN_DOCUMENT_UPLOAD', null, $this->loan_documents->find($document_id));

        $this->session->set_flashdata('status', 'Document uploaded.');
        redirect('admin/loans/' . $id);
    }

    /** GET /admin/loans/document/(:num) -- gated inline file view */
    public function download_document($document_id)
    {
        $document = $this->loan_documents->find($document_id);
        if (! $document) {
            show_404();

            return;
        }

        $path = FCPATH . 'uploads/' . $document['file_ref'];
        if (! is_file($path)) {
            show_404();

            return;
        }

        $this->audit_log('Loan', $document['loan_id'], 'LOAN_DOCUMENT_VIEW', null, null);

        $mime = function_exists('mime_content_type') ? mime_content_type($path) : 'application/octet-stream';
        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . basename($path) . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    private function _fail_document($loan_id, $message)
    {
        $this->session->set_flashdata('error', $message);
        redirect('admin/loans/' . $loan_id);
    }

    /**
     * GET /admin/loans/create -- restricted: this path bypasses the normal
     * Appraiser -> Manager -> Regional Manager maker-checker workflow (see
     * store() below), so now that every staff role can log into the admin
     * panel, it must stay ADMIN/OPERATIONS only rather than becoming a field
     * role's back door around approval.
     */
    public function create_form()
    {
        if (! $this->require_admin_role(array('OPERATIONS'))) {
            return;
        }

        $this->render('loans_create', array(
            'page_title' => 'New Loan',
            'branches' => $this->branches->all(array(), 'name ASC'),
            'loan_products' => $this->loan_products->all(array('is_active' => 1), 'name ASC'),
            'categories' => $this->jewellery_categories->all(array(), 'name ASC'),
            'old' => array(),
        ));
    }

    /**
     * POST /admin/loans/create
     * Direct admin loan creation — bypasses the maker-checker workflow.
     * Ported verbatim from App\Http\Livewire\Admin\Loans\Create::save().
     */
    public function store()
    {
        if (! $this->require_admin_role(array('OPERATIONS'))) {
            return;
        }

        $customer_mode = $this->input->post('customer_mode') === 'new' ? 'new' : 'existing';
        $branch_id = (string) $this->input->post('branch_id');
        $loan_product_id = (string) $this->input->post('loan_product_id');
        $item_rows = (array) $this->input->post('items');

        $errors = array();

        $branch = $branch_id !== '' ? $this->branches->find($branch_id) : null;
        if (! $branch) {
            $errors[] = 'A valid branch is required.';
        }

        $product = $loan_product_id !== '' ? $this->loan_products->find($loan_product_id) : null;
        if (! $product) {
            $errors[] = 'A valid loan product is required.';
        }

        // Customer inputs
        $customer_search = trim((string) $this->input->post('customer_search'));
        $found_customer = null;
        $new_customer_data = array();

        if ($customer_mode === 'existing') {
            if ($customer_search === '') {
                $errors[] = 'Please provide a customer mobile or code.';
            } else {
                $found_customer = $this->customers->first(array('mobile' => $customer_search, 'deleted_at' => null));
                if (! $found_customer) {
                    $found_customer = $this->customers->first(array('customer_code' => $customer_search, 'deleted_at' => null));
                }
                if (! $found_customer) {
                    $errors[] = 'No customer found with that mobile / code.';
                }
            }
        } else {
            $new_customer_data = array(
                'name' => trim((string) $this->input->post('cust_name')),
                'mobile' => trim((string) $this->input->post('cust_mobile')),
                'email' => trim((string) $this->input->post('cust_email')),
                'dob' => trim((string) $this->input->post('cust_dob')),
                'gender' => trim((string) $this->input->post('cust_gender')),
                'address_line1' => trim((string) $this->input->post('address_line1')),
                'address_city' => trim((string) $this->input->post('address_city')),
                'address_state' => trim((string) $this->input->post('address_state')),
                'address_pincode' => trim((string) $this->input->post('address_pincode')),
            );

            if ($new_customer_data['name'] === '') {
                $errors[] = 'Customer name is required.';
            }
            if ($new_customer_data['mobile'] === '' || strlen($new_customer_data['mobile']) !== 10) {
                $errors[] = 'A valid 10-digit customer mobile is required.';
            }
            if ($new_customer_data['address_line1'] === '' || $new_customer_data['address_city'] === ''
                || $new_customer_data['address_state'] === '' || $new_customer_data['address_pincode'] === '') {
                $errors[] = 'Full address (line 1, city, state, pincode) is required for a new customer.';
            }
        }

        // Jewellery item rows
        if (empty($item_rows)) {
            $errors[] = 'At least one jewellery item is required.';
        }

        $gold_rates_by_karat = array();

        foreach ($item_rows as $row) {
            $category_id = isset($row['category_id']) ? $row['category_id'] : '';
            $purity_karat = isset($row['purity_karat']) ? trim($row['purity_karat']) : '';
            $gross_weight = isset($row['gross_weight']) ? $row['gross_weight'] : '';

            if ($category_id === '' || ! $this->jewellery_categories->find($category_id)) {
                $errors[] = 'Each jewellery item must have a valid category.';
            }
            if ($purity_karat === '') {
                $errors[] = 'Each jewellery item must have a purity (karat).';
            }
            if ($gross_weight === '' || ! is_numeric($gross_weight) || (float) $gross_weight < 0.001) {
                $errors[] = 'Each jewellery item must have a gross weight of at least 0.001g.';
            }

            if ($purity_karat !== '' && ! array_key_exists($purity_karat, $gold_rates_by_karat)) {
                $gold_rates_by_karat[$purity_karat] = $this->gold_rates->latest_approved($purity_karat);

                if (! $gold_rates_by_karat[$purity_karat]) {
                    $errors[] = "No approved gold rate found for {$purity_karat}.";
                }
            }
        }

        if ($errors) {
            $this->session->set_flashdata('error', implode(' ', $errors));
            redirect('admin/loans/create');

            return;
        }

        $admin_id = $this->user['id'];

        $this->db->trans_start();

        // Customer
        if ($customer_mode === 'existing') {
            $customer_id = $found_customer['id'];
        } else {
            $customer_id = $this->customers->insert(array(
                'customer_code' => $this->customers->next_customer_code(),
                'name' => $new_customer_data['name'],
                'mobile' => $new_customer_data['mobile'],
                'email' => $new_customer_data['email'] !== '' ? $new_customer_data['email'] : null,
                'dob' => $new_customer_data['dob'] !== '' ? $new_customer_data['dob'] : null,
                'gender' => $new_customer_data['gender'] !== '' ? $new_customer_data['gender'] : null,
                'branch_id' => $branch_id,
                'registered_by' => $admin_id,
                'kyc_status' => 'PENDING',
            ));

            $this->customer_addresses->insert(array(
                'customer_id' => $customer_id,
                'type' => 'CURRENT',
                'line1' => $new_customer_data['address_line1'],
                'city' => $new_customer_data['address_city'],
                'state' => $new_customer_data['address_state'],
                'pincode' => $new_customer_data['address_pincode'],
            ));
        }

        // Jewellery items
        $item_ids = array();
        $eligible_amount = 0.0;

        foreach ($item_rows as $row) {
            $purity_karat = trim($row['purity_karat']);
            $gold_rate = $gold_rates_by_karat[$purity_karat];

            $gross_weight = (float) $row['gross_weight'];
            $stone_weight = isset($row['stone_weight']) && $row['stone_weight'] !== '' ? (float) $row['stone_weight'] : 0.0;
            $net_weight = $gross_weight - $stone_weight;
            $eligible_percentage = (float) $gold_rate['ltv_pct']; // approved alongside the gold rate, not hardcoded
            $item_eligible_amount = round($net_weight * (float) $gold_rate['rate_per_gram'] * ($eligible_percentage / 100), 2);

            $item_id = $this->jewellery_items->insert(array(
                'barcode' => $this->generate_barcode(),
                'customer_id' => $customer_id,
                'category_id' => $row['category_id'],
                'hallmark_flag' => ! empty($row['hallmark_flag']) ? 1 : 0,
                'gross_weight' => $gross_weight,
                'stone_weight' => $stone_weight,
                // net_weight is NOT inserted -- it's a MySQL generated column
                // (gross_weight - stone_weight) on the live jewellery_items
                // table; an explicit value here would error.
                'purity_karat' => $purity_karat,
                'gold_rate_id' => $gold_rate['id'],
                'applied_rate' => $gold_rate['rate_per_gram'],
                'eligible_percentage' => $eligible_percentage,
                'eligible_amount' => $item_eligible_amount,
                'evaluated_by' => $admin_id,
                'status' => 'EVALUATED',
            ));

            $this->valuation_history->insert(array(
                'jewellery_item_id' => $item_id,
                'gold_rate_id' => $gold_rate['id'],
                'gross_weight' => $gross_weight,
                'stone_weight' => $stone_weight,
                'applied_rate' => $gold_rate['rate_per_gram'],
                'eligible_percentage' => $eligible_percentage,
                'eligible_amount' => $item_eligible_amount,
                'evaluated_by' => $admin_id,
            ));

            $item_ids[] = $item_id;
            $eligible_amount += $item_eligible_amount;
        }

        $processing_fee = round($eligible_amount * ($product['processing_fee_pct'] / 100), 2);
        $gst_amount = round($processing_fee * ($product['gst_pct'] / 100), 2);
        $insurance_amount = round($eligible_amount * ($product['insurance_pct'] / 100), 2);
        $net_disbursed_amount = $eligible_amount - $processing_fee - $gst_amount - $insurance_amount;

        $loan_id = $this->loans->insert(array(
            // loan_account_number is intentionally not set here -- BRD §9
            // "Unique Loan ID created after disbursement" -- it's assigned
            // in Disbursement::disburse() once the loan actually disburses,
            // even for loans created (and auto-approved) directly here.
            'customer_id' => $customer_id,
            'branch_id' => $branch_id,
            'loan_product_id' => $product['id'],
            'eligible_amount' => $eligible_amount,
            'sanctioned_amount' => $eligible_amount,
            'interest_rate_pct' => $product['interest_rate_pct'],
            'processing_fee' => $processing_fee,
            'gst_amount' => $gst_amount,
            'insurance_amount' => $insurance_amount,
            'net_disbursed_amount' => $net_disbursed_amount,
            'loan_date' => date('Y-m-d'),
            'due_date' => date('Y-m-d', strtotime('+' . (int) $product['tenure_months'] . ' months')),
            'status' => 'APPROVED',
            'created_by' => $admin_id,
        ));

        foreach (array(
            array('charge_type' => 'PROCESSING_FEE', 'amount' => $processing_fee),
            array('charge_type' => 'GST', 'amount' => $gst_amount),
            array('charge_type' => 'INSURANCE', 'amount' => $insurance_amount),
        ) as $charge) {
            $this->loan_charges->insert(array_merge($charge, array('loan_id' => $loan_id)));
        }

        $this->jewellery_items->mark_pledged($item_ids, $loan_id);

        $this->loan_approval_workflows->insert(array(
            'loan_id' => $loan_id,
            'current_stage' => 'ADMIN_DIRECT',
            'status' => 'APPROVED',
        ));

        $this->loan_approval_logs->insert(array(
            'loan_id' => $loan_id,
            'stage' => 'ADMIN_DIRECT',
            'action' => 'APPROVE',
            'actioned_by' => $admin_id,
            'remarks' => 'Created and approved directly by admin — maker-checker workflow bypassed.',
        ));

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            $this->session->set_flashdata('error', 'Could not create the loan. Please try again.');
            redirect('admin/loans/create');

            return;
        }

        $this->session->set_flashdata('status', 'Loan #' . $loan_id . ' created and approved. The loan account number will be assigned on disbursement.');
        redirect('admin/loans/' . $loan_id);
    }

    private function generate_barcode()
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code = '';
        for ($i = 0; $i < 10; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return 'JWL' . $code;
    }
}
