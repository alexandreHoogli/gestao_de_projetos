<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Get CodeIgniter instance
$CI = &get_instance();

// Define table names with prefix
$interaction_table = db_prefix() . 'whatsapp_interactions';
$interaction_messages_table = db_prefix() . 'whatsapp_interaction_messages';

// Create upload directories if they don't exist
$upload_folders = [
    WHATSAPP_MODULE_UPLOAD_FOLDER,
];

foreach ($upload_folders as $folder) {
    $desired_permission = 0755; // Default permission if not specified
    if (!is_dir($folder)) {
        if (!mkdir($folder, $desired_permission, true)) {
            die('Failed to create directory: ' . $folder);
        }
        // Create index.html file to prevent directory listing
        $fp = fopen($folder . '/index.html', 'w');
        fclose($fp);
    }
}

// Set permissions to 0777 for all folders
foreach ($upload_folders as $folder) {
    if (is_dir($folder)) {
        if (!chmod($folder, 0777)) {
            die('Failed to set permissions for directory: ' . $folder);
        }
    }
}
// Create table for WhatsApp official interactions if it doesn't exist
$create_interaction_table_query = "
    CREATE TABLE IF NOT EXISTS `$interaction_table` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(100) NOT NULL,
        `receiver_id` VARCHAR(20) NOT NULL,
        `last_message` TEXT NULL,
        `time_sent` DATETIME NOT NULL,
        `type` VARCHAR(500) NULL,
        `type_id` VARCHAR(500) NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
";
$CI->db->query($create_interaction_table_query);

// Create table for interaction messages if it doesn't exist
$create_interaction_messages_table_query = "
    CREATE TABLE IF NOT EXISTS `$interaction_messages_table` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `interaction_id` INT(11) UNSIGNED NOT NULL,
        `sender_id` VARCHAR(20) NOT NULL,
        `url` VARCHAR(255) NULL,
        `message` LONGTEXT NOT NULL,
        `status` VARCHAR(20) NULL,
        `time_sent` DATETIME NOT NULL,
        `message_id` VARCHAR(500) NULL,
        `staff_id` VARCHAR(500) NULL,
        `type` VARCHAR(20) NULL,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`interaction_id`) REFERENCES `$interaction_table`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
";
$CI->db->query($create_interaction_messages_table_query);

