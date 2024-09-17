<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: WhatsApp Cloud API Interaction Module
Description: The first real-time interaction module for Perfex CRM, to interact with your clients through Admin area.
Version: 1.2.1
Requires at least: 2.3.*
*/

define('WHATSAPP_MODULE_NAME', 'whatsapp');
include( __DIR__ . '/vendor/autoload.php');
$CI = &get_instance();

//Register language files
register_language_files(WHATSAPP_MODULE_NAME, [WHATSAPP_MODULE_NAME]);

// Define constants for upload folders
define('WHATSAPP_MODULE_UPLOAD_FOLDER', 'uploads/' . WHATSAPP_MODULE_NAME);
define('WHATSAPP_MODULE_UPLOAD_URL', 'https://' . $_SERVER['HTTP_HOST'] . '/' . WHATSAPP_MODULE_UPLOAD_FOLDER . '/');

// Create upload directories if they don't exist
$upload_folders = [
    WHATSAPP_MODULE_UPLOAD_FOLDER,
];
    if (!is_dir(WHATSAPP_MODULE_UPLOAD_FOLDER)) {
        if (!mkdir(WHATSAPP_MODULE_UPLOAD_FOLDER, 0755, true)) {
            die('Failed to create directory: ' . WHATSAPP_MODULE_UPLOAD_FOLDER);
        }
        $fp = fopen(WHATSAPP_MODULE_UPLOAD_FOLDER . '/index.html', 'w');
        fclose($fp);
    }

//Initializes the WhatsApp gateway for sending SMS messages.
hooks()->add_filter('sms_gateways', 'whatsapp_gateway_sms_gateways');

function whatsapp_gateway_sms_gateways($gateways)
{
    $gateways[] = 'whatsapp/sms_whatsapp_gateway';
    return $gateways;
}

//Function to handle module activation
 register_activation_hook(WHATSAPP_MODULE_NAME, 'whatsapp_activation_hook');

function whatsapp_activation_hook()
{
    require_once __DIR__ . '/install.php';
}

//Register module activation hook
register_activation_hook(__FILE__, 'whatsapp_activate');

//Initialize permissions
function whatsapp_init_permissions()
{
   $capabilities = [];

	$capabilities['capabilities'] = [
		'view' => _l('permission_view'),
		'create' => _l('permission_create'),
	];
    register_staff_capabilities(WHATSAPP_MODULE_NAME, $capabilities, _l('whatsapp'));
}

// Menu Items
function whatsapp_init_menu_items()
{
    $CI = &get_instance();
    // Check permissions
    if (has_permission('whatsapp_view')) {
		if (get_option('phone_number_id') && get_option('whatsapp_access_token')&& get_option('whatsapp_access_token')&& get_option('whatsapp_access_token')) {
            $CI->app_menu->add_sidebar_menu_item('whatsapp_interaction', [
                'slug' => 'whatsapp/interaction',
                'name' => _l('WhatsApp Chat'),
                'icon' => 'fab fa-whatsapp', // Adjust the icon as needed
                'href' => admin_url('whatsapp/interaction'),
                'position' => 1,
		]);
	}
        $CI->app_tabs->add_settings_tab('whatsapp_interaction', [
            'name' => _l('settings_group_whatsapp_interaction'),
            'view' => 'whatsapp/admin/settings',
            'position' => 8,
            'icon' => 'fa fa-user-plus',
        ]);
    }
}

// Head components
function whatsapp_add_head_components(){
    $CI = &get_instance();
	$viewuri = $_SERVER['REQUEST_URI'];
	if (strpos($viewuri, '/admin/whatsapp/interaction') !== false) {
		echo '<link href="' . base_url('modules/whatsapp/assets/interaction.css') .'"  rel="stylesheet" type="text/css" />';
		echo '<link href="' . base_url('modules/whatsapp/assets/twailwind.css') .'"  rel="stylesheet" type="text/css" />';
		echo '<link href="' . base_url('modules/whatsapp/assets/fa.css') .'"  rel="stylesheet" type="text/css" />';
	}
}

// Hooks
hooks()->add_action('admin_init', 'whatsapp_init_menu_items');
hooks()->add_action('admin_init', 'whatsapp_init_permissions');
hooks()->add_action('app_admin_head', 'whatsapp_add_head_components');