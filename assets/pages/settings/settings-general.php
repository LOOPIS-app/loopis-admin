<?php
/**
 * Page with settings for LOOPIS admin.
 */
 
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Function to display content of settings page
function loopis_settings_general() {
    // Page title and description
    echo '<h1>⚙ Allmänna inställningar</h1>';
    echo '<p>💡 Work in progress...</p>';

    // Tool: Two-step delete process
    echo '<hr><h2>Delete posts</h2>';

    // List users with specified roles as clickable buttons
    $roles = array('board_member', 'manager', 'storage_submitter');
    $users = get_users(array('role__in' => $roles));
    if (!empty($users)) {
        echo '<div style="margin-bottom:10px;"><strong>Click users to exclude:</strong><br>';
        foreach ($users as $user) {
            $uid = esc_attr($user->ID);
            $uname = esc_html($user->display_name);
            echo '<button type="button" class="add-user-id" data-id="' . $uid . '" style="margin:2px 4px 2px 0;">' . $uname . ' (' . $uid . ')</button>';
        }
        echo '</div>';
        echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            var buttons = document.querySelectorAll(".add-user-id");
            var input = document.getElementById("user_ids");
            buttons.forEach(function(btn) {
                btn.addEventListener("click", function() {
                    var id = btn.getAttribute("data-id");
                    var current = input.value.trim();
                    var ids = current ? current.split(",") : [];
                    ids = ids.map(function(i){return i.trim();});
                    if (ids.indexOf(id) === -1) {
                        if (current.length > 0) {
                            input.value = current + "," + id;
                        } else {
                            input.value = id;
                        }
                    }
                    input.focus();
                });
            });
        });
        </script>';
    }

    echo '<form method="post">';
    echo '<label for="user_ids">Excluded users IDs (comma-separated):</label> ';
    $entered_ids = isset($_POST['user_ids']) ? htmlspecialchars($_POST['user_ids']) : '';
    echo '<input type="text" name="user_ids" id="user_ids" style="width:300px;" value="' . $entered_ids . '"> ';
    if (!isset($_POST['count_other_posts']) && !isset($_POST['delete_other_posts'])) {
        echo '<button type="submit" name="count_other_posts">Count posts</button>';
    }
    echo '</form>';

    if (isset($_POST['count_other_posts']) && !empty($_POST['user_ids'])) {
        $ids = array_map('trim', explode(',', $_POST['user_ids']));
        $ids = array_filter($ids, 'is_numeric');
        if (!empty($ids)) {
            global $wpdb;
            // List user names for entered IDs, with post count
            echo '<h3>Excluded users:</h3><ul>';
            $total_kept = 0;
            foreach ($ids as $uid) {
                $user = get_userdata($uid);
                $uname = $user ? esc_html($user->display_name) : '(unknown)';
                $post_count = $user ? count_user_posts($uid, 'post', true) : 0;
                $total_kept += $post_count;
                echo '<li>' . $uid . ': ' . $uname . ' <span style="color:green">(' . $post_count . ' posts)</span></li>';
            }
            echo '</ul>';
            echo '<p style="color:green;">Number of posts that will be kept: <strong>' . $total_kept . '</strong></p>';
            // Count posts to be deleted
            $placeholders = implode(',', array_fill(0, count($ids), '%d'));
            $query = $wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_author NOT IN ($placeholders) AND post_type = 'post'", ...$ids);
            $posts = $wpdb->get_col($query);
            $count = count($posts);
            echo '<p style="color:red;">Number of posts that will be deleted: <strong>' . $count . '</strong></p>';
            if ($count > 0) {
                // Show delete button in a new form
                echo '<form method="post">';
                echo '<input type="hidden" name="user_ids" value="' . htmlspecialchars($_POST['user_ids']) . '">';
                echo '<button type="submit" name="delete_other_posts" onclick="return confirm(\'Are you sure? This will delete ALL posts not by these users!\')">Delete posts</button>';
                echo '</form>';
            }
        } else {
            echo '<p style="color:red;">No valid user IDs entered.</p>';
        }
    }

    if (isset($_POST['delete_other_posts']) && !empty($_POST['user_ids'])) {
        $ids = array_map('trim', explode(',', $_POST['user_ids']));
        $ids = array_filter($ids, 'is_numeric');
        if (!empty($ids)) {
            global $wpdb;
            $placeholders = implode(',', array_fill(0, count($ids), '%d'));
            $query = $wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_author NOT IN ($placeholders) AND post_type = 'post'", ...$ids);
            $posts = $wpdb->get_col($query);
            $batch_size = 500;
            $offset = isset($_POST['batch_offset']) ? intval($_POST['batch_offset']) : 0;
            $total = count($posts);
            $batch = array_slice($posts, $offset, $batch_size);
            $batch_deleted = 0;
            foreach ($batch as $post_id) {
                // Delete attached images
                $attachments = get_attached_media('', $post_id);
                foreach ($attachments as $attachment) {
                    wp_delete_attachment($attachment->ID, true);
                }
                // Delete post meta
                $wpdb->delete($wpdb->postmeta, array('post_id' => $post_id));
                // Delete post
                wp_delete_post($post_id, true);
                $batch_deleted++;
            }
            $next_offset = $offset + $batch_deleted;
            echo '<p style="color:red;">Batch deleted: ' . $batch_deleted . ' posts.</p>';
            if ($next_offset < $total) {
                echo '<form method="post" id="continue-delete-form">';
                echo '<input type="hidden" name="user_ids" value="' . htmlspecialchars($_POST['user_ids']) . '">';
                echo '<input type="hidden" name="delete_other_posts" value="1">';
                echo '<input type="hidden" name="batch_offset" value="' . $next_offset . '">';
                echo '<button type="submit" id="continue-delete-btn">Continue</button>';
                echo '</form>';
                echo '<p>' . ($total - $next_offset) . ' posts remaining.</p>';
            } else {
                echo '<p style="color:green;">All posts not by user IDs: ' . implode(', ', $ids) . ' have been deleted.</p>';
            }
        } else {
            echo '<p style="color:red;">No valid user IDs entered.</p>';
        }
    }
}