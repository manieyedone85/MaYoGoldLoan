<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mirrors app/Http/Controllers/Api/V1/KycPanController.php. Same route
 * group as Kyc_aadhaar/Customer (auth:sanctum + device.binding, no role
 * restriction).
 */
class Kyc_pan extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Customer_model', 'customers');
        $this->load->model('Kyc_pan_verification_model', 'verifications');
    }

    /** POST /api/v1/kyc/pan/validate */
    public function validate_pan()
    {
        $this->require_auth();
        $this->require_device_binding();

        $data = $this->json_input();

        if (empty($data['customer_id']) || ! ($customer = $this->customers->find($data['customer_id']))) {
            return json_error('customer_id is required and must reference an existing customer.');
        }
        if (empty($data['pan_number'])
            || strlen((string) $data['pan_number']) !== 10
            || ! preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', (string) $data['pan_number'])) {
            return json_error('pan_number is required and must match the PAN format (5 letters, 4 digits, 1 letter).');
        }

        // Call PAN validation API + fuzzy-match name against customers.name
        // (Levenshtein <= 2) here — external service, not implemented; the
        // Laravel source itself also stubs this to `true`.
        $name_match = true;

        $verification_id = $this->verifications->insert(array(
            'customer_id' => $customer['id'],
            'pan_number' => $data['pan_number'],
            'is_verified' => 1,
            'name_match' => $name_match ? 1 : 0,
        ));

        $verification = $this->verifications->find($verification_id);

        // pan_number is stored unmasked on this row — exclude it from the audit log.
        $this->audit_log(
            'Customer',
            $customer['id'],
            'KYC_PAN_VALIDATE',
            null,
            array_diff_key($verification, array_flip(array('pan_number')))
        );

        return json_response(array('data' => $verification), 201);
    }
}
