<?php
/**
 * Helper functions for custom plugin settings stored in the loopis_settings table.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function loopis_update_setting($key, $value) {
    global $wpdb;
    $table = $wpdb->prefix . 'loopis_settings';
    $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE setting_key = %s", $key));
    if ($exists) {
        $wpdb->update($table, ['setting_value' => $value], ['setting_key' => $key]);
    } else {
        $wpdb->insert($table, ['setting_key' => $key, 'setting_value' => $value]);
    }
}