<?php
/**
 * Page with settings for LOOPIS admin.
 */
 
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Function to display content of settings page
function loopis_settings_event() {
    // Page title and description
    echo '<h1>⚙ Event</h1>';
    echo '<p>💡 Inställningar för event.</p>';

    // Handle form submission
    $event_name = loopis_get_setting('event_name', '');
    $history_serialized = loopis_get_setting('event_name_history', '');
    $history = !empty($history_serialized) ? unserialize($history_serialized) : array();
    if (isset($_POST['event_name_nonce']) && wp_verify_nonce($_POST['event_name_nonce'], 'save_event_name')) {
        if (isset($_POST['event_name'])) {
            $new_event_name = sanitize_text_field($_POST['event_name']);
            loopis_update_setting('event_name', $new_event_name);
            // Store previous unique values
            if (!in_array($new_event_name, $history) && !empty($new_event_name)) {
                $history[] = $new_event_name;
                loopis_update_setting('event_name_history', serialize($history));
            }
            echo '<div class="updated"><p>Ändringar har sparats.</p></div>';
        }
    }
    $event_name = loopis_get_setting('event_name', '');
    $history_serialized = loopis_get_setting('event_name_history', '');
    $history = !empty($history_serialized) ? unserialize($history_serialized) : array();
    echo '<form method="post">';
    wp_nonce_field('save_event_name', 'event_name_nonce');
    echo '<table class="form-table"><tr><th scope="row"><label for="event_name">Eventnamn</label></th>';
    echo '<td style="vertical-align:top;">';
    echo '<input name="event_name" type="text" id="event_name" value="' . esc_attr($event_name) . '" class="regular-text" />';
    // Show previous values as buttons, aligned with textbox
    if (!empty($history)) {
        echo '<div style="margin-top:8px;">';
        foreach (array_reverse($history) as $prev_name) {
            $button_id = 'event_btn_' . md5($prev_name);
            echo '<span style="display:inline-block; position:relative; margin:2px 4px 2px 0;">';
            echo '<button type="button" class="button" style="margin:0;" onclick="document.getElementById(\'event_name\').value=\'' . esc_js($prev_name) . '\';">' . esc_html($prev_name) . '</button>';
            echo '</span>';
        }
        echo '</div>';
    }
    echo '</td></tr></table>';
    echo '<p class="submit"><input type="submit" class="button-primary" value="Spara ändringar"></p>';
    echo '</form>';

    // List users with storage_submitter role
    echo '<h2 style="margin-top:2em;">Användare med rollen <code>storage_submitter</code></h2>';
    $submitters = get_users(array('role' => 'storage_submitter'));
    if (!empty($submitters)) {
        echo '<ul>';
        foreach ($submitters as $user) {
            $edit_url = admin_url('user-edit.php?user_id=' . $user->ID);
            echo '<li><a href="' . esc_url($edit_url) . '" target="_blank">' . esc_html($user->display_name) . '</a></li>';
        }
        echo '</ul>';
    } else {
        echo '<p><em>Inga användare med denna roll.</em></p>';
    }

    // List users with storage_booker role
    echo '<h2 style="margin-top:2em;">Användare med rollen <code>storage_booker</code></h2>';
    $bookers = get_users(array('role' => 'storage_booker'));
    if (!empty($bookers)) {
        echo '<ul>';
        foreach ($bookers as $user) {
            $edit_url = admin_url('user-edit.php?user_id=' . $user->ID);
            echo '<li><a href="' . esc_url($edit_url) . '" target="_blank">' . esc_html($user->display_name) . '</a></li>';
        }
        echo '</ul>';
    } else {
        echo '<p><em>Inga användare med denna roll.</em></p>';
    }
    echo '</div>';
}
