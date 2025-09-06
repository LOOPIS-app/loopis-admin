<?php
/**
 * Setup of the WordPress admin menu.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Add the admin menu pages
add_action('admin_menu', 'loopis_custom_admin_menu');

function loopis_custom_admin_menu() {
    global $menu;

    // Change the name of the Posts menu directly by its position
    $menu[5][0] = 'Saker att få'; // New name for Posts

    // Add categories as sub-items under "Saker att få"
    add_categories_submenu('Saker att få', 'edit.php', 'category');

    // Add custom post type menus after "Saker att få"
    add_custom_post_type_menu('borrow', 'Saker att låna', LOOPIS_PLUGIN_URL . '/assets/img/wp-admin-menu/icon-borrow.png', 'category', 6);
    add_custom_post_type_menu('booking', 'Bokningar', LOOPIS_PLUGIN_URL . '/assets/img/wp-admin-menu/icon-bookings.png', 'booking-status', 7);
    add_custom_post_type_menu('forum', 'Forum', LOOPIS_PLUGIN_URL . '/assets/img/wp-admin-menu/icon-forum.png', 'forum-category', 8);
    add_custom_post_type_menu('support', 'Support', LOOPIS_PLUGIN_URL . '/assets/img/wp-admin-menu/icon-support.png', 'support-status', 9);
    
    // Adjust the position of the Media menu to appear after the separator
    $menu[13] = $menu[10]; // Move Media to position 13
    unset($menu[10]); // Remove the old position

    // Add a menu separator
    $menu[10] = array('', 'read', 'separator1', '', 'wp-menu-separator');

    // Add top-level menu for Locker (Skåp)
    add_menu_page(
        'Skåp', // Page title
        'Skåp', // Menu title
        'manage_options', // Capability
        'loopis-locker-general', // Menu slug
        'loopis_locker_general', // Function to display the page content
        LOOPIS_PLUGIN_URL . '/assets/img/wp-admin-menu/icon-locker.png', // Path to your custom settings icon
        10 // Position, before Inställningar (which is 11)
    );

    // Rename first sub-menu and add sub-menus
    add_submenu_page(
        'loopis-locker-general', // Parent slug
        'Aktiva skåp', // Page title
        'Aktiva skåp', // Menu title
        'manage_options', // Capability
        'loopis-locker-general', // Menu slug
        'loopis_locker_general' // Callback function to render the page
    );

    add_submenu_page(
        'loopis-locker-general',
        'Redigera skåp',
        'Redigera skåp',
        'manage_options',
        'loopis-locker-edit',
        'loopis_locker_edit'
    );

    add_submenu_page(
        'loopis-locker-general',
        'Meddelanden',
        'Meddelanden',
        'manage_options',
        'loopis-locker-messages',
        'loopis_locker_messages'
    );

    // Add top-level menu for Settings
    add_menu_page(
        'Inställningar', // Page title
        'Inställningar', // Menu title
        'manage_options', // Capability
        'loopis-settings-general', // Menu slug
        'loopis_settings_general', // Function to display the page content
        LOOPIS_PLUGIN_URL . '/assets/img/wp-admin-menu/icon-settings.png', // Path to your custom settings icon
        11 // Position
    );

    // Rename first sub-menu and add sub-menus
    add_submenu_page(
        'loopis-settings-general', // Parent slug
        'Allmänt', // Page title
        'Allmänt', // Menu title
        'manage_options', // Capability
        'loopis-settings-general', // Menu slug
        'loopis_settings_general' // Callback function to render the page
    );

    add_submenu_page(
        'loopis-settings-general',
        'Postnummer',
        'Postnummer',
        'manage_options',
        'loopis-settings-postal-codes',
        'loopis_settings_postal_codes'
    );

    add_submenu_page(
        'loopis-settings-general',
        'Event',
        'Event',
        'manage_options',
        'loopis-settings-event',
        'loopis_settings_event'
    );
    
    add_submenu_page(
        'loopis-settings-general',
        'Roller',
        'Roller',
        'manage_options',
        'loopis-settings-roles',
        'loopis_settings_roles'
    );

    add_submenu_page(
        'loopis-settings-general',
        'Välkomstmail',
        'Välkomstmail',
        'manage_options',
        'loopis-settings-welcome',
        'loopis_settings_welcome'
    );

    // Add a menu separator
    $menu[12] = array('', 'read', 'separator2', '', 'wp-menu-separator');
}

function add_categories_submenu($parent_title, $parent_slug, $taxonomy) {
    // Fetch all categories, including empty ones, and order them by ID in ascending order
    $categories = get_categories(array(
        'hide_empty' => false,
        'orderby' => 'ID',
        'order' => 'ASC'
    ));

    foreach ($categories as $category) {
        // Check if the category is a child category
        if ($category->parent != 0) {
            // Construct the URL for filtering posts by category
            $url = 'edit.php?post_status=all&post_type=post&cat=' . $category->term_id;

            add_submenu_page(
                $parent_slug, // Parent slug
                $category->name, // Page title
                $category->name, // Menu title
                'manage_categories', // Capability
                $url // Menu slug with link
            );
        }
    }
}

function add_custom_post_type_menu($post_type, $menu_title, $icon_url, $taxonomy, $position) {
    // Add the top-level menu for the custom post type
    add_menu_page(
        $menu_title, // Page title
        $menu_title, // Menu title
        'manage_options', // Capability
        'edit.php?post_type=' . $post_type, // Menu slug
        '', // Function
        $icon_url, // Use the icon URL directly
        $position // Position
    );

    // Add "All Posts" submenu item
    add_submenu_page(
        'edit.php?post_type=' . $post_type, // Parent slug
        'All Posts ' . $menu_title, // Page title
        'All Posts', // Menu title
        'manage_options', // Capability
        'edit.php?post_type=' . $post_type // Menu slug with link
    );

    // Add "Add New" submenu item
    add_submenu_page(
        'edit.php?post_type=' . $post_type, // Parent slug
        'Add New ' . $menu_title, // Page title
        'Add New', // Menu title
        'manage_options', // Capability
        'post-new.php?post_type=' . $post_type // Menu slug with link
    );

    // Add "Categories" submenu item for taxonomy management
    add_submenu_page(
        'edit.php?post_type=' . $post_type, // Parent slug
        'Manage Categories', // Page title
        'Categories', // Menu title
        'manage_options', // Capability
        'edit-tags.php?taxonomy=' . $taxonomy . '&post_type=' . $post_type // Menu slug with link
    );

    // Only add taxonomy terms as sub-items if the post type is not "borrow"
    if ($post_type !== 'borrow') {
        // Fetch all terms for the taxonomy
        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
            'fields' => 'all', // Ensure we get all fields of the term objects
        ));

        // Check if terms are valid and not an error
        if (!is_wp_error($terms) && !empty($terms)) {
            // Add a submenu item for each term under the custom post type menu
            foreach ($terms as $term) {
                // Construct the URL for filtering posts by custom taxonomy term
                $url = 'edit.php?post_type=' . $post_type . '&' . $taxonomy . '=' . $term->slug;

                add_submenu_page(
                    'edit.php?post_type=' . $post_type, // Parent slug
                    $term->name, // Page title
                    $term->name, // Menu title
                    'manage_options', // Capability
                    $url // Menu slug with link
                );
            }
        }
    }
}

// Function to replace admin menu icons with custom PNG images
add_action('admin_head', 'loopis_replace_admin_menu_icons_css');

function loopis_replace_admin_menu_icons_css() {
    ?>
    <style>
        /* General styling for menu icons */
        #adminmenu div.wp-menu-image {
            float: left;
            width: 36px;
            height: 34px;
            margin: 0;
            text-align: center;
            background-repeat: no-repeat !important;
            background-position: center center !important;
        }

        /* Posts icon */
        #adminmenu #menu-posts .wp-menu-image {
            background-image: url('<?php echo LOOPIS_PLUGIN_URL; ?>/assets/img/wp-admin-menu/icon-posts.png') !important;
        }

        /* Settings icon */
        #adminmenu #toplevel_page_installningar .wp-menu-image {
            background-image: url('<?php echo LOOPIS_PLUGIN_URL; ?>/assets/img/wp-admin-menu/icon-settings.png') !important;
        }

        /* Hide original Dashicon */
        #adminmenu #menu-posts .wp-menu-image:before,
        #adminmenu #toplevel_page_installningar .wp-menu-image:before {
            content: none !important;
        }

        /* Image padding and opacity */
        #adminmenu .wp-menu-image img {
            padding: 9px 0 0;
            opacity: .6;
        }

        /* Hover and active state for images */
        #adminmenu li.menu-top:hover .wp-menu-image img, 
        #adminmenu li.wp-has-current-submenu .wp-menu-image img {
            opacity: 1;
        }
    </style>
    <?php
}