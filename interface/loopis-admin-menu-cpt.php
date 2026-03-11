<?php
/**
 * Configure WP Admin menu items for custom post types.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Configure WP Admin menu items for custom post types
 */
function loopis_admin_menu_cpt($position) {
    global $menu;

    // Register new custom post types + taxonomies here
    // Array structure per CPT:
    // [
    //   0: post type slug,
    //   1: menu title,
    //   2: icon URL,
    //   3: hierarchical taxonomy (categories) slug or '' if none,
    //   4: tag taxonomies (string or array of slugs) to show as "Tags" submenu
    // ]
    $cpt_menus = array(
        array('forum', 'Forum', LOOPIS_ADMIN_URL . '/assets/img/wp-admin-menu/icon-forum.png', 'forum-category'),
        array('support', 'Support', LOOPIS_ADMIN_URL . '/assets/img/wp-admin-menu/icon-support.png', 'support-status'),
        array('faq', 'FAQ', LOOPIS_ADMIN_URL . '/assets/img/wp-admin-menu/icon-faq.png', '', 'faq-tag'),
    );

    $added_cpt_menus = 0;

    foreach ($cpt_menus as $cpt_menu) {
        list($post_type, $menu_title, $icon_url, $taxonomy) = $cpt_menu;
        $tag_taxonomies = isset($cpt_menu[4]) ? (array) $cpt_menu[4] : array();

        if (!post_type_exists($post_type)) {
            continue;
        }

        while (isset($menu[$position])) {
            $position++;
        }

        loopis_admin_menu_cpt_add($post_type, $menu_title, $icon_url, $taxonomy, $position, $tag_taxonomies);
        $added_cpt_menus++;
        $position++;
    }

    // Add separator only when at least one CPT menu was added
    if ($added_cpt_menus > 0) {
        while (isset($menu[$position])) {
            $position++;
        }

        $menu[$position] = array('', 'read', 'separator-custom-posts', '', 'wp-menu-separator');
    }
    
    // Return next position for further menu items
    return $position;
}

/**
 * Add menu item with submenus
 */
function loopis_admin_menu_cpt_add($post_type, $menu_title, $icon_url, $taxonomy, $position, $tag_taxonomies = array()) {
    if (!post_type_exists($post_type)) {
        return;
    }

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

    // Add the "All Posts" submenu item
    add_submenu_page(
        'edit.php?post_type=' . $post_type,
        'All Posts ' . $menu_title,
        'All Posts',
        'manage_options',
        'edit.php?post_type=' . $post_type
    );

    // Add the "Add Post" submenu item
    add_submenu_page(
        'edit.php?post_type=' . $post_type,
        'Add Post ' . $menu_title,
        'Add Post',
        'manage_options',
        'post-new.php?post_type=' . $post_type
    );

    // Add tag "Tags" submenu item (if provided) for taxonomy management
    if (!empty($tag_taxonomies)) {
        $tag_taxonomy_objects = array_map('get_taxonomy', (array) $tag_taxonomies);

        $tag_taxonomy_objects = array_filter(
            $tag_taxonomy_objects,
            function ($taxonomy_object) {
                return $taxonomy_object && !$taxonomy_object->hierarchical && !empty($taxonomy_object->show_ui);
            }
        );

        foreach ($tag_taxonomy_objects as $tag_taxonomy) {
            add_submenu_page(
                'edit.php?post_type=' . $post_type,
                !empty($tag_taxonomy->labels->menu_name) ? $tag_taxonomy->labels->menu_name : $tag_taxonomy->labels->name,
                'Tags',
                'manage_options',
                'edit-tags.php?taxonomy=' . $tag_taxonomy->name . '&post_type=' . $post_type
            );
        }
    }

    if (!taxonomy_exists($taxonomy)) {
        return;
    }

    // Add the "Categories" submenu item for taxonomy management
    add_submenu_page(
        'edit.php?post_type=' . $post_type,
        'Manage Categories',
        'Categories',
        'manage_options',
        'edit-tags.php?taxonomy=' . $taxonomy . '&post_type=' . $post_type
    );

    // Add all categories as submenu items
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