<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'id',
    'sent_by',
    'email_subject',
    'total_emails_to_send',
    'emails_sent',
    'emails_failed',
    'created_at'
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'mailflow_newsletter_history';

$join = [];
$where = [];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'id'
]);

$output  = $result['output'];
$rResult = $result['rResult'];


foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {

        $row[] = get_staff_full_name($aRow['sent_by']);

        $row[] = $aRow['email_subject'];

        $row[] = $aRow['total_emails_to_send'];

        $row[] = $aRow['emails_sent'];

        $row[] = $aRow['emails_failed'];

        $row[] = $aRow['created_at'];


        $options = '<div class="tw-flex tw-items-center tw-space-x-3">';
        $options .= '<a href="' . admin_url('mailflow/view_newsletter/' . $aRow['id']) . '" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700">
        <i class="fas fa-eye fa-lg"></i>
    </a>';

        $options .= '</div>';

        $row[]              = $options;
    }

    $output['aaData'][] = $row;
}
