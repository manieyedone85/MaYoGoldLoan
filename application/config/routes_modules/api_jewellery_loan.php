<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// ---------------- Jewellery Evaluation ----------------
$route['api/v1/jewellery/evaluate']['post'] = 'api/v1/jewellery/evaluate';
$route['api/v1/jewellery/rate/current']['get'] = 'api/v1/jewellery/current_rate';
$route['api/v1/jewellery/rate/propose']['post'] = 'api/v1/jewellery/propose_rate';
$route['api/v1/jewellery/rate/(:num)/approve']['post'] = 'api/v1/jewellery/approve_rate/$1';
$route['api/v1/jewellery/(:num)/image']['post'] = 'api/v1/jewellery/upload_image/$1';
$route['api/v1/jewellery/(:num)/barcode']['get'] = 'api/v1/jewellery/barcode/$1';

// ---------------- Loan Core ----------------
$route['api/v1/loan/calculate']['post'] = 'api/v1/loan/calculate';
$route['api/v1/loan']['post'] = 'api/v1/loan/store';
$route['api/v1/loan/pending-approval']['get'] = 'api/v1/loan_approval/pending';
$route['api/v1/loan/(:num)/emi-schedule']['get'] = 'api/v1/loan/emi_schedule/$1';
$route['api/v1/loan/(:num)']['get'] = 'api/v1/loan/show/$1';

// ---------------- Loan Approval (Maker-Checker) ----------------
$route['api/v1/loan/(:num)/submit-for-approval']['post'] = 'api/v1/loan_approval/submit/$1';
$route['api/v1/loan/(:num)/approve']['post'] = 'api/v1/loan_approval/approve/$1';
$route['api/v1/loan/(:num)/reject']['post'] = 'api/v1/loan_approval/reject/$1';
$route['api/v1/loan/(:num)/override']['post'] = 'api/v1/loan_approval/override/$1';
