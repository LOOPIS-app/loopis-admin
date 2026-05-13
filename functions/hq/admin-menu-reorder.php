<?php
/**
 * Admin Menu Reordering
 * 
 * Moves standard WordPress menus below custom LOOPIS menus.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Reorder admin menu to push standard WP items down
 */
function loopis_admin_menu_reorder($new_position) {
    global $menu;
    
    // Slugs and order of standard WordPress menu items to move below custom LOOPIS menus.
    // Separator tokens can be inserted as 'separator'.
    $standard_menus = array(
        'separator',
        'users.php',               // Users
        'edit-comments.php',       // Comments
        'upload.php',              // Media
        'edit.php?post_type=page', // Pages
        'separator',
        'themes.php',              // Appearance
        'plugins.php',             // Plugins
        'tools.php',               // Tools
        'options-general.php',     // Settings
    );
    
    // Extract only real menu slugs (skip separator tokens)
    $standard_menu_slugs = array();
    foreach ($standard_menus as $menu_item) {
        if ($menu_item !== 'separator') {
            $standard_menu_slugs[] = $menu_item;
        }
    }

    // Store menus to move by slug
    $menus_to_move = array();
    
    // Find and extract standard menus
    foreach ($menu as $position => $item) {
        if (isset($item[2]) && in_array($item[2], $standard_menu_slugs, true)) {
            $menus_to_move[$item[2]] = $item;
            unset($menu[$position]);
        }
    }

    if (empty($menus_to_move)) {
        return;
    }

    // Ensure target start position is free
    while (isset($menu[$new_position])) {
        $new_position++;
    }
    
    // Re-add items in the exact order defined by $standard_menus
    foreach ($standard_menus as $menu_item) {
        if ($menu_item === 'separator') {
            $menu[$new_position] = array('', 'read', 'separator-custom-' . $new_position, '', 'wp-menu-separator');
            $new_position++;
            continue;
        }

        if (isset($menus_to_move[$menu_item])) {
            $menu[$new_position] = $menus_to_move[$menu_item];
            $new_position++;
        }
    }
    
    // Sort menu by position keys
    ksort($menu);
}