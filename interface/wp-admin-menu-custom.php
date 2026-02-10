<?php
/**
 * Custom LOOPIS Admin Menus
 * 
 * Defines Lockers and Settings menu pages.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add custom LOOPIS admin menus
 */
function loopis_add_custom_menus() {
    // Add Lockers menu (shifted to 11 to make room for separator at 10)
    add_menu_page(
        'Locker',
        'Locker',
        'manage_options',
        'loopis-locker-overview',
        'loopis_locker_overview',
        LOOPIS_ADMIN_URL . '/assets/img/wp-admin-menu/icon-locker.png',
        11
    );

    // Lockers sub-menus  
    add_submenu_page(
        'loopis-locker-overview',
        'Overview',
        'Overview',
        'manage_options',
        'loopis-locker-overview',
        'loopis_locker_overview'
    );

    add_submenu_page(
        'loopis-locker-overview',
        'Messages',
        'Messages',
        'manage_options',
        'loopis-locker-messages',
        'loopis_locker_messages'
    );

    // Hidden edit page (accessible only via direct link)
    add_submenu_page(
        null,
        'Edit locker',
        'Edit locker',
        'manage_options',
        'loopis-locker-edit',
        'loopis_locker_edit'
    );

    // Hidden add page (accessible only via direct link)
    add_submenu_page(
        null,
        'Add locker',
        'Add locker',
        'manage_options',
        'loopis-locker-add',
        'loopis_locker_add'
    );

    // Add Settings menu (shifted to 12)
    add_menu_page(
        'Settings',
        'Settings',
        'manage_options',
        'loopis-settings-general',
        'loopis_settings_general',
        LOOPIS_ADMIN_URL . '/assets/img/wp-admin-menu/icon-settings.png',
        12
    );

    // Settings sub-menus
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
        'Postal codes',
        'Postal codes',
        'manage_options',
        'loopis-settings-postal-codes',
        'loopis_settings_postal_codes'
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
}