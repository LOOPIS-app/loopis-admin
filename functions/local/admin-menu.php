<?php
/**
 * Setup of the WP Admin menu for LOOPIS.app
 */

if (!defined('ABSPATH')) {
    exit;
}

// Include functions
require_once LOOPIS_ADMIN_DIR . 'functions/local/admin-menu-posts.php';
require_once LOOPIS_ADMIN_DIR . 'functions/local/admin-menu-cpt.php';
require_once LOOPIS_ADMIN_DIR . 'functions/local/admin-menu-lockers.php';
require_once LOOPIS_ADMIN_DIR . 'functions/local/admin-menu-settings.php';
require_once LOOPIS_ADMIN_DIR . 'functions/local/admin-menu-reorder.php';

// Add action with high priority to run after WordPress core menus
add_action('admin_menu', 'loopis_admin_menu', 999);

function loopis_admin_menu() {
    // Set starting point for configuration
    $position = 5; // "Posts" is at position 5 by default

    // Configure post menu item
    $position = loopis_admin_menu_posts($position);

    // Configure custom post type menu items
    $position = loopis_admin_menu_cpt($position);

    // Configure custom pages menu items
    $position = loopis_admin_menu_lockers($position);
    $position = loopis_admin_menu_settings($position);

    // Reorder menu items
    loopis_admin_menu_reorder($position);
}