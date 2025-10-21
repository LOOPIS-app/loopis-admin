<?php
/**
 * Page with settings for LOOPIS admin.
 */
 
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Function to display content of page
function loopis_locker_edit() {
    // Page title and description
    echo '<h1>🛠 Redigera skåp</h1>';
    echo '<p>💡 Gör inställningar för aktiva skåp eller lägg till ett nytt.</p>';

    global $wpdb;
    $lockers_table = $wpdb->prefix . 'loopis_lockers';
    
    // Get selected locker
    $selected_locker_id = isset($_GET['locker_id']) ? sanitize_text_field($_GET['locker_id']) : '';
    
    // Handle form submission for locker updates
    if (isset($_POST['locker_nonce']) && wp_verify_nonce($_POST['locker_nonce'], 'save_locker')) {
        if (!empty($_POST['locker_id'])) {
            $locker_id = sanitize_text_field($_POST['locker_id']);
            $update_data = array();
            
            // Process all form fields except the nonce and locker_id
            foreach ($_POST as $key => $value) {
                if ($key !== 'locker_nonce' && $key !== 'locker_id' && $key !== 'submit' && $key !== '_wp_http_referer' && $key !== '_wpnonce') {
                    // Handle checkbox values (which can be arrays due to hidden field)
                    if (is_array($value)) {
                        // For checkboxes: if array contains '1', checkbox was checked, otherwise use '0'
                        $update_data[sanitize_key($key)] = in_array('1', $value) ? '1' : '0';
                    } else {
                        $update_data[sanitize_key($key)] = sanitize_text_field($value);
                    }
                }
            }
            
            if (!empty($update_data)) {
                $updated = $wpdb->update($lockers_table, $update_data, ['locker_id' => $locker_id]);
                if ($updated !== false) {
                    echo '<div class="updated"><p>Ändringar har sparats för skåp ' . esc_html($locker_id) . '.</p></div>';
                } else {
                    echo '<div class="error"><p>Ett fel uppstod vid sparandet.</p></div>';
                }
            }
        }
    }
    
    // Fetch all lockers for dropdown
    $lockers = $wpdb->get_results("SELECT locker_id, locker_name FROM $lockers_table ORDER BY locker_id, locker_name");

    if (empty($lockers)) {
        echo '<p><em>Inga skåp tillagda ännu.</em></p></div>';
        return;
    }
    
    // Locker selection dropdown
    echo '<form method="get">';
    echo '<input type="hidden" name="page" value="' . esc_attr($_GET['page']) . '" />';
    echo '<h2>Välj ett skåp att redigera</h2>';
    echo '<select name="locker_id" onchange="this.form.submit();">';
    echo '<option value="">-- Välj skåp --</option>';
    foreach ($lockers as $locker) {
        $selected = ($selected_locker_id === $locker->locker_id) ? 'selected' : '';
        echo '<option value="' . esc_attr($locker->locker_id) . '" ' . $selected . '>';
        echo esc_html($locker->locker_id) . ': ' . esc_html($locker->locker_name);
        echo '</option>';
    }
    echo '</select>';
    echo '</form>';
    
    // If a locker is selected, show the edit form
    if (!empty($selected_locker_id)) {
        $selected_locker = $wpdb->get_row($wpdb->prepare("SELECT * FROM $lockers_table WHERE locker_id = %s", $selected_locker_id));
        
        if ($selected_locker) {
            echo '<h2>Redigera skåp: ' . esc_html($selected_locker_id) . '</h2>';
            echo '<form method="post">';
            wp_nonce_field('save_locker', 'locker_nonce');
            echo '<input type="hidden" name="locker_id" value="' . esc_attr($selected_locker_id) . '" />';
            echo '<table class="form-table">';
            
            // Get all columns from the table
            $columns = $wpdb->get_results("SHOW COLUMNS FROM $lockers_table");
            
            foreach ($columns as $column) {
                $column_name = $column->Field;
                $column_value = $selected_locker->$column_name;
                
                // Skip the auto-increment id column
                if ($column_name === 'id') {
                    continue;
                }
                
                // Handle locker_id separately (readonly)
                if ($column_name === 'locker_id') {
                    echo '<tr><th scope="row"><label>Skåp-ID</label></th>';
                    echo '<td><strong>' . esc_html($column_value) . '</strong> <em>(kan inte ändras)</em></td></tr>';
                    continue;
                }
                
                // Create appropriate input field based on column type
                $field_label = ucfirst(str_replace('_', ' ', $column_name));
                echo '<tr><th scope="row"><label for="' . esc_attr($column_name) . '">' . esc_html($field_label) . '</label></th>';
                echo '<td>';
                
                // Handle different field types
                if (strpos($column->Type, 'tinyint(1)') !== false) {
                    // Boolean fields (checkboxes)
                    $checked = $column_value ? 'checked' : '';
                    echo '<input type="hidden" name="' . esc_attr($column_name) . '" value="0" />';
                    echo '<input type="checkbox" name="' . esc_attr($column_name) . '" id="' . esc_attr($column_name) . '" value="1" ' . $checked . ' />';
                } else {
                    // Text fields
                    echo '<input name="' . esc_attr($column_name) . '" type="text" id="' . esc_attr($column_name) . '" value="' . esc_attr($column_value) . '" class="regular-text" />';
                }
                
                echo '</td></tr>';
            }
            
            echo '</table>';
            echo '<p class="submit"><input type="submit" class="button-primary" value="Spara ändringar"></p>';
            echo '</form>';
        } else {
            echo '<p><em>Skåpet kunde inte hittas.</em></p>';
        }
    }
    
    echo '</div>';
}