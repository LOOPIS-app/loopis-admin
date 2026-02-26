<?php
/**
* Plugin Name:  LOOPIS Admin
* Plugin URI:   https://github.com/LOOPIS-app/loopis-admin
* Description:  Plugin configuring the WP Admin area of LOOPIS.app
* Version:      0.73 (beta)
* Author:       The Develoopers
* Author URI:   https://loopis.org
* Text Domain:  loopis-admin
**/

// Prevent direct access
if (!defined('ABSPATH')) { exit; }

// Run only in admin area
if (!is_admin()) { return; }

// Define plugin version
define('LOOPIS_ADMIN_VERSION', '0.73');

// Define plugin folder path constants
define('LOOPIS_ADMIN_DIR', plugin_dir_path(__FILE__)); // Server-side path to /wp-content/plugins/loopis-admin/
define('LOOPIS_ADMIN_URL', plugin_dir_url(__FILE__));  // Client-side path to https://site.com/wp-content/plugins/loopis-admin/

// Enqueue CSS
add_action('admin_enqueue_scripts', 'loopis_admin_enqueue_assets');

function loopis_admin_enqueue_assets() {
    // Enqueue styles
    wp_enqueue_style(
        'loopis-admin-styles',
        LOOPIS_ADMIN_URL . 'assets/css/loopis-admin.css',
        array(),
        filemtime(LOOPIS_ADMIN_DIR . 'assets/css/loopis-admin.css')
    );
}

// Define folders to load
function loopis_admin_load_files() {
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
        error_log("Failed to include folder: {$folder_name}");
    }
}

// Load files when all plugins are loaded
add_action('plugins_loaded', 'loopis_admin_load_files');