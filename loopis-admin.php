<?php
/*
Plugin Name: LOOPIS Admin
Plugin URI: https://github.com/LOOPIS-app/loopis-admin
Version: 0.7
Author: joxyzan
Author URI: https://loopis.org
Description: Plugin for enhancing the WP Admin area of LOOPIS.app
*/

// Prevent direct access
if (!defined('ABSPATH')) { 
    exit; 
}

// Define plugin version
define('LOOPIS_ADMIN_VERSION', '0.7');

// Define plugin folder path constants
define('LOOPIS_ADMIN_DIR', plugin_dir_path(__FILE__)); // Server-side path to /wp-content/plugins/loopis-admin/
define('LOOPIS_ADMIN_URL', plugin_dir_url(__FILE__));  // Client-side path to https://site.com/wp-content/plugins/loopis-admin/

// Hook into 'plugins_loaded' to ensure all plugins are loaded before initializing
add_action('plugins_loaded', 'loopis_admin_load_files');

// Define folders to load for administrators in admin area
function loopis_admin_load_files() {
    if (!current_user_can('administrator') || !is_admin()) {
        return; // Exit early
    }

    // Load all plugin files
    loopis_admin_include_folder('interface');
    loopis_admin_include_folder('functions');
    loopis_admin_include_folder('pages/locker');
    loopis_admin_include_folder('pages/settings');
}

// Utility function to include all PHP files in a folder
function loopis_admin_include_folder($folder_name) {
    $absolute_path = LOOPIS_ADMIN_DIR . '/' . $folder_name;
    if (is_dir($absolute_path)) {
        foreach (glob($absolute_path . '/*.php') as $file) {
            include_once $file;
        }
    } else {
        error_log("loopis-admin: Failed to include folder from loopis-admin.php: {$folder_name}");
    }
}