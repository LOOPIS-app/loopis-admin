<?php
/*
Plugin Name: LOOPIS Plugin
Version: 0.6
Author: joxyzan
Author URI: https://loopis.org
Description: Plugin for enhancing the WP Admin area of LOOPIS.app
*/

// Prevent direct access
if (!defined('ABSPATH')) { 
    exit; 
}

// Define plugin folder path constants
define('LOOPIS_PLUGIN_DIR', plugin_dir_path(__FILE__)); // Plugin directory absolute path, for server-side operations
define('LOOPIS_PLUGIN_URL', plugin_dir_url(__FILE__)); // Plugin directory URL, for client-side operations

// Hook into 'plugins_loaded' to ensure all plugins are loaded before initializing
add_action('plugins_loaded', 'loopis_plugin_load_files');

// Define folders to load for administrators in admin area
function loopis_plugin_load_files() {
    if (!current_user_can('administrator') || !is_admin()) {
        return; // Exit early if conditions not met
    }

    // Load all plugin files
    loopis_plugin_include_folder('interface');
    loopis_plugin_include_folder('functions');
    loopis_plugin_include_folder('pages/locker');
    loopis_plugin_include_folder('pages/settings');
}

// Utility function to include all PHP files in a folder
function loopis_plugin_include_folder($folder_name) {
    $absolute_path = LOOPIS_PLUGIN_DIR . '/assets/' . $folder_name;
    if (is_dir($absolute_path)) {
        foreach (glob($absolute_path . '/*.php') as $file) {
            include_once $file;
        }
    } else {
        error_log("loopis-plugin: Failed to include folder from loopis-plugin.php: {$folder_name}");
    }
}

// Register activation hook for setup
require_once LOOPIS_PLUGIN_DIR . '/assets/db/plugin-activation.php';
register_activation_hook(__FILE__, 'loopis_db_setup');