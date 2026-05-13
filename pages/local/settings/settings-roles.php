<?php
/**
 * Page with settings for LOOPIS admin.
 */
 
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Function to display content of settings page
function loopis_settings_roles() {
    // Page title and description
    echo '<h1>👥 Roles</h1>';
    echo '<p>💡 List of current roles set for users in your area</p>';

    // List roles
    global $wp_roles;
    if (!isset($wp_roles)) {
        $wp_roles = new WP_Roles();
    }
    $custom_roles = array();
    foreach ($wp_roles->roles as $role_key => $role) {
        // Only include custom roles (not built-in WP roles) and exclude those starting with 'member'
        if (
            !in_array($role_key, array('administrator', 'editor', 'author', 'contributor', 'subscriber')) &&
            strpos($role_key, 'member') !== 0
        ) {
            $custom_roles[$role_key] = $role['name'];
        }
    }
    if (empty($custom_roles)) {
        echo '<p><em>No custom roles found.</em></p>';
    } else {
        foreach ($custom_roles as $role_key => $role_name) {
            $users = get_users(array('role' => $role_key));
            $user_count = count($users);
            echo '<h2 style="margin-top:2em;">' . esc_html($role_name) . ' (' . $user_count . ')</h2>';
            // User list
            echo '<p>';
            if (!empty($users)) {
                $user_links = array();
                foreach ($users as $user) {
                    $edit_url = admin_url('user-edit.php?user_id=' . $user->ID);
                    $user_links[] = '<a href="' . esc_url($edit_url) . '" target="_blank">' . esc_html($user->display_name) . '</a>';
                }
                echo implode(', ', $user_links);
            } else {
                echo '<em>No users with this role.</em>';
            }
            echo '</p>';
            // List capabilities (custom only, single line)
            if (isset($wp_roles->roles[$role_key]['capabilities'])) {
                $caps = array();
                foreach ($wp_roles->roles[$role_key]['capabilities'] as $cap => $granted) {
                    if ($granted && !in_array($cap, array(
                        'read','edit_posts','delete_posts','publish_posts','edit_others_posts','delete_others_posts','edit_published_posts','delete_published_posts','edit_private_posts','delete_private_posts','edit_pages','edit_others_pages','publish_pages','delete_pages','delete_others_pages','edit_published_pages','delete_published_pages','edit_private_pages','delete_private_pages','manage_categories','moderate_comments','upload_files','list_users','edit_users','create_users','delete_users','promote_users','remove_users','add_users','edit_theme_options','manage_options','edit_dashboard','customize','edit_files','unfiltered_html','edit_plugins','install_plugins','update_plugins','delete_plugins','activate_plugins','edit_themes','install_themes','update_themes','delete_themes','switch_themes','update_core','edit_css','edit_comment','read_private_pages','read_private_posts','delete_site','manage_links','unfiltered_upload','edit_others_comments','delete_others_comments','edit_published_comments','delete_published_comments','edit_private_comments','delete_private_comments','read_private_comments','level_0','level_1','level_2','level_3','level_4','level_5','level_6','level_7','level_8','level_9','level_10'
                    ))) {
                        $caps[] = esc_html($cap);
                    }
                }
                if (!empty($caps)) {
                    echo '<p><strong>Access:</strong><br><code>' . implode('</code>, <code>', $caps) . '</code></p>';
                } else {
                    echo '<p><em>No custom capabilities assigned.</em></p>';
                }
            }
        }
    }
    echo '</div>';
}