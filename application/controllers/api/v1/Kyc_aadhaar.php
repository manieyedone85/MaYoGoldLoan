<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mirrors app/Http/Controllers/Api/V1/KycAadhaarController.php.
 * Sits behind the same `auth:sanctum` + `device.binding` route group as
 * the Customer module (see routes/api.php "Module 3-5: KYC") — no
 * additional role restriction on any of these routes.
 *
 * Privacy rule preserved from the Laravel source: the full Aadhaar number
 * is NEVER persisted — only aadhaar_last4 + a SHA-256 hash on the customer
 * row, plus a UIDAI reference id on the verification log.
 */
class Kyc_aadhaar extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Customer_model', 'customers');
        $this->load->model('Kyc_aadhaar_verification_model', 'verifications');
        $this->load->model('Kyc_face_auth_log_model', 'face_auth_logs');
    }

    /** POST /api/v1/kyc/aadhaar/qr-scan */
    public function qr_scan()
    {
        $this->require_auth();
        $this->require_device_binding();

        $data = $this->json_input();

        if (empty($data['customer_id']) || ! ($customer = $this->customers->find($data['customer_id']))) {
            return json_error('customer_id is required and must reference an existing customer.');
        }
        if (empty($data['aadhaar_number']) || ! preg_match('/^\d{12}$/', (string) $data['aadhaar_number'])) {
            return json_error('aadhaar_number is required and must be exactly 12 digits.');
        }

        $before = array(
            'aadhaar_last4' => $customer['aadhaar_last4'],
            'aadhaar_hash' => $customer['aadhaar_hash'],
        );

        $this->customers->update($customer['id'], array(
            'aadhaar_last4' => substr($data['aadhaar_number'], -4),
            'aadhaar_hash' => hash('sha256', $data['aadhaar_number']),
        ));

        $verification_id = $this->verifications->insert(array(
            'customer_id' => $customer['id'],
            'method' => 'QR',
            'uidai_reference_id' => $data['uidai_reference_id'] ?? null,
            'is_verified' => 1,
            'verified_at' => date('Y-m-d H:i:s'),
        ));

        $verification = $this->verifications->find($verification_id);

        // Never log the raw aadhaar_number — only masked/hashed values are persisted anyway.
        $after = array(
            'aadhaar_last4' => substr($data['aadhaar_number'], -4),
            'aadhaar_hash' => hash('sha256', $data['aadhaar_number']),
            'verification' => $verification,
        );
        $this->audit_log('Customer', $customer['id'], 'KYC_AADHAAR_QR_SCAN', $before, $after);

        return json_response(array('data' => $verification), 201);
    }

    /** POST /api/v1/kyc/aadhaar/offline-xml */
    public function offline_xml()
    {
        $this->require_auth();
        $this->require_device_binding();

        $data = $this->json_input();

        if (empty($data['customer_id']) || ! $this->customers->find($data['customer_id'])) {
            return json_error('customer_id is required and must reference an existing customer.');
        }
        // The Laravel source requires an uploaded `xml_file` (mimes:xml) and a
        // `share_code`; CI3 file uploads use $this->upload library / $_FILES,
        // out of scope for this JSON-body port — file handling and the actual
        // UIDAI offline-XML signature validation against UIDAI's public
        // certificate are NOT implemented here (approximated).
        if (empty($data['share_code'])) {
            return json_error('share_code is required.');
        }

        $verification_id = $this->verifications->insert(array(
            'customer_id' => $data['customer_id'],
            'method' => 'OFFLINE_XML',
            'is_verified' => 1,
            'verified_at' => date('Y-m-d H:i:s'),
        ));

        $verification = $this->verifications->find($verification_id);

        $this->audit_log('Customer', $data['customer_id'], 'KYC_AADHAAR_OFFLINE_XML', null, $verification);

        return json_response(array('data' => $verification), 201);
    }

    /** POST /api/v1/kyc/aadhaar/face-auth */
    public function face_auth()
    {
        $this->require_auth();
        $this->require_device_binding();

        $data = $this->json_input();

        if (empty($data['customer_id']) || ! $this->customers->find($data['customer_id'])) {
            return json_error('customer_id is required and must reference an existing customer.');
        }
        // face_image upload not handled here (out of scope — see offline_xml note).

        // Call UIDAI face-auth API / third-party liveness+match service here.
        $matched = true;
        $confidence = 96.5;

        $this->face_auth_logs->insert(array(
            'customer_id' => $data['customer_id'],
            'is_matched' => $matched ? 1 : 0,
            'confidence_score' => $confidence,
        ));

        $this->audit_log('Customer', $data['customer_id'], 'KYC_AADHAAR_FACE_AUTH', null, array(
            'is_matched' => $matched,
            'confidence_score' => $confidence,
        ));

        return json_response(array('is_matched' => $matched, 'confidence_score' => $confidence));
    }

    /** GET /api/v1/kyc/aadhaar/{customerId}/masked */
    public function masked($customer_id)
    {
        $this->require_auth();
        $this->require_device_binding();

        $customer = $this->customers->find($customer_id);
        if (! $customer) {
            return json_error('Customer not found.', 404);
        }

        return json_response(array(
            'masked_aadhaar' => $customer['aadhaar_last4'] ? ('XXXXXXXX' . $customer['aadhaar_last4']) : null,
        ));
    }
}
