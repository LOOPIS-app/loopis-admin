<?php
/**
 * Function to insert a spacer of specified height, always available 
 *
 * Included in theme but also here because we haven't decided on how to handle functions common for front & back.
 */
 
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!function_exists('insert_spacer')) {
    function insert_spacer($pixels) {
        echo '<div style="height:' . intval($pixels) . 'px" aria-hidden="true" class="wp-block-spacer"></div>';
    }
}