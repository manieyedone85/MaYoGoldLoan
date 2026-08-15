<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Thin CRUD base every table-specific model extends. CI3 has no Eloquent —
 * this just wraps Query Builder calls so controllers don't repeat them.
 * Subclasses set $this->table and call parent::__construct().
 */
class MY_Model extends CI_Model
{
    protected $table;
    protected $primary_key = 'id';
    protected $timestamps = true;

    public function __construct()
    {
        parent::__construct();
    }

    public function all($where = array(), $order_by = null)
    {
        $query = $this->db->from($this->table);
        if (! empty($where)) {
            $query->where($where);
        }
        if ($order_by) {
            $query->order_by($order_by);
        }

        return $query->get()->result_array();
    }

    public function find($id)
    {
        return $this->db->from($this->table)->where($this->primary_key, $id)->get()->row_array();
    }

    public function first($where)
    {
        return $this->db->from($this->table)->where($where)->limit(1)->get()->row_array();
    }

    public function paginate($where = array(), $order_by = null, $per_page = 15, $page = 1)
    {
        $query = $this->db->from($this->table);
        if (! empty($where)) {
            $query->where($where);
        }
        $total = $query->count_all_results('', false);

        $query = $this->db->from($this->table);
        if (! empty($where)) {
            $query->where($where);
        }
        if ($order_by) {
            $query->order_by($order_by);
        }
        $query->limit($per_page, ($page - 1) * $per_page);

        return array(
            'data' => $query->get()->result_array(),
            'total' => $total,
            'per_page' => $per_page,
            'page' => $page,
            'last_page' => (int) ceil($total / $per_page),
        );
    }

    public function insert($data)
    {
        if ($this->timestamps) {
            $now = date('Y-m-d H:i:s');
            $data['created_at'] = $data['created_at'] ?? $now;
            $data['updated_at'] = $data['updated_at'] ?? $now;
        }

        $this->db->insert($this->table, $data);

        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        if ($this->timestamps) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        return $this->db->where($this->primary_key, $id)->update($this->table, $data);
    }

    public function delete($id)
    {
        return $this->db->where($this->primary_key, $id)->delete($this->table);
    }

    public function count($where = array())
    {
        $query = $this->db->from($this->table);
        if (! empty($where)) {
            $query->where($where);
        }

        return $query->count_all_results();
    }
}
