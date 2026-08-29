<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * $timestamps is off because this table only has `created_at`, no
 * `updated_at` -- see Customer_duplicate_log_model for the same pattern.
 */
class Loan_approval_limit_model extends MY_Model
{
    protected $table = 'loan_approval_limit_master';
    protected $timestamps = false;

    public function insert($data)
    {
        $data['created_at'] = $data['created_at'] ?? date('Y-m-d H:i:s');

        $this->db->insert($this->table, $data);

        return $this->db->insert_id();
    }

    public function for_role($role_id)
    {
        return $this->first(array('role_id' => $role_id));
    }

    /** Limits joined with role name/code -- for the admin Masters "Approval Limits" tab. */
    public function with_relations()
    {
        return $this->db->select('loan_approval_limit_master.*, roles.code AS role_code, roles.name AS role_name')
            ->from('loan_approval_limit_master')
            ->join('roles', 'roles.id = loan_approval_limit_master.role_id', 'left')
            ->order_by('roles.name', 'ASC')
            ->get()
            ->result_array();
    }
}
