<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

/*
|--------------------------------------------------------------------------
| Admin panel routes (session auth, controllers/admin/*)
|--------------------------------------------------------------------------
*/
$route['admin/login']['get'] = 'admin/auth/login_form';
$route['admin/login']['post'] = 'admin/auth/attempt_login';
$route['admin/logout']['post'] = 'admin/auth/logout';
$route['admin']['get'] = 'admin/dashboard/index';
$route['admin/dashboard']['get'] = 'admin/dashboard/index';
$route['admin/employees']['get'] = 'admin/employees/index';
$route['admin/employees/create']['post'] = 'admin/employees/store';
$route['admin/employees/(:num)']['post'] = 'admin/employees/update/$1';
$route['admin/employees/(:num)/toggle']['post'] = 'admin/employees/toggle_active/$1';
$route['admin/customers']['get'] = 'admin/customers/index';
$route['admin/customers/(:num)']['get'] = 'admin/customers/show/$1';
$route['admin/customers/(:num)/blacklist']['post'] = 'admin/customers/toggle_blacklist/$1';
$route['admin/customers/(:num)/aadhaar-verify']['post'] = 'admin/customers/verify_aadhaar/$1';
$route['admin/customers/(:num)/pan-verify']['post'] = 'admin/customers/verify_pan/$1';
$route['admin/customers/(:num)/nominee']['post'] = 'admin/customers/add_nominee/$1';
$route['admin/customers/(:num)/photo']['get'] = 'admin/customers/photo/$1';
$route['admin/customers/(:num)/photo']['post'] = 'admin/customers/update_photo/$1';
$route['admin/customers/(:num)/details']['post'] = 'admin/customers/update_details/$1';
$route['admin/loans']['get'] = 'admin/loans/index';
$route['admin/loans/create']['get'] = 'admin/loans/create_form';
$route['admin/loans/create']['post'] = 'admin/loans/store';
$route['admin/loans/(:num)']['get'] = 'admin/loans/show/$1';
$route['admin/loans/(:num)/receipt']['get'] = 'admin/loans/receipt/$1';
$route['admin/loans/(:num)/document']['post'] = 'admin/loans/upload_document/$1';
$route['admin/loans/(:num)/cancel']['post'] = 'admin/loans/cancel/$1';
$route['admin/loans/document/(:num)']['get'] = 'admin/loans/download_document/$1';
$route['admin/reports']['get'] = 'admin/reports/index';
$route['admin/reports/export/(:any)']['get'] = 'admin/reports/export/$1';
$route['admin/reports/view/(:any)']['get'] = 'admin/reports/view/$1';
$route['admin/masters']['get'] = 'admin/masters/index';
$route['admin/masters/branch/create']['post'] = 'admin/masters/store_branch';
$route['admin/masters/branch/(:num)']['post'] = 'admin/masters/update_branch/$1';
$route['admin/masters/loan-product/create']['post'] = 'admin/masters/store_loan_product';
$route['admin/masters/loan-product/(:num)']['post'] = 'admin/masters/update_loan_product/$1';
$route['admin/masters/role/create']['post'] = 'admin/masters/store_role';
$route['admin/masters/role/(:num)']['post'] = 'admin/masters/update_role/$1';
$route['admin/masters/vault/create']['post'] = 'admin/masters/store_vault';
$route['admin/masters/vault/(:num)']['post'] = 'admin/masters/update_vault/$1';
$route['admin/masters/gl-account/create']['post'] = 'admin/masters/store_gl_account';
$route['admin/masters/gl-account/(:num)']['post'] = 'admin/masters/update_gl_account/$1';
$route['admin/masters/approval-limit/create']['post'] = 'admin/masters/store_approval_limit';
$route['admin/masters/approval-limit/(:num)']['post'] = 'admin/masters/update_approval_limit/$1';
$route['admin/masters/rate/propose']['post'] = 'admin/masters/propose_rate';
$route['admin/masters/rate/(:num)/approve']['post'] = 'admin/masters/approve_rate/$1';
$route['admin/loan-approvals']['get'] = 'admin/loan_approvals/index';
$route['admin/loan-approvals/(:num)/approve']['post'] = 'admin/loan_approvals/approve/$1';
$route['admin/loan-approvals/(:num)/reject']['post'] = 'admin/loan_approvals/reject/$1';
$route['admin/loan-approvals/(:num)/override']['post'] = 'admin/loan_approvals/override/$1';
$route['admin/disbursements']['get'] = 'admin/disbursements/index';
$route['admin/disbursements/(:num)/disburse']['post'] = 'admin/disbursements/disburse/$1';
$route['admin/disbursements/(:num)/receipt']['get'] = 'admin/disbursements/receipt/$1';
$route['admin/interest-collections']['get'] = 'admin/interest_collections/index';
$route['admin/interest-collections/(:num)/collect']['post'] = 'admin/interest_collections/collect/$1';
$route['admin/interest-collections/(:num)/receipt']['get'] = 'admin/interest_collections/receipt/$1';
$route['admin/part-payments']['get'] = 'admin/part_payments/index';
$route['admin/part-payments/(:num)/pay']['post'] = 'admin/part_payments/pay/$1';
$route['admin/part-payments/(:num)/reload']['post'] = 'admin/part_payments/reload_loan/$1';
$route['admin/topups']['get'] = 'admin/topups/index';
$route['admin/topups/(:num)/add-jewellery']['post'] = 'admin/topups/add_jewellery/$1';
$route['admin/topups/(:num)/approve']['post'] = 'admin/topups/approve/$1';
$route['admin/topups/(:num)/disburse']['post'] = 'admin/topups/disburse/$1';
$route['admin/renewals']['get'] = 'admin/renewals/index';
$route['admin/renewals/(:num)/renew']['post'] = 'admin/renewals/renew/$1';
$route['admin/settlements']['get'] = 'admin/settlements/index';
$route['admin/settlements/(:num)/settle']['post'] = 'admin/settlements/settle/$1';
$route['admin/settlements/(:num)/receipt']['get'] = 'admin/settlements/receipt/$1';
$route['admin/gold-releases']['get'] = 'admin/gold_releases/index';
$route['admin/gold-releases/(:num)']['get'] = 'admin/gold_releases/show/$1';
$route['admin/gold-releases/(:num)/verify-id']['post'] = 'admin/gold_releases/verify_id/$1';
$route['admin/gold-releases/release/(:num)/signature']['post'] = 'admin/gold_releases/capture_signature/$1';
$route['admin/gold-releases/release/(:num)/photo']['post'] = 'admin/gold_releases/capture_photo/$1';
$route['admin/gold-releases/release/(:num)/complete']['post'] = 'admin/gold_releases/complete/$1';
$route['admin/gold-releases/release/(:num)/receipt']['get'] = 'admin/gold_releases/receipt/$1';
$route['admin/jewellery-items']['get'] = 'admin/jewellery_items/index';
$route['admin/jewellery-items/evaluate']['post'] = 'admin/jewellery_items/evaluate';
$route['admin/jewellery-items/image/(:num)']['get'] = 'admin/jewellery_items/download_image/$1';
$route['admin/jewellery-items/(:num)']['get'] = 'admin/jewellery_items/show/$1';
$route['admin/jewellery-items/(:num)/re-evaluate']['post'] = 'admin/jewellery_items/re_evaluate/$1';
$route['admin/jewellery-items/(:num)/image']['post'] = 'admin/jewellery_items/upload_image/$1';
$route['admin/kyc']['get'] = 'admin/kyc/index';
$route['admin/kyc/upload']['post'] = 'admin/kyc/upload';
$route['admin/kyc/(:num)/verify']['post'] = 'admin/kyc/verify/$1';
$route['admin/kyc/(:num)/file']['get'] = 'admin/kyc/file/$1';
$route['admin/inventory']['get'] = 'admin/inventories/index';
$route['admin/inventory/packet']['post'] = 'admin/inventories/store';
$route['admin/inventory/(:num)/transfer']['post'] = 'admin/inventories/transfer/$1';
$route['admin/auctions']['get'] = 'admin/auctions/index';
$route['admin/auctions/schedule']['post'] = 'admin/auctions/schedule';
$route['admin/auctions/(:num)']['get'] = 'admin/auctions/show/$1';
$route['admin/auctions/(:num)/notice']['post'] = 'admin/auctions/notice/$1';
$route['admin/auctions/(:num)/bidder']['post'] = 'admin/auctions/add_bidder/$1';
$route['admin/auctions/(:num)/bid']['post'] = 'admin/auctions/place_bid/$1';
$route['admin/auctions/(:num)/winner']['post'] = 'admin/auctions/declare_winner/$1';
$route['admin/auctions/(:num)/settle']['post'] = 'admin/auctions/settle/$1';
$route['admin/accounting']['get'] = 'admin/accounts/index';
$route['admin/accounting/voucher']['post'] = 'admin/accounts/store_voucher';
$route['admin/ops']['get'] = 'admin/ops/index';

/*
|--------------------------------------------------------------------------
| Public promo page
|--------------------------------------------------------------------------
*/
$route['welcome'] = 'welcome/index';

/*
|--------------------------------------------------------------------------
| API v1 module routes
|--------------------------------------------------------------------------
| Each domain module contributes its own fragment file under
| config/routes_modules/ (e.g. routes_modules/api_auth.php) so multiple
| people/agents can add routes without editing this shared file directly.
*/
foreach (glob(APPPATH . 'config/routes_modules/*.php') as $__route_fragment) {
    require $__route_fragment;
}
unset($__route_fragment);
