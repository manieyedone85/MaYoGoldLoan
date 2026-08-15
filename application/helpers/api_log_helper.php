<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('api_request_log')) {

    function api_request_log($apiName, $request = [], $response = [])
    {
        // Basic validation
        if (empty($apiName)) {
            return;
        }

        $CI =& get_instance();

        // Safe log directory
        $logDir = APPPATH . 'logs/api/';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        if (!is_writable($logDir)) {
            return; // fail silently in production
        }

        // Daily log file
        $logFile = $logDir . 'api_' . date('Y-m-d') . '.log';

        // Sanitize request
        $safeRequest = sanitize_log_data($request);
        $safeResponse = sanitize_log_data($response);

        $logData = [
            'time'   => date('Y-m-d H:i:s'),
            'api'    => $apiName,
            'ip'     => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
            'method' => isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '',
            'req'    => $safeRequest,
            'res'    => $safeResponse
        ];

        @file_put_contents(
            $logFile,
            json_encode($logData, JSON_UNESCAPED_SLASHES) . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }
}

/**
 * Remove unsafe / heavy fields from logs
 */
if (!function_exists('sanitize_log_data')) {

    function sanitize_log_data($data)
    {
        if (!is_array($data)) {
            return [];
        }

        $blockedKeys = [
            'password', 'pwd', 'token', 'auth',
            'image', 'images', 'photo', 'base64',
            'file', 'files'
        ];

        $clean = [];

        foreach ($data as $key => $value) {

            // Skip sensitive keys
            if (in_array(strtolower($key), $blockedKeys)) {
                $clean[$key] = '[FILTERED]';
                continue;
            }

            // Prevent huge logs
            if (is_string($value) && strlen($value) > 500) {
                $clean[$key] = '[TRUNCATED]';
                continue;
            }

            // Prevent deep objects
            if (is_array($value)) {
                $clean[$key] = '[ARRAY]';
                continue;
            }

            $clean[$key] = $value;
        }

        return $clean;
    }
}
