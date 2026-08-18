<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin panel: Gold Release checklist. Ports
 * application/controllers/api/v1/Gold_release.php's verify_id()/
 * capture_signature()/complete() -- same firstOrCreate-per-item release row,
 * same "loan must be SETTLED/CLOSED" + all-three-gates-true guard before
 * complete() will release.
 *
 * Adds one screen the API itself has no equivalent for: a "Capture Photo"
 * action. The API checklist has three gates (id_proof_verified,
 * signature_captured, photo_captured — see Gold_release_model::
 * is_ready_for_release()) but no endpoint anywhere ever sets
 * photo_captured, so no release could actually reach complete() through the
 * API alone. capture_photo() below closes that gap the same way
 * capture_signature() sets its own gate.
 */
class Gold_releases extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Checklist steps (verify-id/signature/photo) are open to any logged-in
        // staff role -- only complete() (below) is further restricted.

        $this->load->model('Loan_model', 'loans');
        $this->load->model('Gold_release_model', 'gold_releases');
        $this->load->model('Jewellery_item_model', 'jewellery_items');
    }

    /** GET /admin/gold-releases */
    public function index()
    {
        $settled = $this->loans->with_relations(array('loans.status' => 'SETTLED'));
        $closed = $this->loans->with_relations(array('loans.status' => 'CLOSED'));

        $this->render('gold_releases', array(
            'page_title' => 'Gold Release',
            'loans' => array_merge($settled, $closed),
        ));
    }

    /** GET /admin/gold-releases/(:num) */
    public function show($loan_id)
    {
        $loan = $this->loans->find($loan_id);
        if (! $loan) {
            show_404();

            return;
        }

        $items = $this->jewellery_items->for_loan($loan_id);
        foreach ($items as &$item) {
            $item['release'] = $this->gold_releases->first(array('loan_id' => $loan_id, 'jewellery_item_id' => $item['id']));
        }
        unset($item);

        $this->render('gold_release_show', array(
            'page_title' => 'Gold Release — Loan #' . $loan_id,
            'loan' => $loan,
            'items' => $items,
            'can_complete' => in_array($this->user['role_code'], array('BRANCH_MANAGER'), true),
        ));
    }

    /** POST /admin/gold-releases/(:num)/verify-id */
    public function verify_id($loan_id)
    {
        $loan = $this->loans->find($loan_id);
        if (! $loan) {
            show_404();

            return;
        }

        $jewellery_item_id = $this->input->post('jewellery_item_id');
        if (! $jewellery_item_id || ! $this->jewellery_items->find($jewellery_item_id)) {
            return $this->_fail($loan_id, 'A valid jewellery item is required.');
        }

        $released_to = trim((string) $this->input->post('released_to'));
        if ($released_to === '') {
            return $this->_fail($loan_id, 'Released-to name is required.');
        }

        $release = $this->gold_releases->first(array('loan_id' => $loan_id, 'jewellery_item_id' => $jewellery_item_id));

        if (! $release) {
            $release_id = $this->gold_releases->insert(array(
                'loan_id' => $loan_id,
                'jewellery_item_id' => $jewellery_item_id,
                'released_by' => $this->user['id'],
                'released_to' => $released_to,
                'status' => 'PENDING',
            ));
        } else {
            $release_id = $release['id'];
        }

        $this->gold_releases->update($release_id, array('id_proof_verified' => 1));

        $this->session->set_flashdata('status', 'ID verified.');
        redirect('admin/gold-releases/' . $loan_id);
    }

    /** POST /admin/gold-releases/release/(:num)/signature */
    public function capture_signature($gold_release_id)
    {
        $release = $this->gold_releases->find($gold_release_id);
        if (! $release) {
            show_404();

            return;
        }

        $this->gold_releases->update($release['id'], array('signature_captured' => 1));

        $this->session->set_flashdata('status', 'Signature captured.');
        redirect('admin/gold-releases/' . $release['loan_id']);
    }

    /** POST /admin/gold-releases/release/(:num)/photo */
    public function capture_photo($gold_release_id)
    {
        $release = $this->gold_releases->find($gold_release_id);
        if (! $release) {
            show_404();

            return;
        }

        $this->gold_releases->update($release['id'], array('photo_captured' => 1));

        $this->session->set_flashdata('status', 'Photo captured.');
        redirect('admin/gold-releases/' . $release['loan_id']);
    }

    /** POST /admin/gold-releases/release/(:num)/complete -- role BRANCH_MANAGER */
    public function complete($gold_release_id)
    {
        if (! $this->require_admin_role(array('BRANCH_MANAGER'))) {
            return;
        }

        $release = $this->gold_releases->find($gold_release_id);
        if (! $release) {
            show_404();

            return;
        }

        $loan = $this->loans->find($release['loan_id']);
        if (! $loan || ! in_array($loan['status'], array('SETTLED', 'CLOSED'), true)) {
            return $this->_fail($release['loan_id'], 'This loan has not been settled/closed yet — jewellery cannot be released.');
        }

        if (! $this->gold_releases->is_ready_for_release($release)) {
            return $this->_fail($release['loan_id'], 'ID proof, signature, and photo must all be captured first.');
        }

        $this->db->trans_start();

        $released_at = date('Y-m-d H:i:s');

        $this->gold_releases->update($release['id'], array('status' => 'RELEASED', 'released_at' => $released_at));

        if (! empty($release['jewellery_item_id'])) {
            $this->jewellery_items->update($release['jewellery_item_id'], array('status' => 'RELEASED'));
        }

        $this->audit_log('Loan', $release['loan_id'], 'RELEASE',
            array('status' => $release['status']),
            array('status' => 'RELEASED', 'released_at' => $released_at, 'jewellery_item_id' => $release['jewellery_item_id'])
        );

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return $this->_fail($release['loan_id'], 'Gold release completion failed. Please retry.');
        }

        $this->session->set_flashdata('status', 'Jewellery item released.');
        redirect('admin/gold-releases/' . $release['loan_id']);
    }

    /** GET /admin/gold-releases/release/(:num)/receipt -- printable customer-copy release receipt. */
    public function receipt($gold_release_id)
    {
        $release = $this->gold_releases->find_with_relations($gold_release_id);
        if (! $release || $release['status'] !== 'RELEASED') {
            show_404();

            return;
        }

        $this->render('gold_release_receipt', array(
            'page_title' => 'Release Receipt — ' . ($release['loan_account_number'] ?? 'Loan #' . $release['loan_id']),
            'release' => $release,
        ));
    }

    private function _fail($loan_id, $message)
    {
        $this->session->set_flashdata('error', $message);
        redirect('admin/gold-releases/' . $loan_id);
    }
}
