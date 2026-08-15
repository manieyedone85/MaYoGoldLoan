<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Role_model extends MY_Model
{
    protected $table = 'role_master';

    public function find_by_code($code)
    {
        return $this->first(array('code' => $code));
    }
}
