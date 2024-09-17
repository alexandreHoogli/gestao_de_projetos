<?php 
    $statuses = $this->leads_model->get_status();
    $sources  = $this->leads_model->get_source();
    $staff = $this->staff_model->get('', ['active' => 1]);
?>

<div class="row">
    <div class="col-md-6">
        <i class="fa fa-question-circle padding-5 pull-left" data-toggle="tooltip" data-title="<?php echo _l('phone_number_id'); ?>" data-placement="left"></i>
        <?php echo render_input('settings[phone_number_id]', _l('phone_number_id'), get_option('phone_number_id')); ?>
    </div>
    <div class="col-md-6">
        <i class="fa fa-question-circle padding-5 pull-left" data-toggle="tooltip" data-title="<?php echo _l('phone_number'); ?>" data-placement="left"></i>
        <?php echo render_input('settings[phone_number]', _l('phone_number'), get_option('phone_number')); ?>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <i class="fa fa-question-circle padding-5 pull-left" data-toggle="tooltip" data-title="<?php echo _l('business_account_id_description'); ?>" data-placement="left"></i>
        <?php echo render_input('settings[whatsapp_business_account_id]', _l('whatsapp_business_account_id'), get_option('whatsapp_business_account_id')); ?>
    </div>
    <div class="col-md-6">
        <i class="fa fa-question-circle padding-5 pull-left" data-toggle="tooltip" data-title="<?php echo _l('access_token_description'); ?>" data-placement="left"></i>
        <?php echo render_input('settings[whatsapp_access_token]', _l('whatsapp_access_token'), get_option('whatsapp_access_token')); ?>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <label><?php echo _l('whatsapp_lead_status'); ?></label>
        <select class="selectpicker" data-width="100%" name="settings[whatsapp_lead_status]">
            <?php foreach ($statuses as $status) { ?>
                <option value="<?php echo $status['id']; ?>" <?php if (get_option('whatsapp_lead_status') == $status['id']) echo 'selected'; ?>><?php echo $status['name']; ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="col-md-4">
        <label><?php echo _l('whatsapp_lead_source'); ?></label>
        <select class="selectpicker" data-width="100%" name="settings[whatsapp_lead_source]">
            <?php foreach ($sources as $source) { ?>
                <option value="<?php echo $source['id']; ?>" <?php if (get_option('whatsapp_lead_source') == $source['id']) echo 'selected'; ?>><?php echo $source['name']; ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="col-md-4">
        <label><?php echo _l('whatsapp_lead_assigned'); ?></label>
        <select class="selectpicker" data-width="100%" name="settings[whatsapp_lead_assigned]">
            <?php foreach ($staff as $staff_member) { ?>
                <option value="<?php echo $staff_member['staffid']; ?>" <?php if (get_option('whatsapp_lead_assigned') == $staff_member['staffid']) echo 'selected'; ?>><?php echo htmlspecialchars($staff_member['firstname'] . ' ' . $staff_member['lastname']); ?></option>
            <?php } ?>
        </select>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <label><?php echo _l('whatsapp_webhook'); ?></label>
        <input type="text" name="settings[whatsapp_webhook]" value="<?php echo htmlspecialchars(base_url('whatsapp/webhook/getdata')); ?>" class="form-control" disabled>
    </div>
    <div class="col-md-4">
        <i class="fa fa-question-circle padding-5 pull-left" data-toggle="tooltip" data-title="<?php echo _l('whatsapp_webhook_token'); ?>" data-placement="left"></i>
        <?php echo render_input('settings[whatsapp_webhook_token]', _l('whatsapp_webhook_token'), '123456'); ?>
    </div>
</div>
