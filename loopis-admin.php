<?php
/**
* Plugin Name:  LOOPIS Admin
* Plugin URI:   https://github.com/LOOPIS-app/loopis-admin
* Description:  Plugin for configuring the WP Admin area of LOOPIS.app
* Version:      0.73 (beta)
* Author:       The Develoopers
* Author URI:   https://loopis.org
* License:      GPL-3.0-or-later
* License URI:  https://www.gnu.org/licenses/gpl-3.0.html
* Text Domain:  loopis-admin
**/

/*
 * Copyright (C) 2026 LOOPIS association
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

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