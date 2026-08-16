<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mirrors App\Models\DisbursementMode (disbursement_mode_master table).
 * `loan_disbursements.mode` is a FK to this table's `id`, not a free-text
 * code -- see Disbursement::disburse(), which used to insert the raw
 * string mode code (e.g. "CASH") straight into that bigint FK column.
 */
class Disbursement_mode_model extends MY_Model
{
    protected $table = 'disbursement_mode_master';

    public function find_by_code($code)
    {
        return $this->first(array('code' => $code));
    }
}
