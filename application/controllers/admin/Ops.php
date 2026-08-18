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
        $this->render('ops', array(
            'page_title' => 'Ops',
            'notification_logs' => $this->notification_logs->all(array(), 'id DESC'),
            'notification_templates' => $this->notification_templates->all(array(), 'code ASC'),
            'sync_queue' => $this->sync_queues->all(array(), 'id DESC'),
        ));
    }
}
