<?php
/**
 * Page with settings for LOOPIS admin.
 */
 
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Function to display content of settings page
function loopis_locker_messages() {
    // Handle form submission
    if (isset($_POST['submit_locker_messages']) && wp_verify_nonce($_POST['locker_messages_nonce'], 'looper_messages_action')) {
        // Update fetch warning
        if (isset($_POST['locker_fetch_warning'])) {
            loopis_update_setting('locker_fetch_warning', sanitize_textarea_field($_POST['locker_fetch_warning']));
        }
        
        // Update leave warning
        if (isset($_POST['locker_leave_warning'])) {
            loopis_update_setting('locker_leave_warning', sanitize_textarea_field($_POST['locker_leave_warning']));
        }
        
        echo '<div class="notice notice-success is-dismissible"><p>Inställningar sparade!</p></div>';
    }
    
    // Get current values
    $fetch_warning = loopis_get_setting('locker_fetch_warning', '');
    $leave_warning = loopis_get_setting('locker_leave_warning', '');
    
    // Page title and description
    echo '<div class="wrap"><h1>📡 Meddelanden</h1>';
    echo '<p>💡 Aktivering av varningar görs per skåp.</p>';
    
    echo '<form method="post" action="">';
    wp_nonce_field('looper_messages_action', 'locker_messages_nonce');
    
    echo '<table class="form-table">';
    
    // Fetch warning section
    echo '<tr>';
    echo '<th scope="row">🐎 Varning vid hämtning</th>';
    echo '<td>';
    echo '<textarea name="locker_fetch_warning" rows="3" cols="50" class="large-text">' . esc_textarea($fetch_warning) . '</textarea>';
    echo '<p class="description">Visas för användare som ska hämta när det är många saker i skåpen.</p>';
    echo '</td>';
    echo '</tr>';
    
    // Leave warning section
    echo '<tr>';
    echo '<th scope="row">🐌 Varning vid lämning</th>';
    echo '<td>';
    echo '<textarea name="locker_leave_warning" rows="3" cols="50" class="large-text">' . esc_textarea($leave_warning) . '</textarea>';
    echo '<p class="description">Visas för användare som ska lämna när det är många saker i skåpen.</p>';
    echo '</td>';
    echo '</tr>';
    
    echo '</table>';
    
    echo '<p class="submit">';
    echo '<input type="submit" name="submit_locker_messages" class="button-primary" value="Spara inställningar">';
    echo '</p>';
    
    echo '</form>';
    
    echo '</div>';
}