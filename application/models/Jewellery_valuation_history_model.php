<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * One row per valuation event for a jewellery item (`jewellery_items` only
 * ever holds the current applied_rate/eligible_amount) -- see BRD §8
 * "Valuation history retained" in docs/BRD_COVERAGE_AUDIT.md. Written to by
 * Jewellery::evaluate() and Jewellery::re_evaluate().
 *
 * $timestamps is off because this table only has `created_at`, no
 * `updated_at` -- MY_Model::insert() would otherwise try to write a column
 * that doesn't exist (same pattern as Customer_duplicate_log_model).
 */
class Jewellery_valuation_history_model extends MY_Model
{
    protected $table = 'jewellery_valuation_history';
    protected $timestamps = false;

    public function insert($data)
    {
        $data['created_at'] = $data['created_at'] ?? date('Y-m-d H:i:s');

        $this->db->insert($this->table, $data);

        return $this->db->insert_id();
    }

    public function for_item($jewellery_item_id)
    {
        return $this->all(array('jewellery_item_id' => $jewellery_item_id), 'created_at DESC');
    }
}
