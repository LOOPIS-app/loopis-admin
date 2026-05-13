<?php
/**
 * Page for adding lockers in LOOPIS admin.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function loopis_locker_add() {
    // Page title and description
    echo '<h1>➕ Add locker</h1>';
    echo '<p>💡 Create a new locker for your area.</p>';

    global $wpdb;
    $table_name = $wpdb->prefix . 'loopis_lockers';

    // Handle add locker
    if (isset($_POST['add_locker']) && isset($_POST['add_locker_nonce']) && wp_verify_nonce($_POST['add_locker_nonce'], 'add_locker')) {
        $insert_data = array();

        foreach ($_POST as $key => $value) {
            if ($key !== 'add_locker' && $key !== 'add_locker_nonce' && $key !== '_wp_http_referer' && $key !== '_wpnonce') {
                if (is_array($value)) {
                    $insert_data[sanitize_key($key)] = in_array('1', $value) ? '1' : '0';
                } else {
                    $insert_data[sanitize_key($key)] = sanitize_text_field(wp_unslash($value));
                }
            }
        }

        if (!empty($insert_data['locker_id']) && !empty($insert_data['postal_code'])) {
            $wpdb->insert($table_name, $insert_data);
            wp_safe_redirect(admin_url('admin.php?page=loopis-locker-overview&added=' . urlencode($insert_data['locker_id'])));
            exit;
        }
    }

    // Add locker form
    echo '<form method="post" style="margin-bottom:1em;">';
    wp_nonce_field('add_locker', 'add_locker_nonce');
    echo '<table class="form-table">';

    $columns = $wpdb->get_results("SHOW COLUMNS FROM $table_name");
    $columns_by_name = array();
    foreach ($columns as $column) {
        $columns_by_name[$column->Field] = $column;
    }

    $preferred_order = array('locker_id', 'locker_name', 'postal_code', 'locker_code');
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

        if ($column_name === 'id' || $column_name === 'locker_full') {
            continue;
        }

        $field_label = ucfirst(str_replace('_', ' ', $column_name));
        echo '<tr><th scope="row"><label for="' . esc_attr($column_name) . '">' . esc_html($field_label) . '</label></th>';
        echo '<td>';

        if (strpos($column->Type, 'tinyint(1)') !== false) {
            echo '<input type="hidden" name="' . esc_attr($column_name) . '" value="0" />';
            echo '<input type="checkbox" name="' . esc_attr($column_name) . '" id="' . esc_attr($column_name) . '" value="1" />';
        } else {
            $required = ($column_name === 'locker_id' || $column_name === 'postal_code') ? ' required' : '';
            $placeholder = $field_label;
            $pattern = $column_name === 'postal_code' ? ' pattern="[0-9]{5}" maxlength="5"' : '';
            echo '<input name="' . esc_attr($column_name) . '" type="text" id="' . esc_attr($column_name) . '" class="regular-text" placeholder="' . esc_attr($placeholder) . '"' . $required . $pattern . ' />';
        }

        echo '</td></tr>';
    }

    echo '</table>';
    echo '<p class="submit">';
    echo '<button type="submit" name="add_locker" class="button button-primary">Add locker</button> ';
    echo '<a class="button" href="' . esc_url(admin_url('admin.php?page=loopis-locker-overview')) . '">Cancel</a>';
    echo '</p>';
    echo '</form>';
    echo '</div>';
}
