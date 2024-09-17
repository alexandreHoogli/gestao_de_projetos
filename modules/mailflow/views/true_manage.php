<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">

            <div class="col-md-12">
                <h2>
                    <?php echo $title; ?>
                </h2>
            </div>

            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">

                        <div class="horizontal-scrollable-tabs panel-full-width-tabs">
                            <div class="scroller arrow-left" style="display: none;"><i class="fa fa-angle-left"></i>
                            </div>
                            <div class="scroller arrow-right" style="display: none;"><i class="fa fa-angle-right"></i>
                            </div>
                            <div class="horizontal-tabs">
                                <ul class="nav nav-tabs nav-tabs-horizontal" role="tablist">
                                    <li role="presentation" class="active">
                                        <a href="#customers" aria-controls="customers" role="tab" data-toggle="tab"
                                           aria-expanded="true"><?php echo _l('customers'); ?></a>
                                    </li>
                                    <li role="presentation" class="">
                                        <a href="#leads" aria-controls="leads" role="tab" data-toggle="tab"
                                           aria-expanded="true"><?php echo _l('leads'); ?></a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <?php echo form_open(admin_url('mailflow/sendEmails'), ['id' => 'mailflow-form']); ?>
                        <div class="tab-content">

                            <div class="col-md-12">
                                <?php echo render_select('send_newsletter_to[]', [['id' => 'customers', 'name' => _l('customers')], ['id' => 'leads', 'name' => _l('leads')]], ['id', 'name'], 'emailflow_send_newsletter_to', '', ['multiple' => true], [], '', '', false); ?>
                                <hr class="hr">
                            </div>

                            <div role="tabpanel" class="tab-pane active" id="customers">

                                <div class="col-md-12">
                                    <?php echo render_select('customers_status', [['id' => 'active', 'name' => _l('mailflow_customers_active')], ['id' => 'inactive', 'name' => _l('mailflow_customers_inactive')]], ['id', 'name'], 'emailflow_customers_db_status', '', [], [], '', '', false); ?>
                                </div>

                                <div class="col-md-6">
                                    <?php echo render_select('customer_groups[]', $clientGroups, ['id', 'name'], 'emailflow_customers_group_list', '', ['multiple' => true], [], '', '', false); ?>
                                </div>

                                <div class="col-md-6">
                                    <?php echo render_select('customers_country[]', get_all_countries(), ['country_id', ['short_name'], 'iso2'], 'mailflow_customers_country', '', ['multiple' => true], [], '', '', false); ?>
                                </div>
                            </div>

                            <div role="tabpanel" class="tab-pane" id="leads">

                                <div class="col-md-12">
                                    <?php echo render_select('lead_groups[]', $lead_statuses, ['id', 'name'], 'emailflow_leads_group_list', '', ['multiple' => true], [], '', '', false); ?>
                                </div>

                                <div class="col-md-6">
                                    <?php echo render_select('leads_source[]', $lead_sources, ['id', 'name'], 'mailflow_lead_sources', '', ['multiple' => true], [], '', '', false); ?>
                                </div>

                                <div class="col-md-6">
                                    <?php echo render_select('leads_assigned_to_staff[]', $staff_members, ['staffid', ['firstname', 'lastname']], 'mailflow_leads_assiged_to', '', ['multiple' => true], [], '', '', false); ?>
                                </div>

                                <div class="col-md-12">
                                    <?php echo render_select('leads_country[]', get_all_countries(), ['country_id', ['short_name'], 'iso2'], 'mailflow_leads_country', '', ['multiple' => true], [], '', '', false); ?>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">

                        <div class="col-md-12">
                            <?php echo render_input('email_subject', 'mailflow_email_subject'); ?>
                        </div>

                        <div class="col-md-12">
                            <?php echo render_textarea('email_content', '', '', ['rows' => 10], [], '', 'tinymce'); ?>
                        </div>

                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">
                                        <?php echo _l('mailflow_total_customers_emails'); ?>
                                        <span id="totalCustomers" class="badge badge-primary">0</span>
                                        <?php echo _l('mailflow_total_leads_emails'); ?>
                                        <span id="totalLeads" class="badge badge-success">0</span>
                                    </h4>
                                </div>
                            </div>
                        </div>



                        <div class="btn-bottom-toolbar text-right">
                            <button type="submit"
                                    class="btn btn-primary pull-right"><?php echo _l('mailflow_sends_newsletter'); ?></button>
                        </div>
                    </div>
                </div>
            </div>
            <?php echo form_close(); ?>

        </div>

    </div>
</div>
<?php init_tail(); ?>
<script>
    'use strict';

    $(document).ready(function() {

        sendRequest();

        var formData = $('#mailflow-form').serialize();

        $('#mailflow-form input, #mailflow-form select').on('input change', function() {
            sendRequest();
        });

        function sendRequest() {
            var newFormData = $('#mailflow-form').serialize();

            if (formData !== newFormData) {
                formData = newFormData;

                $.ajax({
                    url: '<?php echo admin_url('mailflow/totalEmailsFound') ?>',
                    type: 'POST',
                    data: newFormData,
                    success: function(response) {
                        response = JSON.parse(response);

                        $('#totalLeads').text(response.total_leads);
                        $('#totalCustomers').text(response.total_customers);
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr.responseText);
                    }
                });
            }
        }
    });

</script>
</body>
</html>
