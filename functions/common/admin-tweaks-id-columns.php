<?php
/**
 * Add ID columns to WordPress admin lists
 * 
 * Adds ID column to posts, pages, custom post types, categories, tags, 
 * custom taxonomies, media library, and comments.
 * 
 * @package LOOPIS_Admin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add ID column to all post types (posts, pages, custom post types)
 */
function loopis_admin_add_id_column_posts($columns) {
    // Add ID column after checkbox (first position)
    $new_columns = array();
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key === 'cb') {
            $new_columns['id'] = 'ID';
        }
    }
    return $new_columns;
}

function loopis_admin_show_id_column_posts($column, $post_id) {
    if ($column === 'id') {
        echo $post_id;
    }
}

// Apply to all post types
add_filter('manage_posts_columns', 'loopis_admin_add_id_column_posts');
add_action('manage_posts_custom_column', 'loopis_admin_show_id_column_posts', 10, 2);

add_filter('manage_pages_columns', 'loopis_admin_add_id_column_posts');
add_action('manage_pages_custom_column', 'loopis_admin_show_id_column_posts', 10, 2);

// For custom post types, add dynamically
function loopis_admin_add_id_to_custom_post_types() {
    $post_types = get_post_types(array('_builtin' => false), 'names');
    foreach ($post_types as $post_type) {
        add_filter("manage_{$post_type}_posts_columns", 'loopis_admin_add_id_column_posts');
        add_action("manage_{$post_type}_posts_custom_column", 'loopis_admin_show_id_column_posts', 10, 2);
    }
}
add_action('admin_init', 'loopis_admin_add_id_to_custom_post_types');

/**
 * Add ID column to categories
 */
function loopis_admin_add_id_column_categories($columns) {
    $columns['id'] = 'ID';
    return $columns;
}

function loopis_admin_show_id_column_categories($content, $column_name, $term_id) {
    if ($column_name === 'id') {
        return $term_id;
    }
    return $content;
}

add_filter('manage_edit-category_columns', 'loopis_admin_add_id_column_categories');
add_filter('manage_category_custom_column', 'loopis_admin_show_id_column_categories', 10, 3);

/**
 * Add ID column to tags
 */
add_filter('manage_edit-post_tag_columns', 'loopis_admin_add_id_column_categories');
add_filter('manage_post_tag_custom_column', 'loopis_admin_show_id_column_categories', 10, 3);

/**
 * Add ID column to all custom taxonomies
 */
function loopis_admin_add_id_to_custom_taxonomies() {
    $taxonomies = get_taxonomies(array('_builtin' => false), 'names');
    foreach ($taxonomies as $taxonomy) {
        add_filter("manage_edit-{$taxonomy}_columns", 'loopis_admin_add_id_column_categories');
        add_filter("manage_{$taxonomy}_custom_column", 'loopis_admin_show_id_column_categories', 10, 3);
    }
}
add_action('admin_init', 'loopis_admin_add_id_to_custom_taxonomies');

/**
 * Add ID column to media library
 */
function loopis_admin_add_id_column_media($columns) {
    $columns['id'] = 'ID';
    return $columns;
}

function loopis_admin_show_id_column_media($column_name, $post_id) {
    if ($column_name === 'id') {
        echo $post_id;
    }
}

add_filter('manage_media_columns', 'loopis_admin_add_id_column_media');
add_action('manage_media_custom_column', 'loopis_admin_show_id_column_media', 10, 2);

/**
 * Add ID column to comments
 */
function loopis_admin_add_id_column_comments($columns) {
    $new_columns = array();
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key === 'cb') {
            $new_columns['id'] = 'ID';
        }
    }
    return $new_columns;
}

function loopis_admin_show_id_column_comments($column, $comment_id) {
    if ($column === 'id') {
        echo $comment_id;
    }
}

add_filter('manage_edit-comments_columns', 'loopis_admin_add_id_column_comments');
add_action('manage_comments_custom_column', 'loopis_admin_show_id_column_comments', 10, 2);

