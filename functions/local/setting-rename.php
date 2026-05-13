<?php
/**
 * Helper function for renaming a setting in the loopis_settings table.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function loopis_rename_setting($old_key, $new_key) {
    global $wpdb;
    $table = $wpdb->prefix . 'loopis_settings';

    $old_exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE setting_key = %s",
        $old_key
    ));

    if (!$old_exists) {
        return false;
    }

    $new_exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE setting_key = %s",
        $new_key
    ));

    if ($new_exists) {
        return false;
    }

    return (bool) $wpdb->update(
        $table,
        ['setting_key' => $new_key],
        ['setting_key' => $old_key]
    );
}
