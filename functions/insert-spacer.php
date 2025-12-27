<?php
/**
 * Function to insert a spacer of specified height, always available 
 *
 * Included for everyone in functions.php
 */
 
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function insert_spacer($pixels) {
    echo '<div style="height:' . intval($pixels) . 'px" aria-hidden="true" class="wp-block-spacer"></div>';
}