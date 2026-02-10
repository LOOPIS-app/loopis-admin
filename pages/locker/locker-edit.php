<?php
/**
 * Page with settings for LOOPIS admin.
 */
 
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Function to display content of page
function loopis_locker_edit() {
    global $wpdb;
    $lockers_table = $wpdb->prefix . 'loopis_lockers';
    
    // Get selected locker
    $selected_locker_id = isset($_GET['locker_id']) ? sanitize_text_field($_GET['locker_id']) : '';
    
    // Handle form submission for locker updates
    if (isset($_POST['locker_nonce']) && wp_verify_nonce($_POST['locker_nonce'], 'save_locker')) {
        if (!empty($_POST['locker_id']) && !empty($_POST['locker_id_original'])) {
            $locker_id = sanitize_text_field($_POST['locker_id']);
            $locker_id_original = sanitize_text_field($_POST['locker_id_original']);
            $update_data = array();
            
            // Process all form fields except the nonce and locker_id
            foreach ($_POST as $key => $value) {
                if ($key !== 'locker_nonce' && $key !== 'locker_id_original' && $key !== 'submit' && $key !== '_wp_http_referer' && $key !== '_wpnonce') {
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
                $updated = $wpdb->update($lockers_table, $update_data, ['locker_id' => $locker_id_original]);
                if ($updated !== false) {
                    $redirect_url = admin_url('admin.php?page=loopis-locker-overview&updated=' . urlencode($locker_id));
                    if (headers_sent()) {
                        $redirect_js = wp_json_encode($redirect_url);
                        echo '<script>window.location.href=' . $redirect_js . ';</script>';
                        echo '<noscript><meta http-equiv="refresh" content="0;url=' . esc_url($redirect_url) . '"></noscript>';
                        exit;
                    }
                    wp_safe_redirect($redirect_url);
                    exit;
                }
                echo '<div class="error"><p>An error occurred while saving.</p></div>';
            } else {
                $redirect_url = admin_url('admin.php?page=loopis-locker-overview&updated=' . urlencode($locker_id_original));
                if (headers_sent()) {
                    $redirect_js = wp_json_encode($redirect_url);
                    echo '<script>window.location.href=' . $redirect_js . ';</script>';
                    echo '<noscript><meta http-equiv="refresh" content="0;url=' . esc_url($redirect_url) . '"></noscript>';
                    exit;
                }
                wp_safe_redirect($redirect_url);
                exit;
            }
        }
    }
    
    if (empty($selected_locker_id)) {
        wp_safe_redirect(admin_url('admin.php?page=loopis-locker-overview'));
        exit;
    }

    if (isset($_POST['delete_locker']) && wp_verify_nonce($_POST['delete_locker_nonce'], 'delete_locker')) {
        $locker_id = sanitize_text_field($_POST['locker_id']);
        $wpdb->delete($lockers_table, ['locker_id' => $locker_id]);
        wp_safe_redirect(admin_url('admin.php?page=loopis-locker-overview&deleted=' . urlencode($locker_id)));
        exit;
    }

    // Page title and description
    echo '<h1>🛠 Edit locker</h1>';
    echo '<p>💡 Adjust settings for a locker in your area.</p>';

    $selected_locker = $wpdb->get_row($wpdb->prepare("SELECT * FROM $lockers_table WHERE locker_id = %s", $selected_locker_id));

    if ($selected_locker) {
        echo '<h2>Edit locker: ' . esc_html($selected_locker_id) . '</h2>';
        echo '<form method="post">';
        wp_nonce_field('save_locker', 'locker_nonce');
        echo '<input type="hidden" name="locker_id" value="' . esc_attr($selected_locker_id) . '" />';
        echo '<input type="hidden" name="locker_id_original" value="' . esc_attr($selected_locker_id) . '" />';
        echo '<table class="form-table">';

        // Get all columns from the table
        $columns = $wpdb->get_results("SHOW COLUMNS FROM $lockers_table");
        $columns_by_name = array();
        foreach ($columns as $column) {
            $columns_by_name[$column->Field] = $column;
        }

        $preferred_order = array('locker_id', 'locker_name', 'postal_code', 'locker_code', 'locker_full');
        $ordered_columns = array();

        foreach ($preferred_order as $column_name) {
            if (isset($columns_by_name[$column_name])) {
                $ordered_columns[] = $columns_by_name[$column_name];
                unset($columns_by_name[$column_name]);
            }
        }

        foreach ($columns_by_name as $column) {
            $ordered_columns[] = $column;
        }

        foreach ($ordered_columns as $column) {
            $column_name = $column->Field;
            $column_value = $selected_locker->$column_name;

            // Skip the auto-increment id column
            if ($column_name === 'id') {
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
        echo '<p class="submit">';
        echo '<input type="submit" class="button-primary" value="Save changes"> ';
        echo '<a class="button" href="' . esc_url(admin_url('admin.php?page=loopis-locker-overview')) . '">Cancel</a>';
        echo '</p>';
        echo '</form>';
        echo '<form method="post" onsubmit="return confirm(\'Are you sure you want to delete this locker?\');">';
        wp_nonce_field('delete_locker', 'delete_locker_nonce');
        echo '<input type="hidden" name="locker_id" value="' . esc_attr($selected_locker_id) . '" />';
        echo '<p><button type="submit" name="delete_locker" class="button button-link-delete">🗑 Delete locker</button></p>';
        echo '</form>';
    } else {
        echo '<p><em>Locker not found.</em></p>';
    }
    
    echo '</div>';
}