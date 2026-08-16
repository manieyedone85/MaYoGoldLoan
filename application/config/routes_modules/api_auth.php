<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['api/v1/auth/login']['post'] = 'api/v1/auth/login';
$route['api/v1/auth/otp/send']['post'] = 'api/v1/auth/send_otp';
$route['api/v1/auth/otp/verify']['post'] = 'api/v1/auth/verify_otp';
$route['api/v1/auth/refresh-token']['post'] = 'api/v1/auth/refresh_token';
$route['api/v1/auth/logout']['post'] = 'api/v1/auth/logout';
$route['api/v1/auth/device/bind']['post'] = 'api/v1/auth/bind_device_endpoint';
$route['api/v1/auth/mpin/set']['post'] = 'api/v1/auth/set_mpin';
$route['api/v1/auth/biometric/enroll']['post'] = 'api/v1/auth/enroll_biometric';
$route['api/v1/auth/biometric/login']['post'] = 'api/v1/auth/biometric_login';
