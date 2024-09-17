<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">
                    <?php echo $title; ?>
                </h4>
                <div class="panel_s">
                    <div class="col-md-12 panel-body">
                        <?php render_datatable([
                            _l('mailflow_sent_by'),
                            _l('mailflow_email_subject'),
                            _l('mailflow_total_emails_to_send'),
                            _l('mailflow_emails_sent'),
                            _l('mailflow_emails_failed'),
                            _l('mailflow_created_at'),
                            _l('options'),
                        ], 'newsletter-list'); ?>

                    </div>
                </div>
            </div>
        </div>

        <div class="btn-bottom-pusher"></div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(document).ready(function() {
        "use strict";
        $(function() {
            initDataTable('.table-newsletter-list', window.location.href, [5], [5], [], [5, 'desc']);
        });
    });
</script>
</body>
</html>