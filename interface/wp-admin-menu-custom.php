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
        'Skåp',
        'Skåp',
        'manage_options',
        'loopis-locker-general',
        'loopis_locker_general',
        LOOPIS_ADMIN_URL . '/assets/img/wp-admin-menu/icon-locker.png',
        11
    );

    // Lockers sub-menus  
    add_submenu_page(
        'loopis-locker-general',
        'Aktiva skåp',
        'Aktiva skåp',
        'manage_options',
        'loopis-locker-general',
        'loopis_locker_general'
    );

    add_submenu_page(
        'loopis-locker-general',
        'Redigera skåp',
        'Redigera skåp',
        'manage_options',
        'loopis-locker-edit',
        'loopis_locker_edit'
    );

    add_submenu_page(
        'loopis-locker-general',
        'Meddelanden',
        'Meddelanden',
        'manage_options',
        'loopis-locker-messages',
        'loopis_locker_messages'
    );

    // Add Settings menu (shifted to 12)
    add_menu_page(
        'Inställningar',
        'Inställningar',
        'manage_options',
        'loopis-settings-general',
        'loopis_settings_general',
        LOOPIS_ADMIN_URL . '/assets/img/wp-admin-menu/icon-settings.png',
        12
    );

    // Settings sub-menus
    add_submenu_page(
        'loopis-settings-general',
        'Allmänt',
        'Allmänt',
        'manage_options',
        'loopis-settings-general',
        'loopis_settings_general'
    );

    add_submenu_page(
        'loopis-settings-general',
        'Postnummer',
        'Postnummer',
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
        'Roller',
        'Roller',
        'manage_options',
        'loopis-settings-roles',
        'loopis_settings_roles'
    );

    add_submenu_page(
        'loopis-settings-general',
        'Välkomstmail',
        'Välkomstmail',
        'manage_options',
        'loopis-settings-welcome',
        'loopis_settings_welcome'
    );
}