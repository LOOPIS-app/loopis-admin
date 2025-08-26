<?php
/**
 * Page with settings for LOOPIS admin.
 */
 
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Function to display content of settings page
function loopis_locker_general() {

    // Page title and description
    echo '<h1>⏹ Aktiva skåp</h1>';
    echo '<p>💡 Skåp i ditt område.</p>';

    global $wpdb;
    $table_name = $wpdb->prefix . 'loopis_lockers';
    $message = '';

    // Handle add locker
    if (isset($_POST['add_locker']) && !empty($_POST['postal_code'])) {
        $postal_code = sanitize_text_field($_POST['postal_code']);
        $code = isset($_POST['locker_code']) ? sanitize_text_field($_POST['locker_code']) : '';
        // Find the highest locker number for this postal code
        $max_num = $wpdb->get_var($wpdb->prepare(
            "SELECT MAX(CAST(SUBSTRING_INDEX(locker_id, '-', -1) AS UNSIGNED)) FROM $table_name WHERE locker_id LIKE %s",
            $postal_code . '-%'
        ));
        $next_num = $max_num ? $max_num + 1 : 1;
        $locker_id = $postal_code . '-' . $next_num;
        $wpdb->insert($table_name, [
            'locker_id' => $locker_id,
            'postal_code' => $postal_code,
            'code' => $code
        ]);
        $message = '<div class="updated"><p>Skåp tillagt: ' . esc_html($locker_id) . '</p></div>';
    }

    // Handle remove locker
    if (isset($_POST['remove_locker']) && !empty($_POST['locker_id'])) {
        $locker_id = sanitize_text_field($_POST['locker_id']);
        $wpdb->delete($table_name, ['locker_id' => $locker_id]);
        $message = '<div class="updated"><p>Skåp borttaget: ' . esc_html($locker_id) . '</p></div>';
    }

    // Fetch all lockers
    $lockers = $wpdb->get_results("SELECT * FROM $table_name ORDER BY postal_code, locker_id");

    if ($message) echo $message;
    echo '<h2>Skåp i ditt område</h2>';
    if ($lockers) {
        echo '<table class="widefat"><thead><tr><th>Skåp-ID</th><th>Namn</th><th>Postnummer</th><th>Kod</th><th>Varningar</th><th>Redigera</th><th>Ta bort</th></tr></thead><tbody>';
        foreach ($lockers as $locker) {
            echo '<tr><td>' . esc_html($locker->locker_id) . '</td>';
            echo '<td>' . esc_html($locker->locker_name ?: '—') . '</td>';
            echo '<td>' . esc_html($locker->postal_code) . '</td>';
            echo '<td>' . esc_html($locker->code ?: '—') . '</td>';
            
            // Combined warnings column
            $warnings = '';
            if ($locker->fetch_warning) $warnings .= '⚠ ';
            if ($locker->leave_warning) $warnings .= '⚠ ';
            echo '<td>' . trim($warnings) . '</td>';
            
            // Edit button
            $edit_url = admin_url('admin.php?page=loopis-locker-edit&locker_id=' . urlencode($locker->locker_id));
            echo '<td><a href="' . esc_url($edit_url) . '" class="button">⚙</a></td>';
            
            echo '<td><form method="post" style="display:inline;" onsubmit="return confirm(\'Vill du verkligen ta bort skåpet?\');"><input type="hidden" name="locker_id" value="' . esc_attr($locker->locker_id) . '"><button type="submit" name="remove_locker" class="button">🗑</button></form></td></tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<p>Inga skåp tillagda ännu.</p>';
    }
    // Form to add new locker
    echo '<h2>Lägg till nytt skåp</h2>';
    echo '<form method="post" style="margin-bottom:1em;">';
    echo '<input type="text" name="postal_code" placeholder="Postnummer" required pattern="[0-9]{5}" maxlength="5"> ';
    echo '<input type="text" name="locker_code" placeholder="Kod (valfritt)" maxlength="32"> ';
    echo '<button type="submit" name="add_locker" class="button button-primary">Lägg till skåp</button>';
    echo '</form>';
    echo '</div>';
}