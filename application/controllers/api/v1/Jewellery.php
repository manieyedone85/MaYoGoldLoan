<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mirrors app/Http/Controllers/Api/V1/JewelleryController.php.
 * All methods sit behind auth:sanctum + device.binding in the Laravel
 * route group; device.binding only actually enforces single-device rules
 * for BRANCH_EXECUTIVE/APPRAISER/CASHIER, so require_device_binding() is
 * only called here where the route's role middleware includes APPRAISER.
 */
class Jewellery extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Jewellery_item_model', 'jewellery_items');
        $this->load->model('Jewellery_image_model', 'jewellery_images');
        $this->load->model('Jewellery_category_model', 'jewellery_categories');
        $this->load->model('Gold_rate_model', 'gold_rates');
        $this->load->model('Customer_model', 'customers');
    }

    /** POST /api/v1/jewellery/evaluate  (role: APPRAISER) */
    public function evaluate()
    {
        $this->require_auth();
        $this->require_role(array('APPRAISER','ADMIN'));
        $this->require_device_binding();

        $data = $this->json_input();

        if (empty($data['customer_id']) || ! $this->customers->find($data['customer_id'])) {
            return json_error('customer_id is required and must exist.');
        }
        if (empty($data['category_id']) || ! $this->jewellery_categories->find($data['category_id'])) {
            return json_error('category_id is required and must exist.');
        }
        if (! isset($data['gross_weight']) || ! is_numeric($data['gross_weight']) || (float) $data['gross_weight'] < 0.001) {
            return json_error('gross_weight is required and must be at least 0.001.');
        }
        if (isset($data['stone_weight']) && $data['stone_weight'] !== null && (! is_numeric($data['stone_weight']) || (float) $data['stone_weight'] < 0)) {
            return json_error('stone_weight must be a non-negative number.');
        }
        if (empty($data['purity_karat']) || strlen((string) $data['purity_karat']) > 5) {
            return json_error('purity_karat is required and must be at most 5 characters.');
        }

        $goldRate = $this->gold_rates->latest_approved($data['purity_karat']);
        if (! $goldRate) {
            return json_error('No approved gold rate found for this karat.', 404);
        }

        $grossWeight = (float) $data['gross_weight'];
        $stoneWeight = (float) ($data['stone_weight'] ?? 0);
        $netWeight = $grossWeight - $stoneWeight;
        $eligiblePercentage = 75.00; // pulled from loan_product config in a full implementation
        $eligibleAmount = round($netWeight * (float) $goldRate['rate_per_gram'] * ($eligiblePercentage / 100), 2);

        $id = $this->jewellery_items->insert(array(
            'barcode' => 'JWL' . $this->_random_alnum(10),
            'customer_id' => $data['customer_id'],
            'category_id' => $data['category_id'],
            'hallmark_flag' => ! empty($data['hallmark_flag']) ? 1 : 0,
            'gross_weight' => $grossWeight,
            'stone_weight' => $stoneWeight,
            'net_weight' => $netWeight,
            'purity_karat' => $data['purity_karat'],
            'gold_rate_id' => $goldRate['id'],
            'applied_rate' => $goldRate['rate_per_gram'],
            'eligible_percentage' => $eligiblePercentage,
            'eligible_amount' => $eligibleAmount,
            'evaluated_by' => $this->user['id'],
            'status' => 'EVALUATED',
        ));

        $this->audit_log('JewelleryItem', $id, 'CREATE', null, array(
            'customer_id' => $data['customer_id'],
            'category_id' => $data['category_id'],
            'net_weight' => $netWeight,
            'eligible_amount' => $eligibleAmount,
            'status' => 'EVALUATED',
        ));

        return json_response(array('data' => $this->jewellery_items->find($id)), 201);
    }

    /** GET /api/v1/jewellery/rate/current */
    public function current_rate()
    {
        $this->require_auth();

        $karat = $this->input->get('karat');
        if (empty($karat)) {
            return json_error('karat is required.');
        }

        $rate = $this->gold_rates->latest_approved($karat);
        if (! $rate) {
            return json_error('No approved gold rate found for this karat.', 404);
        }

        return json_response(array('data' => $rate));
    }

    /** POST /api/v1/jewellery/rate/propose  (role: APPRAISER, BRANCH_MANAGER) */
    public function propose_rate()
    {
        $this->require_auth();
        $this->require_role(array('APPRAISER', 'BRANCH_MANAGER','ADMIN'));
        $this->require_device_binding();

        $data = $this->json_input();

        if (! isset($data['rate_per_gram']) || ! is_numeric($data['rate_per_gram']) || (float) $data['rate_per_gram'] < 0) {
            return json_error('rate_per_gram is required and must be a non-negative number.');
        }
        if (empty($data['karat']) || strlen((string) $data['karat']) > 5) {
            return json_error('karat is required and must be at most 5 characters.');
        }
        if (empty($data['effective_date']) || strtotime($data['effective_date']) === false) {
            return json_error('effective_date is required and must be a valid date.');
        }

        $id = $this->gold_rates->insert(array(
            'rate_per_gram' => $data['rate_per_gram'],
            'karat' => $data['karat'],
            'effective_date' => date('Y-m-d', strtotime($data['effective_date'])),
            'status' => 'PENDING_APPROVAL',
            'proposed_by' => $this->user['id'],
        ));

        return json_response(array('data' => $this->gold_rates->find($id)), 201);
    }

    /** POST /api/v1/jewellery/rate/{id}/approve  (role: BRANCH_MANAGER, REGIONAL_MANAGER) */
    public function approve_rate($gold_rate_id)
    {
        $this->require_auth();
        $this->require_role(array('BRANCH_MANAGER', 'REGIONAL_MANAGER','ADMIN'));

        $rate = $this->gold_rates->find($gold_rate_id);
        if (! $rate) {
            return json_error('Gold rate not found.', 404);
        }

        $approved_at = date('Y-m-d H:i:s');

        $this->gold_rates->update($gold_rate_id, array(
            'status' => 'APPROVED',
            'approved_by' => $this->user['id'],
            'approved_at' => $approved_at,
        ));

        $this->audit_log(
            'GoldRate',
            $gold_rate_id,
            'RATE_APPROVE',
            array('status' => $rate['status']),
            array('status' => 'APPROVED', 'approved_by' => $this->user['id'], 'approved_at' => $approved_at)
        );

        return json_response(array('data' => $this->gold_rates->find($gold_rate_id)));
    }

    /** POST /api/v1/jewellery/{id}/image */
    public function upload_image($jewellery_item_id)
    {
        $this->require_auth();

        $item = $this->jewellery_items->find($jewellery_item_id);
        if (! $item) {
            return json_error('Jewellery item not found.', 404);
        }

        if (empty($_FILES['image']) || empty($_FILES['image']['name'])) {
            return json_error('image is required.');
        }
        if ($_FILES['image']['size'] > 5120 * 1024) {
            return json_error('image must not be greater than 5120 kilobytes.');
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
            return json_error($this->upload->display_errors('', ''));
        }

        $uploaded = $this->upload->data();
        $path = 'jewellery-images/' . $uploaded['file_name'];

        $id = $this->jewellery_images->insert(array(
            'jewellery_item_id' => $jewellery_item_id,
            'file_ref' => $path,
        ));

        $this->audit_log('JewelleryItem', $jewellery_item_id, 'IMAGE_UPLOAD', null, array(
            'jewellery_image_id' => $id,
            'file_ref' => $path,
        ));

        return json_response(array('data' => $this->jewellery_images->find($id)), 201);
    }

    /** GET /api/v1/jewellery/{id}/barcode */
    public function barcode($jewellery_item_id)
    {
        $this->require_auth();

        $item = $this->jewellery_items->find($jewellery_item_id);
        if (! $item) {
            return json_error('Jewellery item not found.', 404);
        }

        return json_response(array('barcode' => $item['barcode']));
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
}
