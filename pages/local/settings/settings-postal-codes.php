<?php
/**
 * Page with settings for LOOPIS admin.
 */
 
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Function to render the page
function loopis_settings_postal_codes() {
    // Page title and description
    echo '<h1>⚙ Postal codes</h1>';
    echo '<p>💡 List of active postal codes in your area</p>';
}