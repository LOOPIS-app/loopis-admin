<?php
/**
 * Helper function for deleting a setting in the loopis_settings table.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function loopis_delete_setting($key) {
    global $wpdb;
    $table = $wpdb->prefix . 'loopis_settings';

    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE setting_key = %s",
        $key
    ));

    if (!$exists) {
        return false;
    }

    return (bool) $wpdb->delete($table, ['setting_key' => $key]);
}
