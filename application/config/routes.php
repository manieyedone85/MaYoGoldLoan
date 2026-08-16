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
$route['admin/loans']['get'] = 'admin/loans/index';
$route['admin/loans/create']['get'] = 'admin/loans/create_form';
$route['admin/loans/create']['post'] = 'admin/loans/store';
$route['admin/loans/(:num)']['get'] = 'admin/loans/show/$1';
$route['admin/reports']['get'] = 'admin/reports/index';
$route['admin/masters']['get'] = 'admin/masters/index';
$route['admin/masters/branch/create']['post'] = 'admin/masters/store_branch';
$route['admin/masters/branch/(:num)']['post'] = 'admin/masters/update_branch/$1';
$route['admin/masters/loan-product/create']['post'] = 'admin/masters/store_loan_product';
$route['admin/masters/loan-product/(:num)']['post'] = 'admin/masters/update_loan_product/$1';
$route['admin/masters/role/create']['post'] = 'admin/masters/store_role';
$route['admin/masters/role/(:num)']['post'] = 'admin/masters/update_role/$1';

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
