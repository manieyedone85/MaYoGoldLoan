<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// ---------------- Module 17: Auction ----------------
$route['api/v1/auction/schedule']['post'] = 'api/v1/auction/schedule';
$route['api/v1/auction/(:num)/notice']['post'] = 'api/v1/auction/notice/$1';
$route['api/v1/auction/(:num)/bidder']['post'] = 'api/v1/auction/add_bidder/$1';
$route['api/v1/auction/(:num)/bid']['post'] = 'api/v1/auction/place_bid/$1';
$route['api/v1/auction/(:num)/winner']['post'] = 'api/v1/auction/declare_winner/$1';
$route['api/v1/auction/(:num)/settle']['post'] = 'api/v1/auction/settle/$1';

// ---------------- Module 22: Accounting ----------------
$route['api/v1/accounting/voucher']['post'] = 'api/v1/accounting/store_voucher';
$route['api/v1/accounting/ledger/(:num)']['get'] = 'api/v1/accounting/customer_ledger/$1';

// ---------------- Module 23: Inventory ----------------
$route['api/v1/inventory/packet']['post'] = 'api/v1/inventory/store';
$route['api/v1/inventory/packet/(:num)/transfer']['post'] = 'api/v1/inventory/transfer/$1';
$route['api/v1/inventory/vault/(:num)/status']['get'] = 'api/v1/inventory/vault_status/$1';
