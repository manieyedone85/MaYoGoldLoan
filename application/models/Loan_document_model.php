<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Loan agreement / sanction-letter / other document storage (loan_documents
 * table) -- added for BRD §9 "Loan agreement & documents stored"
 * (docs/BRD_COVERAGE_AUDIT.md). No such model/controller existed before this.
 */
class Loan_document_model extends MY_Model
{
    protected $table = 'loan_documents';

    public function for_loan($loan_id)
    {
        return $this->all(array('loan_id' => $loan_id), 'created_at DESC');
    }
}
