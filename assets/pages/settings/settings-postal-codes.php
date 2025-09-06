<?php
/**
 * Page with settings for LOOPIS admin.
 */
 
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Function to display content of settings page
function loopis_settings_postal_codes() {
    // Page title and description
    echo '<h1>⚙ Postnummer</h1>';
    echo '<p>💡 Lista över aktiva postnummer i ditt område</p>';
}