/**
 * Make ID column sortable for all post types
 */
function loopis_admin_make_id_sortable_posts($columns) {
    $columns['id'] = 'ID';
    return $columns;
}

// Built-in post types
add_filter('manage_edit-post_sortable_columns', 'loopis_admin_make_id_sortable_posts');
add_filter('manage_edit-page_sortable_columns', 'loopis_admin_make_id_sortable_posts');
add_filter('manage_upload_sortable_columns', 'loopis_admin_make_id_sortable_posts'); // Media

// Custom post types
function loopis_admin_make_id_sortable_custom_post_types() {
    $post_types = get_post_types(array('_builtin' => false), 'names');
    foreach ($post_types as $post_type) {
        add_filter("manage_edit-{$post_type}_sortable_columns", 'loopis_admin_make_id_sortable_posts');
    }
}
add_action('admin_init', 'loopis_admin_make_id_sortable_custom_post_types');

/**
 * Make ID column sortable for taxonomies (categories, tags, custom taxonomies)
 */
function loopis_admin_make_id_sortable_terms($columns) {
    $columns['id'] = 'term_id';
    return $columns;
}

// Built-in taxonomies
add_filter('manage_edit-category_sortable_columns', 'loopis_admin_make_id_sortable_terms');
add_filter('manage_edit-post_tag_sortable_columns', 'loopis_admin_make_id_sortable_terms');

// Custom taxonomies
function loopis_admin_make_id_sortable_custom_taxonomies() {
    $taxonomies = get_taxonomies(array('_builtin' => false), 'names');
    foreach ($taxonomies as $taxonomy) {
        add_filter("manage_edit-{$taxonomy}_sortable_columns", 'loopis_admin_make_id_sortable_terms');
    }
}
add_action('admin_init', 'loopis_admin_make_id_sortable_custom_taxonomies');

/**
 * Make ID column sortable for comments
 */
function loopis_admin_make_id_sortable_comments($columns) {
    $columns['id'] = 'comment_ID';
    return $columns;
}
add_filter('manage_edit-comments_sortable_columns', 'loopis_admin_make_id_sortable_comments');

/**
 * Handle the actual sorting for posts by ID
 */
function loopis_admin_sort_posts_by_id($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }
    
    if ('ID' === $query->get('orderby')) {
        $query->set('orderby', 'ID');
    }
}
add_action('pre_get_posts', 'loopis_admin_sort_posts_by_id');

/**
 * Handle the actual sorting for terms (categories, tags) by ID
 */
function loopis_admin_sort_terms_by_id($args, $taxonomies) {
    if (isset($_GET['orderby']) && $_GET['orderby'] === 'term_id') {
        $args['orderby'] = 'term_id';
        $args['order'] = isset($_GET['order']) ? $_GET['order'] : 'ASC';
    }
    return $args;
}
add_filter('get_terms_args', 'loopis_admin_sort_terms_by_id', 10, 2);

/**
 * Handle the actual sorting for comments by ID
 */
function loopis_admin_sort_comments_by_id($query) {
    if (!is_admin()) {
        return;
    }
    
    if (isset($_GET['orderby']) && $_GET['orderby'] === 'comment_ID') {
        $query->query_vars['orderby'] = 'comment_ID';
    }
}
add_action('pre_get_comments', 'loopis_admin_sort_comments_by_id');

/**
 * Add CSS to make ID column narrow
 */
function loopis_admin_id_column_css() {
    echo '<style>
        /* ID column - minimal width */
        .wp-list-table .column-id {
            width: 60px;
            text-align: center;
        }
        
        /* Make header text centered too */
        .wp-list-table th.column-id,
        .wp-list-table td.column-id {
            text-align: center;
        }
        
        /* Sortable ID column */
        .wp-list-table th.column-id.sortable a,
        .wp-list-table th.column-id.sorted a {
            padding: 0;
        }
    </style>';
}
add_action('admin_head', 'loopis_admin_id_column_css');