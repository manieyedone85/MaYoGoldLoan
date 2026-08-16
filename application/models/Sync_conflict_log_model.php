<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * sync_conflict_logs.server_value / client_value are JSON columns.
 * Not written to by the current Laravel SyncController (its
 * uploadQueue()/downloadDelta() methods only touch sync_queues — the
 * server-wins/manual-review conflict logic described in the docblock is not
 * yet implemented in the Laravel source), but the model is provided here per
 * SCHEMA_REFERENCE.md / task scope for when that logic is built out.
 */
class Sync_conflict_log_model extends MY_Model
{
    protected $table = 'sync_conflict_log';

    public function insert($data)
    {
        if (array_key_exists('server_value', $data) && ! is_string($data['server_value'])) {
            $data['server_value'] = $data['server_value'] === null ? null : json_encode($data['server_value']);
        }
        if (array_key_exists('client_value', $data) && ! is_string($data['client_value'])) {
            $data['client_value'] = $data['client_value'] === null ? null : json_encode($data['client_value']);
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

    private function decode($row)
    {
        if (! $row) {
            return $row;
        }
        if (isset($row['server_value'])) {
            $row['server_value'] = json_decode($row['server_value'], true);
        }
        if (isset($row['client_value'])) {
            $row['client_value'] = json_decode($row['client_value'], true);
        }

        return $row;
    }
}
