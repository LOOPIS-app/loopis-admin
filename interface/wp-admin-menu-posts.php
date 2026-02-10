<?php
/**
 * Post Type Menu Configuration
 * 
 * Handles setup of Posts, Forum, and Support menus.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Setup all post type menus
 */
function loopis_setup_post_menus() {
    global $menu;

    // Change the name of the Posts menu
    $menu[5][0] = 'Gifts';

    // Add categories as sub-items under "Posts"
    loopis_add_categories_submenu('Gifts', 'edit.php', 'category');

    // Add custom post type menus
    loopis_add_custom_post_type_menu('forum', 'Forum', LOOPIS_ADMIN_URL . '/assets/img/wp-admin-menu/icon-forum.png', 'forum-category', 8);
    loopis_add_custom_post_type_menu('support', 'Support', LOOPIS_ADMIN_URL . '/assets/img/wp-admin-menu/icon-support.png', 'support-status', 9);
    
    // Add separator after custom post types (must be integer)
    $menu[10] = array('', 'read', 'separator-custom-posts', '', 'wp-menu-separator');
}

/**
 * Add categories as submenu items under a parent menu
 */
function loopis_add_categories_submenu($parent_title, $parent_slug, $taxonomy) {
    $categories = get_categories(array(
        'hide_empty' => false,
        'orderby' => 'ID',
        'order' => 'ASC'
    ));

    foreach ($categories as $category) {
        if ($category->parent != 0) {
            $url = 'edit.php?post_status=all&post_type=post&cat=' . $category->term_id;

            add_submenu_page(
                $parent_slug,
                $category->name,
                $category->name,
                'manage_categories',
                $url
            );
        }
    }
}

/**
 * Add custom post type menu with taxonomy submenu items
 */
function loopis_add_custom_post_type_menu($post_type, $menu_title, $icon_url, $taxonomy, $position) {
    // Add the top-level menu for the custom post type
    add_menu_page(
        $menu_title,
        $menu_title,
        'manage_options',
        'edit.php?post_type=' . $post_type,
        '',
        $icon_url,
        $position
    );

    // Add "All Posts" submenu item
    add_submenu_page(
        'edit.php?post_type=' . $post_type,
        'All Posts ' . $menu_title,
        'All Posts',
        'manage_options',
        'edit.php?post_type=' . $post_type
    );

    // Add "Add New" submenu item
    add_submenu_page(
        'edit.php?post_type=' . $post_type,
        'Add New ' . $menu_title,
        'Add New',
        'manage_options',
        'post-new.php?post_type=' . $post_type
    );

    // Add "Categories" submenu item for taxonomy management
    add_submenu_page(
        'edit.php?post_type=' . $post_type,
        'Manage Categories',
        'Categories',
        'manage_options',
        'edit-tags.php?taxonomy=' . $taxonomy . '&post_type=' . $post_type
    );

    // Add taxonomy terms as submenu items
    $terms = get_terms(array(
        'taxonomy' => $taxonomy,
        'hide_empty' => false,
        'fields' => 'all',
    ));

    if (!is_wp_error($terms) && !empty($terms)) {
        foreach ($terms as $term) {
            $url = 'edit.php?post_type=' . $post_type . '&' . $taxonomy . '=' . $term->slug;

            add_submenu_page(
                'edit.php?post_type=' . $post_type,
                $term->name,
                $term->name,
                'manage_options',
                $url
            );
        }
    }
}