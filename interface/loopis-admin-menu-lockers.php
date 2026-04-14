<?php
/**
 * Configure menu items for LOOPIS Lockers
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add custom admin menu items
 */
function loopis_admin_menu_lockers($position) {
    // Add menu item
    add_menu_page(
        'Lockers',
        'Lockers',
        'manage_options',
        'loopis-locker-overview',
        'loopis_locker_overview',
        LOOPIS_ADMIN_URL . '/assets/img/wp-admin-menu/icon-locker.png',
        $position
    );

    // Add sub-menus  
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

    // Add hidden edit page (accessible only via direct link)
    add_submenu_page(
        null,
        'Edit locker',
        'Edit locker',
        'manage_options',
        'loopis-locker-edit',
        'loopis_locker_edit'
    );

    // Add hidden add page (accessible only via direct link)
    add_submenu_page(
        null,
        'Add locker',
        'Add locker',
        'manage_options',
        'loopis-locker-add',
        'loopis_locker_add'
    );

    // Return next position for further menu items
    return $position + 1;
}