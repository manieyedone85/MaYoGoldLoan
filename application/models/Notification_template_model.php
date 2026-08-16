<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notification_template_model extends MY_Model
{
    protected $table = 'notification_template';

    public function find_by_code($code)
    {
        return $this->first(array('code' => $code));
    }
}
