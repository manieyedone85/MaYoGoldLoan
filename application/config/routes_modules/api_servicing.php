<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Disbursement
$route['api/v1/loan/(:num)/disburse']['post'] = 'api/v1/disbursement/disburse/$1';

// Renewal
$route['api/v1/loan/(:num)/renewal-eligibility']['get'] = 'api/v1/renewal/eligibility/$1';
$route['api/v1/loan/(:num)/renew']['post'] = 'api/v1/renewal/renew/$1';

// Top-up
$route['api/v1/loan/(:num)/topup/eligibility']['get'] = 'api/v1/topup/eligibility/$1';
$route['api/v1/loan/(:num)/topup/approve']['post'] = 'api/v1/topup/approve/$1';
$route['api/v1/loan/(:num)/topup/disburse']['post'] = 'api/v1/topup/disburse/$1';

// Interest
$route['api/v1/loan/(:num)/interest/due']['get'] = 'api/v1/interest/due/$1';
$route['api/v1/loan/(:num)/interest/collect']['post'] = 'api/v1/interest/collect/$1';

// Part payment / reload
$route['api/v1/loan/(:num)/part-payment']['post'] = 'api/v1/part_payment/part_payment/$1';
$route['api/v1/loan/(:num)/reload']['post'] = 'api/v1/part_payment/reload/$1';

// Settlement
$route['api/v1/loan/(:num)/closure-statement']['get'] = 'api/v1/settlement/closure_statement/$1';
$route['api/v1/loan/(:num)/settle']['post'] = 'api/v1/settlement/settle/$1';

// Gold release
$route['api/v1/loan/(:num)/gold-release/verify-id']['post'] = 'api/v1/gold_release/verify_id/$1';
$route['api/v1/loan/goldrelease/(:num)/capture-signature']['post'] = 'api/v1/gold_release/capture_signature/$1';
$route['api/v1/loan/goldrelease/(:num)/complete']['post'] = 'api/v1/gold_release/complete/$1';
