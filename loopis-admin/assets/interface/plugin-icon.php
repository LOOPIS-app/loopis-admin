<?php
/**
 * Filter used by the LOOPIS plugin.
 */
 
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Add the plugins_api filter to modify plugin metadata (plugin icon)
add_filter('plugins_api', 'loopis_add_plugin_icon', 10, 3);

function loopis_add_plugin_icon($res, $action, $args) {
    // Check if the plugin slug matches your plugin
    if (isset($args->slug) && $args->slug === 'loopis-plugin') {
        $res->icons = array(
            'default' => plugin_dir_url(__FILE__) . 'assets/img/loopis-icon/icon-256x256.png',
        );
    }
    return $res;
}