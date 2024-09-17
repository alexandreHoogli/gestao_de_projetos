<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Mailflow extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('client_groups_model');
        $this->load->model('tickets_model');
        $this->load->model('leads_model');
        $this->load->model('mailflow_model');
        $this->load->model('staff_model');
    }

    public function index()
    {
        show_404();
    }

    public function manage()
    {

        if (!has_permission('mailflow', '', 'view')) {
            access_denied('mailflow');
        }

        $data = [];

        $data['title'] = _l('mailflow') . ' - ' . _l('mailflow_sends_newsletter');

        $data['clientGroups'] = $this->client_groups_model->get_groups();
        $data['lead_statuses'] = $this->leads_model->get_status();
        $data['lead_sources'] = $this->leads_model->get_source();
        $data['staff_members'] = $this->staff_model->get('', ['is_not_staff' => 0, 'active' => 1]);

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('mailflow', 'table'));
        }

        $this->load->view('true_manage', $data);
    }

    public function history()
    {
        if (!has_permission('mailflow', '', 'view')) {
            access_denied('mailflow');
        }

        $data = [];

        $data['title'] = _l('mailflow') . ' - ' . _l('mailflow_newsletter_history');

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('mailflow', 'table'));
        }

        $this->load->view('manage', $data);
    }

    public function view_newsletter($id)
    {
        if (!has_permission('mailflow', '', 'view')) {
            access_denied('mailflow');
        }

        $data = [];

        $data['title'] = _l('mailflow') . ' - ' . _l('mailflow_newsletter_history');

        $data['newsletterData'] = $this->mailflow_model->get($id);

        $this->load->view('view', $data);
    }

    public function sendEmails()
    {
        if (!has_permission('mailflow', '', 'create')) {
            access_denied('mailflow');
        }

        $sendNewsletterTo = $this->input->post('send_newsletter_to', true);

        $customerStatus = $this->input->post('customers_status', true);
        $customerGroups = $this->input->post('customer_groups', true);
        $customerCountries = $this->input->post('customers_country', true);

        $leadGroups = $this->input->post('lead_groups', true);
        $leadSources = $this->input->post('leads_source', true);
        $leadAssignedToStaff = $this->input->post('leads_assigned_to_staff', true);
        $leadCountries = $this->input->post('leads_country', true);

        $emailSubject = $this->input->post('email_subject');
        $emailContent = $this->input->post('email_content');

        if (empty($sendNewsletterTo)) {
            set_alert('danger', _l('mailflow_please_select_to_who_you_want_to_newsletter'));
            redirect(admin_url('mailflow/manage'));
        }

        $leadsEmails = [];
        $customerEmails = [];

        if (in_array('leads', $sendNewsletterTo)) {
            $leadsEmails = $this->mailflow_model->searchLeadsEmails($leadSources, $leadAssignedToStaff, $leadCountries, $leadGroups);
        }
        if (in_array('customers', $sendNewsletterTo)) {
            $customerEmails = $this->mailflow_model->searchCustomersEmails($customerStatus, $customerGroups, $customerCountries);
        }

        $usersToSendMail = array_merge(
            $leadsEmails,
            $customerEmails
        );
        $usersToSendMail = array_filter($usersToSendMail, 'strlen');
        $usersToSendMail = array_unique($usersToSendMail);


        if (empty($emailSubject)) {
            set_alert('danger', _l('mailflow_please_enter_email_subject'));
            redirect(admin_url('mailflow/manage'));
        }

        if (empty($emailSubject)) {
            set_alert('danger', _l('mailflow_please_enter_email_content'));
            redirect(admin_url('mailflow/manage'));
        }

        if (count($usersToSendMail) === 0 || empty($usersToSendMail)) {
            set_alert('danger', _l('mailflow_no_emails_found'));
            redirect(admin_url('mailflow/manage'));
        }

        $totalEmailsSent = 0;
        $totalEmailsFailed = 0;
        $emailsToSent = 0;

        $this->load->model('emails_model');

        foreach ($usersToSendMail as $email) {

            ++$emailsToSent;

            if ($this->emails_model->send_simple_email($email, $emailSubject, $emailContent)) {

                ++$totalEmailsSent;
                log_activity('Newsletter Sent [To : ' . $email . ']');

            } else {

                ++$totalEmailsFailed;
                log_activity('Newsletter Failed [To : ' . $email . ']');

            }

        }

        if (($totalEmailsFailed + $totalEmailsSent) === $emailsToSent) {

            $this->mailflow_model->add([
                'sent_by' => get_staff_user_id(),
                'email_subject' => $emailSubject,
                'email_content' => $emailContent,
                'total_emails_to_send' => $emailsToSent,
                'emails_sent' => $totalEmailsSent,
                'email_list' => json_encode($usersToSendMail),
                'emails_failed' => $totalEmailsFailed,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            log_activity('Newsletter Sent [Newsletter Subject - ' . $emailSubject . ' - Total Emails: ' . $emailsToSent . ' - Total Emails Sent: ' . $totalEmailsSent . ' - Total Failed Emails: ' . $totalEmailsFailed . ']');

            set_alert('success', _l('mailflow_newsletter_sent_successfully') . ' ' . _l('mailflow_mails_sent') . ' - ' . $totalEmailsSent . ' ' . _l('mailflow_mails_failed') . ' -' . $totalEmailsFailed);
            redirect(admin_url('mailflow/history'));

        }

    }

    public function totalEmailsFound()
    {
        if (!has_permission('mailflow', '', 'create')) {
            access_denied('mailflow');
        }

        $customerStatus = $this->input->post('customers_status', true);
        $customerGroups = $this->input->post('customer_groups', true);
        $customerCountries = $this->input->post('customers_country', true);

        $leadGroups = $this->input->post('lead_groups', true);
        $leadSources = $this->input->post('leads_source', true);
        $leadAssignedToStaff = $this->input->post('leads_assigned_to_staff', true);
        $leadCountries = $this->input->post('leads_country', true);

        $totalLeads = $this->mailflow_model->searchLeadsEmails($leadSources, $leadAssignedToStaff, $leadCountries, $leadGroups);
        $totalCustomers = $this->mailflow_model->searchCustomersEmails($customerStatus, $customerGroups, $customerCountries);

        echo json_encode([
            'total_leads' => count($totalLeads),
            'total_customers' => count($totalCustomers)
        ]);
        die;
    }

}
