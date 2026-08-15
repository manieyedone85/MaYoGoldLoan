<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Public promo page — same copy as the Laravel app's resources/views/welcome.blade.php.
 * No auth, no DB dependency.
 */
class Welcome extends CI_Controller
{
    public function index()
    {
        $this->load->view('welcome/index');
    }
}
