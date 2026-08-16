<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * audit_logs.before_value / after_value are JSON columns — encode with
 * json_encode() before insert, decode with json_decode($x, true) after read.
 */
class Audit_log_model extends MY_Model
{
    protected $table = 'audit_logs';

    public function insert($data)
    {
        if (array_key_exists('before_value', $data) && ! is_string($data['before_value'])) {
            $data['before_value'] = $data['before_value'] === null ? null : json_encode($data['before_value']);
        }
        if (array_key_exists('after_value', $data) && ! is_string($data['after_value'])) {
            $data['after_value'] = $data['after_value'] === null ? null : json_encode($data['after_value']);
        }

        return parent::insert($data);
    }

    public function find($id)
    {
        $row = parent::find($id);

        return $this->decode($row);
    }

    public function all($where = array(), $order_by = null)
    {
        $rows = parent::all($where, $order_by);

        return array_map(array($this, 'decode'), $rows);
    }

    private function decode($row)
    {
        if (! $row) {
            return $row;
        }
        if (isset($row['before_value'])) {
            $row['before_value'] = json_decode($row['before_value'], true);
        }
        if (isset($row['after_value'])) {
            $row['after_value'] = json_decode($row['after_value'], true);
        }

        return $row;
    }
}
