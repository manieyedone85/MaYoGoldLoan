<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mirrors App\Models\CustomerDuplicateLog (customer_duplicate_log table).
 *
 * Now written to by Customer::store() (BRD §7 "duplicate customer creation
 * is detected" — see docs/BRD_COVERAGE_AUDIT.md) since neither the reviewed
 * Laravel source nor the schema had anything actually persisting a log row
 * despite the table/status workflow (PENDING_REVIEW/CONFIRMED/DISMISSED)
 * existing for exactly this purpose.
 *
 * $timestamps is off because this table only has `created_at`, no
 * `updated_at` — MY_Model::insert() would otherwise try to write a column
 * that doesn't exist.
 */
class Customer_duplicate_log_model extends MY_Model
{
    protected $table = 'customer_duplicate_log';
    protected $timestamps = false;

    public function insert($data)
    {
        $data['created_at'] = $data['created_at'] ?? date('Y-m-d H:i:s');

        $this->db->insert($this->table, $data);

        return $this->db->insert_id();
    }

    public function for_customer($customer_id)
    {
        return $this->all(array('customer_id' => $customer_id), 'created_at DESC');
    }
}
