<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin panel: Customers.
 * Port of App\Http\Livewire\Admin\Customers\Index.
 *
 * Livewire's "view" modal becomes its own page (GET /admin/customers/(:num))
 * since that's how routes.php already wires it. See routes.php:
 * admin/customers (index), admin/customers/(:num) (show),
 * admin/customers/(:num)/blacklist (toggle_blacklist).
 */
class Customers extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Customer_model', 'customers');
        $this->load->model('Loan_model', 'loans');
        $this->load->model('Kyc_document_model', 'kyc_documents');
        $this->load->model('Kyc_aadhaar_verification_model', 'aadhaar_verifications');
        $this->load->model('Kyc_pan_verification_model', 'pan_verifications');
    }

    /** GET /admin/customers */
    public function index()
    {
        $search = trim((string) $this->input->get('search'));
        $kyc_status = trim((string) $this->input->get('kyc_status'));
        $page = max(1, (int) $this->input->get('page'));

        $result = $this->customers->admin_list($search, $kyc_status, 10, $page);

        $this->render('customers', array(
            'page_title' => 'Customers',
            'customers' => $result['data'],
            'pagination' => $result,
            'filters' => array('search' => $search, 'kyc_status' => $kyc_status),
        ));
    }

    /** GET /admin/customers/(:num) */
    public function show($id)
    {
        $customer = $this->customers->with_branch($id);

        if (! $customer) {
            show_404();

            return;
        }

        $loans = $this->loans->with_relations(array('loans.customer_id' => $id));

        $this->render('customer_show', array(
            'page_title' => $customer['name'] . ' (' . $customer['customer_code'] . ')',
            'customer' => $customer,
            'loans' => $loans,
            'kyc_documents' => $this->kyc_documents->for_customer($id),
            'aadhaar_verifications' => $this->aadhaar_verifications->for_customer($id),
            'pan_verifications' => $this->pan_verifications->for_customer($id),
        ));
    }

    /** POST /admin/customers/(:num)/blacklist -- restricted: blacklisting affects a customer's ability to borrow anywhere, not a front-desk action. */
    public function toggle_blacklist($id)
    {
        if (! $this->require_admin_role(array('BRANCH_MANAGER', 'REGIONAL_MANAGER', 'OPERATIONS'))) {
            return;
        }

        $customer = $this->customers->find($id);

        if (! $customer) {
            show_404();

            return;
        }

        $this->customers->update($id, array('is_blacklisted' => $customer['is_blacklisted'] ? 0 : 1));

        $this->session->set_flashdata('status', 'Customer blacklist status updated.');
        redirect('admin/customers');
    }
}
