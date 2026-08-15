<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Send a JSON response and stop further script execution, mirroring the
 * Laravel API's `response()->json([...], $status)` shape used throughout
 * the parent app's Api\V1 controllers: {"data": ...} or {"message": ...}.
 */
function json_response($body, $status = 200)
{
    $ci =& get_instance();
    $ci->output
        ->set_status_header($status)
        ->set_content_type('application/json', 'utf-8')
        ->set_output(json_encode($body));
}

function json_error($message, $status = 422)
{
    json_response(array('message' => $message), $status);
}
