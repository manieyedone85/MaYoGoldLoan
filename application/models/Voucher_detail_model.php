<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Voucher_detail_model extends MY_Model
{
    protected $table = 'voucher_details';

    public function for_voucher($voucher_id)
    {
        return $this->all(array('voucher_id' => $voucher_id));
    }
}
