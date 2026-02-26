<?php
/**
 * Configure menu items for regular posts
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Configure menu item for regular post type
 */
function loopis_admin_menu_gifts($position) {
    global $menu;

    // Change the name of the menu item
    $menu[5][0] = 'Gifts';

    // Add category shortcuts under Gifts
    loopis_admin_menu_categories('Gifts', 'edit.php', 'category');

    // Return next position for further menu items
    $position++;
    return $position;
}
    
/**
 * Add all categories as submenus
 */
function loopis_admin_menu_categories($parent_title, $parent_slug, $taxonomy) {
    if (!taxonomy_exists($taxonomy)) {
        return;
    }

    $categories = get_categories(array(
        'taxonomy' => $taxonomy,
        'hide_empty' => false,
        'orderby' => 'ID',
        'order' => 'ASC'
    ));

    foreach ($categories as $category) {
        $url = 'edit.php?post_status=all&post_type=post&cat=' . $category->term_id;

        add_submenu_page(
            $parent_slug,
            $parent_title . ': ' . $category->name,
            $category->name,
            'manage_options',
            $url
        );
    }
}