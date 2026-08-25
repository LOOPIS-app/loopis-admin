<?php
/**
* Plugin Name:  LOOPIS Admin
* Plugin URI:   https://github.com/LOOPIS-app/loopis-admin
* Description:  Plugin for configuring the WP Admin area of LOOPIS.app
* Version:      0.75
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

// Skip for frontend
if (!is_admin()) { return; }

// Define plugin version
define('LOOPIS_ADMIN_VERSION', '0.75'); // Update version number here + add to CHANGELOG.md

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
function loopis_admin_load_files() {
    loopis_admin_include_folder('functions/common');
    loopis_admin_include_folder('functions/local');
}

function loopis_admin_hq_load_files() {
    loopis_admin_include_folder('functions/common');
    loopis_admin_include_folder('functions/hq');
}

// Load different files for main site and single/sub-sites (when all plugins are loaded)
if ( is_multisite() && is_main_site() ) :

    // Load files for main site
    add_action('plugins_loaded', 'loopis_admin_hq_load_files');

else :

    // Load files for single/sub-sites
    add_action('plugins_loaded', 'loopis_admin_load_files');

endif;