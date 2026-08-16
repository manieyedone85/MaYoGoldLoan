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
        $this->load->model('Jewellery_valuation_history_model', 'valuation_history');
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
        $eligiblePercentage = (float) $goldRate['ltv_pct']; // approved alongside the gold rate, not hardcoded
        $eligibleAmount = round($netWeight * (float) $goldRate['rate_per_gram'] * ($eligiblePercentage / 100), 2);

        $id = $this->jewellery_items->insert(array(
            'barcode' => 'JWL' . $this->_random_alnum(10),
            'customer_id' => $data['customer_id'],
            'category_id' => $data['category_id'],
            'hallmark_flag' => ! empty($data['hallmark_flag']) ? 1 : 0,
            'gross_weight' => $grossWeight,
            'stone_weight' => $stoneWeight,
            // net_weight is NOT inserted -- it's a MySQL generated column
            // (gross_weight - stone_weight) on the live jewellery_items table;
            // an explicit value here would error. $netWeight is still used
            // in-memory for eligible_amount below.
            'purity_karat' => $data['purity_karat'],
            'gold_rate_id' => $goldRate['id'],
            'applied_rate' => $goldRate['rate_per_gram'],
            'eligible_percentage' => $eligiblePercentage,
            'eligible_amount' => $eligibleAmount,
            'evaluated_by' => $this->user['id'],
            'status' => 'EVALUATED',
        ));

        $this->valuation_history->insert(array(
            'jewellery_item_id' => $id,
            'gold_rate_id' => $goldRate['id'],
            'gross_weight' => $grossWeight,
            'stone_weight' => $stoneWeight,
            'applied_rate' => $goldRate['rate_per_gram'],
            'eligible_percentage' => $eligiblePercentage,
            'eligible_amount' => $eligibleAmount,
            'evaluated_by' => $this->user['id'],
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

    /**
     * POST /api/v1/jewellery/{id}/re-evaluate  (role: APPRAISER)
     *
     * Not a Laravel port -- added for BRD §8 "Valuation history retained"
     * (docs/BRD_COVERAGE_AUDIT.md). There was previously no way to
     * re-evaluate an already-evaluated item at all: `evaluate()` only ever
     * inserts a new jewellery_items row. This re-prices an existing item
     * against the current approved gold rate (optionally with corrected
     * weights) and snapshots the new valuation into
     * `jewellery_valuation_history` -- the prior valuation is already
     * preserved as an earlier history row, so nothing is ever overwritten
     * without a trace. Only allowed while the item is still EVALUATED or
     * PLEDGED; once it's RELEASED/AUCTIONED its valuation is final.
     */
    public function re_evaluate($jewellery_item_id)
    {
        $this->require_auth();
        $this->require_role(array('APPRAISER', 'ADMIN'));
        $this->require_device_binding();

        $item = $this->jewellery_items->find($jewellery_item_id);
        if (! $item) {
            return json_error('Jewellery item not found.', 404);
        }
        if (! in_array($item['status'], array('EVALUATED', 'PLEDGED'), true)) {
            return json_error('Only EVALUATED or PLEDGED items can be re-evaluated.', 422);
        }

        $data = $this->json_input();

        $grossWeight = isset($data['gross_weight']) ? (float) $data['gross_weight'] : (float) $item['gross_weight'];
        if ($grossWeight < 0.001) {
            return json_error('gross_weight must be at least 0.001.');
        }
        $stoneWeight = isset($data['stone_weight']) ? (float) $data['stone_weight'] : (float) $item['stone_weight'];
        if ($stoneWeight < 0) {
            return json_error('stone_weight must be a non-negative number.');
        }
        $purityKarat = ! empty($data['purity_karat']) ? $data['purity_karat'] : $item['purity_karat'];

        $goldRate = $this->gold_rates->latest_approved($purityKarat);
        if (! $goldRate) {
            return json_error('No approved gold rate found for this karat.', 404);
        }

        $netWeight = $grossWeight - $stoneWeight;
        $eligiblePercentage = (float) $goldRate['ltv_pct'];
        $eligibleAmount = round($netWeight * (float) $goldRate['rate_per_gram'] * ($eligiblePercentage / 100), 2);

        $before = array(
            'gold_rate_id' => $item['gold_rate_id'],
            'applied_rate' => $item['applied_rate'],
            'eligible_percentage' => $item['eligible_percentage'],
            'eligible_amount' => $item['eligible_amount'],
        );

        $this->jewellery_items->update($jewellery_item_id, array(
            'gross_weight' => $grossWeight,
            'stone_weight' => $stoneWeight,
            'purity_karat' => $purityKarat,
            'gold_rate_id' => $goldRate['id'],
            'applied_rate' => $goldRate['rate_per_gram'],
            'eligible_percentage' => $eligiblePercentage,
            'eligible_amount' => $eligibleAmount,
        ));

        $this->valuation_history->insert(array(
            'jewellery_item_id' => $jewellery_item_id,
            'gold_rate_id' => $goldRate['id'],
            'gross_weight' => $grossWeight,
            'stone_weight' => $stoneWeight,
            'applied_rate' => $goldRate['rate_per_gram'],
            'eligible_percentage' => $eligiblePercentage,
            'eligible_amount' => $eligibleAmount,
            'evaluated_by' => $this->user['id'],
        ));

        $updated = $this->jewellery_items->find($jewellery_item_id);

        $this->audit_log('JewelleryItem', $jewellery_item_id, 'RE_EVALUATE', $before, array(
            'gold_rate_id' => $goldRate['id'],
            'applied_rate' => $goldRate['rate_per_gram'],
            'eligible_percentage' => $eligiblePercentage,
            'eligible_amount' => $eligibleAmount,
        ));

        return json_response(array('data' => $updated));
    }

    /** GET /api/v1/jewellery/{id}/valuation-history */
    public function valuation_history($jewellery_item_id)
    {
        $this->require_auth();

        $item = $this->jewellery_items->find($jewellery_item_id);
        if (! $item) {
            return json_error('Jewellery item not found.', 404);
        }

        return json_response(array('data' => $this->valuation_history->for_item($jewellery_item_id)));
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
        if (isset($data['ltv_pct']) && (! is_numeric($data['ltv_pct']) || (float) $data['ltv_pct'] <= 0 || (float) $data['ltv_pct'] > 100)) {
            return json_error('ltv_pct must be a number between 0 and 100.');
        }

        $id = $this->gold_rates->insert(array(
            'rate_per_gram' => $data['rate_per_gram'],
            'ltv_pct' => isset($data['ltv_pct']) ? $data['ltv_pct'] : 75.00,
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

    /**
     * GET /api/v1/jewellery/image/{id}/file
     * Gated file-serving endpoint -- BRD §15 "Secure KYC / jewellery image
     * access" (docs/BRD_COVERAGE_AUDIT.md). `upload_image()` above stored
     * images under an obfuscated filename with no read-side gate at all;
     * mirrors the same pattern as Kyc_document::download() and
     * Loan_document::download().
     */
    public function download_image($jewellery_image_id)
    {
        $this->require_auth();
        $this->require_role(array('APPRAISER', 'BRANCH_EXECUTIVE', 'BRANCH_MANAGER', 'REGIONAL_MANAGER', 'OPERATIONS', 'ADMIN'));

        $image = $this->jewellery_images->find($jewellery_image_id);
        if (! $image) {
            return json_error('Jewellery image not found.', 404);
        }

        $path = FCPATH . 'uploads/' . $image['file_ref'];
        if (! is_file($path)) {
            return json_error('File not found.', 404);
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
}
