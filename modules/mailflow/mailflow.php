<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: MailFlow - Customers & Leads Newsletter
Description: Effortlessly send newsletters to customers and leads in Perfex CRM. Streamline communication and drive conversions with ease.
Version: 1.0.0
Author: LenzCreative
Author URI: https://codecanyon.net/user/lenzcreativee/portfolio
Requires at least: 1.0.*
*/

define('MAILFLOW_MODULE_NAME', 'mailflow');

hooks()->add_action('admin_init', 'mailflow_module_init_menu_items');
hooks()->add_action('admin_init', 'mailflow_permissions');


/**
 * Load the module helper
 */
$CI = & get_instance();
$CI->load->helper(MAILFLOW_MODULE_NAME . '/mailflow'); //on module main file

function mailflow_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
        'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
        'create' => _l('permission_create'),
        'edit'   => _l('permission_edit'),
        'delete' => _l('permission_delete')
    ];

    register_staff_capabilities('mailflow', $capabilities, _l('mailflow'));
}

/**
 * Register activation module hook
 */
register_activation_hook(MAILFLOW_MODULE_NAME, 'mailflow_module_activation_hook');

function mailflow_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(MAILFLOW_MODULE_NAME, [MAILFLOW_MODULE_NAME]);

/**
 * Init module menu items in setup in admin_init hook
 * @return null
 */
function mailflow_module_init_menu_items()
{
    $CI = &get_instance();

    if (has_permission('mailflow', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('mailflow', [
            'slug' => 'mailflow',
            'name' => _l('mailflow'),
            'position' => 6,
            'href'     => admin_url('mailflow'),
            'icon' => 'far fa-envelope'
        ]);
    }

    if (has_permission('mailflow', '', 'view')) {
        $CI->app_menu->add_sidebar_children_item('mailflow', [
            'slug' => 'mailflow-view',
            'name' => _l('mailflow_newsletter'),
            'href' => admin_url('mailflow/manage'),
            'position' => 11,
        ]);
    }

    if (has_permission('mailflow', '', 'view')) {
        $CI->app_menu->add_sidebar_children_item('mailflow', [
            'slug' => 'mailflow-view',
            'name' => _l('mailflow_newsletter_history'),
            'href' => admin_url('mailflow/history'),
            'position' => 11,
        ]);
    }

}