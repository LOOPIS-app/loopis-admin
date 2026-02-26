<?php
/**
 * Configure menu items for LOOPIS Settings
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add custom admin menu items
 */
function loopis_admin_menu_settings($position) {
    // Add menu item
    add_menu_page(
        'Settings',
        'Settings',
        'manage_options',
        'loopis-settings-general',
        'loopis_settings_general',
        LOOPIS_ADMIN_URL . '/assets/img/wp-admin-menu/icon-settings.png',
        $position
    );

    // Add sub-menus
    add_submenu_page(
        'loopis-settings-general',
        'General',
        'General',
        'manage_options',
        'loopis-settings-general',
        'loopis_settings_general'
    );

    add_submenu_page(
        'loopis-settings-general',
        'Reports',
        'Reports',
        'manage_options',
        'loopis-settings-reports',
        'loopis_settings_reports'
    );

    add_submenu_page(
        'loopis-settings-general',
        'Event',
        'Event',
        'manage_options',
        'loopis-settings-event',
        'loopis_settings_event'
    );
    
    add_submenu_page(
        'loopis-settings-general',
        'Roles',
        'Roles',
        'manage_options',
        'loopis-settings-roles',
        'loopis_settings_roles'
    );

    add_submenu_page(
        'loopis-settings-general',
        'Welcome email',
        'Welcome email',
        'manage_options',
        'loopis-settings-welcome',
        'loopis_settings_welcome'
    );

    // Return next position for further menu items
    return $position + 1;
}