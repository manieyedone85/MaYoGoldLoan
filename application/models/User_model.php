<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends MY_Model
{
    protected $table = 'user_master';

    public function find_by_mobile($mobile)
    {
        return $this->first(array('mobile' => $mobile));
    }

    public function find_by_email($email)
    {
        return $this->first(array('email' => $email));
    }

    public function verify_password($user, $plain_password)
    {
        return $user && ! empty($user['password']) && password_verify($plain_password, $user['password']);
    }

    public function verify_mpin($user, $plain_mpin)
    {
        return $user && ! empty($user['mpin_hash']) && password_verify($plain_mpin, $user['mpin_hash']);
    }

    public function hash_secret($plain)
    {
        return password_hash($plain, PASSWORD_BCRYPT);
    }

    /**
     * Added for the admin Employees page: paginated employee list (every
     * user whose role isn't CUSTOMER), joined with role/branch names, with
     * an optional name/employee_code/mobile search — mirrors
     * Admin\Employees\Index::render() in Laravel.
     */
    public function admin_list($search = '', $per_page = 10, $page = 1)
    {
        $this->load->model('Role_model', 'roles');
        $customer_role = $this->roles->find_by_code('CUSTOMER');

        $build = function () use ($search, $customer_role) {
            $query = $this->db->from('user_master')
                ->join('role_master', 'role_master.id = user_master.role_id', 'left')
                ->join('branches', 'branches.id = user_master.branch_id', 'left');

            if ($customer_role) {
                $query->where('user_master.role_id !=', $customer_role['id']);
            }
            if ($search !== '') {
                $query->group_start()
                    ->like('user_master.name', $search)
                    ->or_like('user_master.employee_code', $search)
                    ->or_like('user_master.mobile', $search)
                    ->group_end();
            }

            return $query;
        };

        $total = $build()->count_all_results();

        $data = $build()
            ->select('user_master.*, role_master.name AS role_name, branches.name AS branch_name')
            ->order_by('user_master.name', 'ASC')
            ->limit($per_page, ($page - 1) * $per_page)
            ->get()
            ->result_array();

        return array(
            'data' => $data,
            'total' => $total,
            'per_page' => $per_page,
            'page' => $page,
            'last_page' => (int) max(1, ceil($total / $per_page)),
        );
    }

    /** Uniqueness helpers used by Employees create/update (Rule::unique(...)->ignore()). */
    public function is_unique($column, $value, $ignore_id = null)
    {
        if ($value === null || $value === '') {
            return true;
        }

        $query = $this->db->from('user_master')->where($column, $value);
        if ($ignore_id) {
            $query->where('id !=', $ignore_id);
        }

        return $query->count_all_results() === 0;
    }
}
