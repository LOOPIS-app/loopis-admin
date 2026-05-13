<?php
/**
 * Function to insert a spacer of specified height in WP Admin Area
 *
 */
 
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function insert_spacer_admin($pixels) {
        echo '<div style="height:' . intval($pixels) . 'px" aria-hidden="true" class="wp-block-spacer"></div>';
    }