<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['api/v1/master/branch']['get'] = 'api/v1/master/branch_index';
$route['api/v1/master/branch']['post'] = 'api/v1/master/branch_store';
$route['api/v1/master/loan-product']['get'] = 'api/v1/master/loan_product_index';
$route['api/v1/master/loan-product']['post'] = 'api/v1/master/loan_product_store';
$route['api/v1/master/role']['get'] = 'api/v1/master/role_index';
