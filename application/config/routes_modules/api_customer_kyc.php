<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// ---------------- Module 2: Customer Management ----------------
$route['api/v1/customer']['post'] = 'api/v1/customer/store';
$route['api/v1/customer/search']['get'] = 'api/v1/customer/search';
$route['api/v1/customer/duplicate-check']['post'] = 'api/v1/customer/duplicate_check';
$route['api/v1/customer/(:num)']['get'] = 'api/v1/customer/show/$1';
$route['api/v1/customer/(:num)/nominee']['post'] = 'api/v1/customer/add_nominee/$1';
$route['api/v1/customer/(:num)/family-member']['post'] = 'api/v1/customer/add_family_member/$1';
$route['api/v1/customer/(:num)/merge']['post'] = 'api/v1/customer/merge/$1';

// ---------------- Module 3-5: KYC ----------------
$route['api/v1/kyc/aadhaar/qr-scan']['post'] = 'api/v1/kyc_aadhaar/qr_scan';
$route['api/v1/kyc/aadhaar/offline-xml']['post'] = 'api/v1/kyc_aadhaar/offline_xml';
$route['api/v1/kyc/aadhaar/face-auth']['post'] = 'api/v1/kyc_aadhaar/face_auth';
$route['api/v1/kyc/aadhaar/(:num)/masked']['get'] = 'api/v1/kyc_aadhaar/masked/$1';

$route['api/v1/kyc/pan/validate']['post'] = 'api/v1/kyc_pan/validate_pan';

$route['api/v1/kyc/document']['post'] = 'api/v1/kyc_document/store';
$route['api/v1/kyc/document-types']['get'] = 'api/v1/kyc_document/document_types_index';
$route['api/v1/kyc/document/(:num)']['get'] = 'api/v1/kyc_document/index/$1';
$route['api/v1/kyc/document/(:num)/verify']['put'] = 'api/v1/kyc_document/verify/$1';
