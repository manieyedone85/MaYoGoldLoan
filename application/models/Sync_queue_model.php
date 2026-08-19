<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * sync_queues.payload is a JSON column — encode with json_encode() before
 * insert, decode with json_decode($x, true) after read (see CONVENTIONS.md).
 */
class Sync_queue_model extends MY_Model
{
    protected $table = 'sync_queue';

    public function insert($data)
    {
        if (array_key_exists('payload', $data) && ! is_string($data['payload'])) {
            $data['payload'] = json_encode($data['payload']);
        }

        return parent::insert($data);
    }

    public function find($id)
    {
        return $this->decode(parent::find($id));
    }

    public function all($where = array(), $order_by = null)
    {
        $rows = parent::all($where, $order_by);

        return array_map(array($this, 'decode'), $rows);
    }

    public function paginate($where = array(), $order_by = null, $per_page = 15, $page = 1, $search = '', array $search_columns = array())
    {
        $result = parent::paginate($where, $order_by, $per_page, $page, $search, $search_columns);
        $result['data'] = array_map(array($this, 'decode'), $result['data']);

        return $result;
    }

    private function decode($row)
    {
        if ($row && isset($row['payload'])) {
            $row['payload'] = json_decode($row['payload'], true);
        }

        return $row;
    }
}
