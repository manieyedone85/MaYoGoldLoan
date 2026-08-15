<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mirrors app/Http/Controllers/Api/V1/NotificationController.php.
 * POST /api/v1/notify/{channel} (sms|whatsapp|email|push) — dispatches via a
 * queued job in Laravel (SendNotificationJob::dispatch(...), commented out /
 * not yet implemented there either); here we just log the QUEUED row.
 */
class Notification extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->require_auth();
        $this->require_device_binding();
        $this->load->model('Notification_template_model', 'templates');
        $this->load->model('Notification_log_model', 'logs');
        $this->load->model('Customer_model', 'customers');
    }

    /** POST /api/v1/notify/(sms|whatsapp|email|push) */
    public function send($channel)
    {
        $data = $this->json_input();

        if (empty($data['template_code'])) {
            return json_error('template_code is required.');
        }

        if (! empty($data['customer_id']) && ! $this->customers->find($data['customer_id'])) {
            return json_error('customer_id does not exist.');
        }

        $template = $this->templates->find_by_code($data['template_code']);
        if (! $template) {
            return json_error('template_code does not exist.');
        }

        $log_id = $this->logs->insert(array(
            'customer_id' => $data['customer_id'] ?? null,
            'template_id' => $template['id'],
            'channel' => strtoupper($channel),
            'status' => 'QUEUED',
        ));

        // SendNotificationJob::dispatch($log)->onQueue('notifications');

        $log = $this->logs->find($log_id);
        $this->audit_log('NotificationLog', $log_id, 'SEND', null, $log);

        return json_response(array('data' => $log), 201);
    }
}
