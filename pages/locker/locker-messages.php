<?php
/**
 * Page with settings for LOOPIS admin.
 */
 
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Function to display content of settings page
function loopis_locker_messages() {
    // ONE-TIME MIGRATION: Merge leave/fetch warnings into one: locker_full_warning
    if (function_exists('loopis_rename_setting') && function_exists('loopis_delete_setting')) {
        $full_exists = loopis_get_setting('locker_full_warning', null);
        if ($full_exists === null) {
            loopis_rename_setting('locker_fetch_warning', 'locker_full_warning');
            loopis_delete_setting('locker_leave_warning');
        }
    }

    // Handle form submission
    if (isset($_POST['submit_locker_messages']) && wp_verify_nonce($_POST['locker_messages_nonce'], 'looper_messages_action')) {
        // Update full locker warning
        if (isset($_POST['locker_full_warning'])) {
            $full_warning = wp_kses_post(wp_unslash($_POST['locker_full_warning']));
            loopis_update_setting('locker_full_warning', loopis_setting_textarea_to_br($full_warning));
        }
        
        echo '<div class="notice notice-success is-dismissible"><p>Settings saved!</p></div>';
    }
    
    // Get current values
    $full_warning = loopis_setting_textarea_from_br(loopis_get_setting('locker_full_warning', ''));

    
    // Page title and description
    echo '<div class="wrap"><h1>📡 Messages</h1>';
    echo '<p>💡 Message output is enabled in locker overview.</p>';
    
    echo '<form method="post" action="">';
    wp_nonce_field('looper_messages_action', 'locker_messages_nonce');
    
    echo '<table class="form-table">';
    
    // Full locker warning
    echo '<tr>';
    echo '<th scope="row">💥 Full locker warning</th>';
    echo '<td>';
    echo '<textarea name="locker_full_warning" rows="3" cols="50" class="large-text">' . esc_textarea($full_warning) . '</textarea>';
    echo '<p class="description">Shown to all users about to fetch or drop-off things in the locker.</p>';
    echo '</td>';
    echo '</tr>';
    
    echo '</table>';
    
    echo '<p class="submit">';
    echo '<input type="submit" name="submit_locker_messages" class="button-primary" value="Save settings">';
    echo '</p>';
    
    echo '</form>';
    
    echo '</div>';
}