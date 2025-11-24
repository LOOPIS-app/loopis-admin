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
function loopis_reorder_admin_menu() {
    global $menu;
    
    // Define starting position for standard WP menus
    $new_position = 20;
    
    // Standard WordPress menu slugs to move
    $standard_menus = array(
        'upload.php',              // Media
        'edit.php?post_type=page', // Pages
        'edit-comments.php',       // Comments
        'themes.php',              // Appearance
        'plugins.php',             // Plugins
        'users.php',               // Users
        'tools.php',               // Tools
        'options-general.php',     // Settings (WP default)
    );
    
    // Store menus to move
    $menus_to_move = array();
    
    // Find and extract standard menus
    foreach ($menu as $position => $item) {
        if (isset($item[2]) && in_array($item[2], $standard_menus)) {
            $menus_to_move[$position] = $item;
            unset($menu[$position]);
        }
    }
    
    // Add separator before standard menus
    $menu[$new_position - 1] = array('', 'read', 'separator-standard', '', 'wp-menu-separator');
    
    // Re-add menus at new positions
    foreach ($menus_to_move as $item) {
        $menu[$new_position] = $item;
        $new_position++;
    }
    
    // Sort menu by position keys
    ksort($menu);
}