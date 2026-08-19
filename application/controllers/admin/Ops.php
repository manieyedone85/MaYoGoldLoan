<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin panel: Ops (Notification Log & Sync Queue Monitor). Read-only
 * troubleshooting views over application/controllers/api/v1/Notification.php
 * and Sync.php -- neither has a workflow to drive (send()'s actual dispatch
 * is a stub, and upload_queue()/download_delta() have no admin-actionable
 * step), so this surfaces the log/queue state rather than porting an action.
 */
class Ops extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->require_admin_role(array('OPERATIONS'));

        $this->load->model('Notification_log_model', 'notification_logs');
        $this->load->model('Notification_template_model', 'notification_templates');
        $this->load->model('Sync_queue_model', 'sync_queues');
    }

    /** GET /admin/ops */
    public function index()
    {
        $notif_search = trim((string) $this->input->get('notif_search'));
        $notif_page = max(1, (int) $this->input->get('notif_page'));
        $notification_logs = $this->notification_logs->paginate(array(), 'id DESC', 15, $notif_page, $notif_search, array('channel', 'status'));

        $queue_search = trim((string) $this->input->get('queue_search'));
        $queue_page = max(1, (int) $this->input->get('queue_page'));
        $sync_queue = $this->sync_queues->paginate(array(), 'id DESC', 15, $queue_page, $queue_search, array('entity_type', 'status'));

        $this->render('ops', array(
            'page_title' => 'Ops',
            'notification_logs' => $notification_logs['data'],
            'notification_pagination' => $notification_logs,
            'notification_filters' => array('notif_search' => $notif_search, 'queue_search' => $queue_search, 'queue_page' => $queue_page),
            'notification_templates' => $this->notification_templates->all(array(), 'code ASC'),
            'sync_queue' => $sync_queue['data'],
            'sync_queue_pagination' => $sync_queue,
            'sync_queue_filters' => array('queue_search' => $queue_search, 'notif_search' => $notif_search, 'notif_page' => $notif_page),
        ));
    }
}
