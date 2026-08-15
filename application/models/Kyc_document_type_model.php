<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mirrors App\Models\KycDocumentType (kyc_document_types table:
 * VOTER_ID/DRIVING_LICENSE/PASSPORT/UTILITY_BILL/BANK_PASSBOOK).
 */
class Kyc_document_type_model extends MY_Model
{
    protected $table = 'kyc_document_types';
}
