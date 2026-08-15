<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
 
class email {
    
    function email()
    {
        $config['protocol']         = 'mail'; // 'mail', 'sendmail', or 'smtp'
		$config['mailpath']         = '/usr/sbin/sendmail';
		$config['smtp_host']        = 'smtp.gmail.com'; // if you are using gmail
		$config['smtp_user']        = 'youremail@gmail.com';
		$config['smtp_pass']        = 'sdkfjsk089sdfskKJ'; // App specific password
		$config['smtp_port']        = 465; // for gmail
		$config['smtp_timeout']     = 5;  
    }
 
}
