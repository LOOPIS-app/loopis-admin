<?php
/**
 * Add LOOPIS custom styles to the text editor of WP Admin.
 *
 */
 
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

add_action('init', function () {
    if (!function_exists('register_block_style')) {
        return;
    }

    register_block_style('core/paragraph', [
        'name' => 'wrapped',
        'label' => __('Wrapped', 'loopis-admin'),
    ]);
});

add_action('enqueue_block_editor_assets', function () {
    wp_register_script('loopis-editor-tweaks', '', ['wp-rich-text', 'wp-editor', 'wp-blocks', 'wp-element', 'wp-components', 'wp-data'], null, true);
    wp_enqueue_script('loopis-editor-tweaks');

    $inline_script = "(function (wp) {\n";
    $inline_script .= "    const { registerFormatType, toggleFormat } = wp.richText;\n";
    $inline_script .= "    const { RichTextToolbarButton } = wp.blockEditor;\n";
    $inline_script .= "    const { __ } = wp.i18n;\n";
    $inline_script .= "    const { createElement } = wp.element;\n\n";
    $inline_script .= "    registerFormatType('loopis/post-label', {\n";
    $inline_script .= "        title: __('LOOPIS Label'),\n";
    $inline_script .= "        tagName: 'span',\n";
    $inline_script .= "        className: 'post-label',\n";
    $inline_script .= "        attributes: { class: 'post-label' },\n";
    $inline_script .= "        edit: ({ isActive, value, onChange, onFocus }) => {\n";
    $inline_script .= "            return createElement(RichTextToolbarButton, {\n";
    $inline_script .= "                icon: 'editor-textcolor',\n";
    $inline_script .= "                title: __('LOOPIS Label'),\n";
    $inline_script .= "                onClick: () => onChange(toggleFormat(value, { type: 'loopis/post-label' })),\n";
    $inline_script .= "                isActive,\n";
    $inline_script .= "                onFocus,\n";
    $inline_script .= "            });\n";
    $inline_script .= "        },\n";
    $inline_script .= "    });\n";
    $inline_script .= "    registerFormatType('loopis/big-post-label', {\n";
    $inline_script .= "        title: __('LOOPIS big label'),\n";
    $inline_script .= "        tagName: 'span',\n";
    $inline_script .= "        className: 'big-post-label',\n";
    $inline_script .= "        attributes: { class: 'big-post-label' },\n";
    $inline_script .= "        edit: ({ isActive, value, onChange, onFocus }) => {\n";
    $inline_script .= "            return createElement(RichTextToolbarButton, {\n";
    $inline_script .= "                icon: 'editor-textcolor',\n";
    $inline_script .= "                title: __('LOOPIS big label'),\n";
    $inline_script .= "                onClick: () => onChange(toggleFormat(value, { type: 'loopis/big-post-label' })),\n";
    $inline_script .= "                isActive,\n";
    $inline_script .= "                onFocus,\n";
    $inline_script .= "            });\n";
    $inline_script .= "        },\n";
    $inline_script .= "    });\n";
    $inline_script .= "})(window.wp);\n";

    wp_add_inline_script('loopis-editor-tweaks', $inline_script);
});

add_filter('tiny_mce_before_init', function ($settings) {
    $settings['style_formats'] = json_encode([
        [
            'title' => 'LOOPIS Label',
            'inline' => 'span',
            'classes' => 'post-label',
        ],
        [
            'title' => 'LOOPIS big label',
            'inline' => 'span',
            'classes' => 'big-post-label',
        ],
    ]);
    return $settings;
});

add_editor_style('assets/css/base.css');