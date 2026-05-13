<?php
/**
 * Page with settings for LOOPIS admin.
 */
 
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Function to display content of settings page
function loopis_locker_overview() {

    // Page title and description
    echo '<h1>🔄 Overview</h1>';
    echo '<p>💡 Lockers in your area.</p>';

    global $wpdb;
    $table_name = $wpdb->prefix . 'loopis_lockers';
    $message = '';

    if (!empty($_GET['added'])) {
        $added_id = sanitize_text_field(wp_unslash($_GET['added']));
        $message = '<div class="updated"><p>Locker added: ' . esc_html($added_id) . '</p></div>';
    }

    if (!empty($_GET['deleted'])) {
        $deleted_id = sanitize_text_field(wp_unslash($_GET['deleted']));
        $message = '<div class="updated"><p>Locker removed: ' . esc_html($deleted_id) . '</p></div>';
    }

    if (!empty($_GET['updated'])) {
        $updated_id = sanitize_text_field(wp_unslash($_GET['updated']));
        $message = '<div class="updated"><p>Locker updated: ' . esc_html($updated_id) . '</p></div>';
    }

    // ONE-TIME MIGRATION: Merge leave/fetch flags into one: locker_full
    $columns = $wpdb->get_col("SHOW COLUMNS FROM $table_name");
    $has_full = in_array('locker_full', $columns, true);
    $has_fetch = in_array('fetch_warning', $columns, true);
    $has_leave = in_array('leave_warning', $columns, true);

    if (!$has_full && ($has_fetch || $has_leave)) {
        $wpdb->query("ALTER TABLE $table_name ADD COLUMN locker_full tinyint(1) DEFAULT 0");
        $has_full = true;
    }

    if ($has_full && ($has_fetch || $has_leave)) {
        if ($has_fetch && $has_leave) {
            $wpdb->query("UPDATE $table_name SET locker_full = IF((fetch_warning + leave_warning) > 0, 1, 0)");
        } elseif ($has_fetch) {
            $wpdb->query("UPDATE $table_name SET locker_full = IF(fetch_warning > 0, 1, 0)");
        } else {
            $wpdb->query("UPDATE $table_name SET locker_full = IF(leave_warning > 0, 1, 0)");
        }

        if ($has_fetch) {
            $wpdb->query("ALTER TABLE $table_name DROP COLUMN fetch_warning");
        }
        if ($has_leave) {
            $wpdb->query("ALTER TABLE $table_name DROP COLUMN leave_warning");
        }
    }

    // Fetch all lockers
    $lockers = $wpdb->get_results("SELECT * FROM $table_name ORDER BY postal_code, locker_id");

    if ($message) echo $message;
    if ($lockers) {
        echo '<table class="widefat"><thead><tr><th>Locker ID</th><th>Name</th><th>Postal code</th><th>Locker code</th><th>Warnings</th><th>Edit</th></tr></thead><tbody>';
        foreach ($lockers as $locker) {
            echo '<tr><td>' . esc_html($locker->locker_id) . '</td>';
            echo '<td>' . esc_html($locker->locker_name ?: '—') . '</td>';
            echo '<td>' . esc_html($locker->postal_code) . '</td>';
            echo '<td>' . esc_html($locker->locker_code ?: '—') . '</td>';
            
            // Warnings column
            $warnings = '';
            if (!empty($locker->locker_full)) $warnings .= '⚠ ';
            echo '<td>' . trim($warnings) . '</td>';
            
            // Edit button
            $edit_url = admin_url('admin.php?page=loopis-locker-edit&locker_id=' . urlencode($locker->locker_id));
            echo '<td><a href="' . esc_url($edit_url) . '" class="button">🛠</a></td></tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<p>No lockers added yet.</p>';
    }
    echo '<p><a class="button button-primary" href="' . esc_url(admin_url('admin.php?page=loopis-locker-add')) . '">Add locker</a></p>';
    echo '</div>';
}