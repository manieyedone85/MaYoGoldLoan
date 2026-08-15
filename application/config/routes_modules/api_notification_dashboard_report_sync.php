<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Notifications, Dashboard (API), Reports, Sync — mirrors the matching
 * blocks in ../routes/api.php (Modules 18, 20, 21, 25).
 */

// ---------------- Module 18: Notifications ----------------
$route['api/v1/notify/(:any)']['post'] = 'api/v1/notification/send/$1';

// ---------------- Module 20: Dashboard ----------------
$route['api/v1/dashboard/summary']['get'] = 'api/v1/dashboard/summary';

// ---------------- Module 21: Reports ----------------
$route['api/v1/reports/(:any)']['get'] = 'api/v1/report/generate/$1';

// ---------------- Module 25: Offline Sync ----------------
$route['api/v1/sync/upload-queue']['post'] = 'api/v1/sync/upload_queue';
$route['api/v1/sync/download-delta']['get'] = 'api/v1/sync/download_delta';
