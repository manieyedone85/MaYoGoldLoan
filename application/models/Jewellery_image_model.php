<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Jewellery_image_model extends MY_Model
{
    protected $table = 'jewellery_images';

    /** All images for a set of jewellery items, keyed by jewellery_item_id. */
    public function for_items(array $jewellery_item_ids)
    {
        if (empty($jewellery_item_ids)) {
            return array();
        }

        $rows = $this->db->from($this->table)->where_in('jewellery_item_id', $jewellery_item_ids)->get()->result_array();

        $by_item = array();
        foreach ($rows as $row) {
            $by_item[$row['jewellery_item_id']][] = $row;
        }

        return $by_item;
    }
}
