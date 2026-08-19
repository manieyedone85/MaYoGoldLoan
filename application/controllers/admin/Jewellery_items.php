<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin panel: Jewellery Items. Ports
 * application/controllers/api/v1/Jewellery.php's evaluate()/re_evaluate()/
 * valuation_history()/upload_image()/download_image() -- same eligible-
 * amount formula (net_weight x rate x ltv_pct from the approved gold rate),
 * same valuation-history snapshot on every evaluate/re-evaluate, same
 * gated image download. Gold-rate propose/approve stays in admin/Masters.php
 * (a pure master-config maker-checker, see that controller).
 */
class Jewellery_items extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->require_admin_role(array('APPRAISER', 'BRANCH_MANAGER', 'BRANCH_EXECUTIVE', 'OPERATIONS'));

        $this->load->model('Jewellery_item_model', 'jewellery_items');
        $this->load->model('Jewellery_image_model', 'jewellery_images');
        $this->load->model('Jewellery_category_model', 'jewellery_categories');
        $this->load->model('Gold_rate_model', 'gold_rates');
        $this->load->model('Customer_model', 'customers');
        $this->load->model('Jewellery_valuation_history_model', 'valuation_history');
    }

    /** GET /admin/jewellery-items */
    public function index()
    {
        $status = trim((string) $this->input->get('status'));
        $where = $status !== '' ? array('jewellery_items.status' => $status) : array();
        $search = trim((string) $this->input->get('search'));
        $page = max(1, (int) $this->input->get('page'));

        $result = $this->jewellery_items->with_relations($where, $search, 15, $page);

        $this->render('jewellery_items', array(
            'page_title' => 'Jewellery Items',
            'items' => $result['data'],
            'pagination' => $result,
            'status' => $status,
            'filters' => array('status' => $status, 'search' => $search),
            'categories' => $this->jewellery_categories->all(array(), 'name ASC'),
            'can_evaluate' => in_array($this->user['role_code'], array('APPRAISER'), true),
        ));
    }

    /** POST /admin/jewellery-items/evaluate -- role APPRAISER */
    public function evaluate()
    {
        if (! $this->require_admin_role(array('APPRAISER'))) {
            return;
        }

        $mobile = trim((string) $this->input->post('customer_mobile'));
        $customer = $mobile !== '' ? $this->customers->find_by_mobile($mobile) : null;
        if (! $customer) {
            return $this->_fail('No customer found with that mobile number.');
        }

        $category_id = $this->input->post('category_id');
        if (! $category_id || ! $this->jewellery_categories->find($category_id)) {
            return $this->_fail('A valid category is required.');
        }

        $gross_weight = $this->input->post('gross_weight');
        if (! is_numeric($gross_weight) || (float) $gross_weight < 0.001) {
            return $this->_fail('Gross weight is required and must be at least 0.001g.');
        }

        $stone_weight = $this->input->post('stone_weight');
        $stone_weight = ($stone_weight !== '' && $stone_weight !== null) ? (float) $stone_weight : 0.0;
        if ($stone_weight < 0) {
            return $this->_fail('Stone weight must be a non-negative number.');
        }

        $purity_karat = trim((string) $this->input->post('purity_karat'));
        if ($purity_karat === '' || strlen($purity_karat) > 5) {
            return $this->_fail('Purity (karat) is required and must be at most 5 characters.');
        }

        $gold_rate = $this->gold_rates->latest_approved($purity_karat);
        if (! $gold_rate) {
            return $this->_fail('No approved gold rate found for this karat.');
        }

        $gross_weight = (float) $gross_weight;
        $net_weight = $gross_weight - $stone_weight;
        $eligible_percentage = (float) $gold_rate['ltv_pct'];
        $eligible_amount = round($net_weight * (float) $gold_rate['rate_per_gram'] * ($eligible_percentage / 100), 2);

        $id = $this->jewellery_items->insert(array(
            'barcode' => 'JWL' . $this->_random_alnum(10),
            'customer_id' => $customer['id'],
            'category_id' => $category_id,
            'hallmark_flag' => $this->input->post('hallmark_flag') ? 1 : 0,
            'gross_weight' => $gross_weight,
            'stone_weight' => $stone_weight,
            'purity_karat' => $purity_karat,
            'gold_rate_id' => $gold_rate['id'],
            'applied_rate' => $gold_rate['rate_per_gram'],
            'eligible_percentage' => $eligible_percentage,
            'eligible_amount' => $eligible_amount,
            'evaluated_by' => $this->user['id'],
            'status' => 'EVALUATED',
        ));

        $this->valuation_history->insert(array(
            'jewellery_item_id' => $id,
            'gold_rate_id' => $gold_rate['id'],
            'gross_weight' => $gross_weight,
            'stone_weight' => $stone_weight,
            'applied_rate' => $gold_rate['rate_per_gram'],
            'eligible_percentage' => $eligible_percentage,
            'eligible_amount' => $eligible_amount,
            'evaluated_by' => $this->user['id'],
        ));

        $this->audit_log('JewelleryItem', $id, 'CREATE', null, array(
            'customer_id' => $customer['id'], 'category_id' => $category_id, 'net_weight' => $net_weight, 'eligible_amount' => $eligible_amount,
        ));

        $this->session->set_flashdata('status', 'Jewellery item evaluated: ₹' . number_format($eligible_amount, 2) . ' eligible.');
        redirect('admin/jewellery-items');
    }

    /** GET /admin/jewellery-items/(:num) */
    public function show($id)
    {
        $item = $this->jewellery_items->find($id);
        if (! $item) {
            show_404();

            return;
        }

        $customer = $this->customers->find($item['customer_id']);
        $images = $this->jewellery_images->for_items(array($id));

        $this->render('jewellery_item_show', array(
            'page_title' => 'Jewellery ' . $item['barcode'],
            'item' => $item,
            'customer' => $customer,
            'images' => $images,
            'history' => $this->valuation_history->for_item($id),
            'can_evaluate' => in_array($this->user['role_code'], array('APPRAISER'), true),
        ));
    }

    /** POST /admin/jewellery-items/(:num)/re-evaluate -- role APPRAISER */
    public function re_evaluate($id)
    {
        if (! $this->require_admin_role(array('APPRAISER'))) {
            return;
        }

        $item = $this->jewellery_items->find($id);
        if (! $item) {
            show_404();

            return;
        }
        if (! in_array($item['status'], array('EVALUATED', 'PLEDGED'), true)) {
            return $this->_fail_item($id, 'Only EVALUATED or PLEDGED items can be re-evaluated.');
        }

        $gross_weight_in = $this->input->post('gross_weight');
        $gross_weight = ($gross_weight_in !== '' && $gross_weight_in !== null) ? (float) $gross_weight_in : (float) $item['gross_weight'];
        if ($gross_weight < 0.001) {
            return $this->_fail_item($id, 'Gross weight must be at least 0.001g.');
        }

        $stone_weight_in = $this->input->post('stone_weight');
        $stone_weight = ($stone_weight_in !== '' && $stone_weight_in !== null) ? (float) $stone_weight_in : (float) $item['stone_weight'];
        if ($stone_weight < 0) {
            return $this->_fail_item($id, 'Stone weight must be a non-negative number.');
        }

        $purity_karat_in = trim((string) $this->input->post('purity_karat'));
        $purity_karat = $purity_karat_in !== '' ? $purity_karat_in : $item['purity_karat'];

        $gold_rate = $this->gold_rates->latest_approved($purity_karat);
        if (! $gold_rate) {
            return $this->_fail_item($id, 'No approved gold rate found for this karat.');
        }

        $net_weight = $gross_weight - $stone_weight;
        $eligible_percentage = (float) $gold_rate['ltv_pct'];
        $eligible_amount = round($net_weight * (float) $gold_rate['rate_per_gram'] * ($eligible_percentage / 100), 2);

        $before = array(
            'gold_rate_id' => $item['gold_rate_id'], 'applied_rate' => $item['applied_rate'],
            'eligible_percentage' => $item['eligible_percentage'], 'eligible_amount' => $item['eligible_amount'],
        );

        $this->jewellery_items->update($id, array(
            'gross_weight' => $gross_weight,
            'stone_weight' => $stone_weight,
            'purity_karat' => $purity_karat,
            'gold_rate_id' => $gold_rate['id'],
            'applied_rate' => $gold_rate['rate_per_gram'],
            'eligible_percentage' => $eligible_percentage,
            'eligible_amount' => $eligible_amount,
        ));

        $this->valuation_history->insert(array(
            'jewellery_item_id' => $id,
            'gold_rate_id' => $gold_rate['id'],
            'gross_weight' => $gross_weight,
            'stone_weight' => $stone_weight,
            'applied_rate' => $gold_rate['rate_per_gram'],
            'eligible_percentage' => $eligible_percentage,
            'eligible_amount' => $eligible_amount,
            'evaluated_by' => $this->user['id'],
        ));

        $this->audit_log('JewelleryItem', $id, 'RE_EVALUATE', $before, array(
            'gold_rate_id' => $gold_rate['id'], 'applied_rate' => $gold_rate['rate_per_gram'],
            'eligible_percentage' => $eligible_percentage, 'eligible_amount' => $eligible_amount,
        ));

        $this->session->set_flashdata('status', 'Item re-evaluated: ₹' . number_format($eligible_amount, 2) . ' eligible.');
        redirect('admin/jewellery-items/' . $id);
    }

    /** POST /admin/jewellery-items/(:num)/image */
    public function upload_image($id)
    {
        $item = $this->jewellery_items->find($id);
        if (! $item) {
            show_404();

            return;
        }

        if (empty($_FILES['image']) || empty($_FILES['image']['name'])) {
            return $this->_fail_item($id, 'An image file is required.');
        }
        if ($_FILES['image']['size'] > 5120 * 1024) {
            return $this->_fail_item($id, 'Image must not be greater than 5120 kilobytes.');
        }

        $upload_dir = FCPATH . 'uploads/jewellery-images';
        if (! is_dir($upload_dir)) {
            mkdir($upload_dir, 0775, true);
        }

        $this->load->library('upload', array(
            'upload_path' => $upload_dir,
            'allowed_types' => 'jpg|jpeg|png|gif|webp',
            'max_size' => 5120,
            'encrypt_name' => true,
        ));

        if (! $this->upload->do_upload('image')) {
            return $this->_fail_item($id, $this->upload->display_errors('', ''));
        }

        $uploaded = $this->upload->data();
        $path = 'jewellery-images/' . $uploaded['file_name'];

        $image_id = $this->jewellery_images->insert(array('jewellery_item_id' => $id, 'file_ref' => $path));

        $this->audit_log('JewelleryItem', $id, 'IMAGE_UPLOAD', null, array('jewellery_image_id' => $image_id, 'file_ref' => $path));

        $this->session->set_flashdata('status', 'Image uploaded.');
        redirect('admin/jewellery-items/' . $id);
    }

    /** GET /admin/jewellery-items/image/(:num) -- gated inline file view */
    public function download_image($jewellery_image_id)
    {
        $image = $this->jewellery_images->find($jewellery_image_id);
        if (! $image) {
            show_404();

            return;
        }

        $path = FCPATH . 'uploads/' . $image['file_ref'];
        if (! is_file($path)) {
            show_404();

            return;
        }

        $this->audit_log('JewelleryImage', $jewellery_image_id, 'JEWELLERY_IMAGE_VIEW', null, null);

        $mime = function_exists('mime_content_type') ? mime_content_type($path) : 'application/octet-stream';
        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . basename($path) . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    private function _random_alnum($length)
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $str;
    }

    private function _fail($message)
    {
        $this->session->set_flashdata('error', $message);
        redirect('admin/jewellery-items');
    }

    private function _fail_item($id, $message)
    {
        $this->session->set_flashdata('error', $message);
        redirect('admin/jewellery-items/' . $id);
    }
}
