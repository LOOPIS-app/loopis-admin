<?php
/**
 * WordPress Admin Menu Setup
 * 
 * Coordinates the setup of custom admin menus and reordering.
 */

if (!defined('ABSPATH')) {
    exit;
}

// Include menu configuration files
require_once LOOPIS_ADMIN_DIR . 'interface/wp-admin-menu-posts.php';
require_once LOOPIS_ADMIN_DIR . 'interface/wp-admin-menu-custom.php';
require_once LOOPIS_ADMIN_DIR . 'interface/wp-admin-menu-reorder.php';

// Add the admin menu pages with high priority to run after WordPress core menus
add_action('admin_menu', 'loopis_custom_admin_menu', 999);

function loopis_custom_admin_menu() {
    // Setup post type menus (from wp-admin-menu-posts.php)
    loopis_setup_post_menus();

    // Add custom LOOPIS menus (from wp-admin-menu-custom.php)
    loopis_add_custom_menus();

    // Reorder standard WordPress menus (from wp-admin-menu-reorder.php)
    loopis_reorder_admin_menu();
}