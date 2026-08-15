<?php
die('MY_ApiController LOADED');

defined('BASEPATH') OR exit('No direct script access allowed');

class MY_ApiController extends MY_Controller
{
    protected $apiName;
    protected $startTime;
    protected $empId;

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('api_log');
        $this->load->model('ApiAuthModel');
        $this->load->model('ApiMetricsModel');
        header('Content-Type: application/json');

        // Allow CORS (mobile apps)
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            show_404();
        }

        // Capture API name & start time
        $this->apiName   = $this->router->fetch_method();
        $this->startTime = microtime(true);
        // 🔐 Validate token
        $this->validateToken();

        // AUTO log request (safe)
        api_request_log(
            $this->apiName . '_request',
            $_REQUEST
        );
    }

    public function __destruct()
    {
        $executionMs = round((microtime(true) - $this->startTime) * 1000, 2);
        $httpCode   = http_response_code();

        // Store metrics
        $this->ApiMetricsModel->insertMetric([
            'emp_id'       => $this->empId,
            'api_name'     => $this->apiName,
            'http_code'    => $httpCode,
            'execution_ms' => $executionMs
        ]);

        // Log response
        api_request_log(
            $this->apiName . '_response',
            [],
            ['http_code'=>$httpCode,'execution_ms'=>$executionMs]
        );
    }

    /* ---------- TOKEN VALIDATION ---------- */

    protected function validateToken()
    {
        $token = $this->input->get_request_header('Authorization');

        if (!$token) {
            $this->fail('Authorization token missing', 401);
        }

        $token = str_replace('Bearer ', '', $token);

        $user = $this->ApiAuthModel->validateToken($token);

        if (!$user) {
            $this->fail('Invalid or expired token', 401);
        }

        $this->empId = $user['user_id'];
    }

    /* -----------------------------------
       Common validation helpers
    ----------------------------------- */

    protected function requireParams(array $params)
    {
        foreach ($params as $param) {
            if (empty($_REQUEST[$param])) {
                $this->fail("$param is required");
            }
        }
    }

    protected function fail($message, $code = 400)
    {
        http_response_code($code);
        echo json_encode([
            'status'  => 'failure',
            'message' => $message
        ]);
        exit;
    }

    protected function success($data = [])
    {
        echo json_encode([
            'status' => 'success',
            'datas'   => $data
        ]);
        exit;
    }
}
