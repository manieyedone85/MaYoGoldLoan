<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_otp_model extends MY_Model
{
    protected $table = 'user_otps';

    public function latest_pending($mobile, $purpose)
    {
        return $this->db->from($this->table)
            ->where('mobile', $mobile)
            ->where('purpose', $purpose)
            ->where('is_verified', 0)
            ->where('expires_at >=', date('Y-m-d H:i:s'))
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get()
            ->row_array();
    }
}
