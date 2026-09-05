<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Customer_model', 'customers');
        $this->load->model('Loan_model', 'loans');
    }

    /** GET /admin/dashboard */
    public function index()
    {
        $this->load->model('Role_model', 'roles');
        $customer_role = $this->roles->find_by_code('CUSTOMER');

        $employee_count = $this->db->from('user_master')->where('is_super_admin', 0);
        if ($customer_role) {
            $employee_count->where('role_id !=', $customer_role['id']);
        }
        $employee_count = $employee_count->count_all_results();

        $stats = array(
            'employees' => $employee_count,
            'customers' => $this->customers->count(),
            'active_loans' => $this->loans->count(array('status' => 'ACTIVE')),
            'pending_approval' => $this->loans->count(array('status' => 'PENDING_APPROVAL')),
            'npa_loans' => $this->loans->count(array('status' => 'NPA')),
        );

        $recent_loans = $this->loans->recent_with_relations(8);

        $this->render('dashboard', array(
            'page_title' => 'Dashboard',
            'stats' => $stats,
            'recent_loans' => $recent_loans,
        ));
    }
}
