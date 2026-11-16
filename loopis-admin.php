<?php
/**
* Plugin Name:  LOOPIS Admin
* Plugin URI:   https://github.com/LOOPIS-app/loopis-admin
* Description:  Plugin for configuring the WP Admin area of LOOPIS.app
* Version:      0.76
* Author:       The Develoopers
* Author URI:   https://loopis.org
* License:      GPL-3.0-or-later
* License URI:  https://www.gnu.org/licenses/gpl-3.0.html
* Text Domain:  loopis-admin
**/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Run only in admin area
if (!is_admin()) {
    return;
}

// Define plugin folder path constants
define('LOOPIS_ADMIN_DIR', plugin_dir_path(__FILE__)); // Server-side path to /wp-content/plugins/loopis-admin/
define('LOOPIS_ADMIN_URL', plugin_dir_url(__FILE__));  // Client-side path to https://site.com/wp-content/plugins/loopis-admin/

// Enqueue CSS
add_action('admin_enqueue_scripts', 'loopis_admin_enqueue_assets');
function loopis_admin_enqueue_assets() {
    $stylesheet = LOOPIS_ADMIN_DIR . 'assets/loopis-admin.css';
    if (is_file($stylesheet)) {
        wp_enqueue_style('loopis-admin-styles', LOOPIS_ADMIN_URL . 'assets/loopis-admin.css', array(), filemtime($stylesheet));
    }
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

// Define folders to load
function loopis_admin_subsite_files() {
    loopis_admin_include_folder('filters');
    loopis_admin_include_folder('menu/subsite');
}

function loopis_admin_mainsite_files() {
    loopis_admin_include_folder('filters');
    loopis_admin_include_folder('menu/mainsite');
}

// Load different files for main and sub site
if ( is_main_site() ) :
    add_action('plugins_loaded', 'loopis_admin_mainsite_files');
else :
    add_action('plugins_loaded', 'loopis_admin_subsite_files');
endif; 