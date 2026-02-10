<?php
/**
 * Helpers for storing textarea line breaks as <br> in settings.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function loopis_setting_textarea_to_br($value) {
    return str_replace(["\r\n", "\r", "\n"], '<br>', $value);
}

function loopis_setting_textarea_from_br($value) {
    return str_replace(['<br />', '<br/>', '<br>'], "\n", $value);
}
