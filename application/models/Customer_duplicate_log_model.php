<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mirrors App\Models\CustomerDuplicateLog (customer_duplicate_logs table).
 * Not currently written to by CustomerController::duplicateCheck() in the
 * Laravel source (it only reads/reports possible duplicates without
 * persisting a log row) — kept here for future use by a reviewer workflow.
 */
class Customer_duplicate_log_model extends MY_Model
{
    protected $table = 'customer_duplicate_log';
}
