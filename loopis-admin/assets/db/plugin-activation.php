<?php
/**
 * Actions performed when activating the LOOPIS plugin.
 */
 
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

 // Add tables and default options
function loopis_db_setup() {

    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    // Create loopis_lockers table
    $lockers_table = $wpdb->prefix . 'loopis_lockers';
    $sql1 = "CREATE TABLE IF NOT EXISTS $lockers_table (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    dbDelta($sql1);

    // Add required columns
    $existing_lockers_columns = $wpdb->get_col("SHOW COLUMNS FROM $lockers_table", 0);
    $required_locker_columns = [
        'locker_id' => 'ALTER TABLE ' . $lockers_table . ' ADD COLUMN locker_id varchar(32) NOT NULL',
        'locker_name' => 'ALTER TABLE ' . $lockers_table . ' ADD COLUMN locker_name varchar(128) DEFAULT NULL',
        'postal_code' => 'ALTER TABLE ' . $lockers_table . ' ADD COLUMN postal_code varchar(16) NOT NULL',
        'code' => 'ALTER TABLE ' . $lockers_table . ' ADD COLUMN code varchar(32) DEFAULT NULL',
        'fetch_warning' => 'ALTER TABLE ' . $lockers_table . ' ADD COLUMN fetch_warning tinyint(1) DEFAULT 0',
        'leave_warning' => 'ALTER TABLE ' . $lockers_table . ' ADD COLUMN leave_warning tinyint(1) DEFAULT 0',
    ];
    foreach ($required_locker_columns as $col => $sql_add) {
        if (!in_array($col, $existing_lockers_columns)) {
            $wpdb->query($sql_add);
        }
    }

    // Add unique key for locker_id if it doesn't exist
    $indexes = $wpdb->get_results("SHOW INDEX FROM $lockers_table WHERE Key_name = 'locker_id'");
    if (empty($indexes)) {
        $wpdb->query("ALTER TABLE $lockers_table ADD UNIQUE KEY locker_id (locker_id)");
    }

    // Create loopis_settings table
    $settings_table = $wpdb->prefix . 'loopis_settings';
    $sql2 = "CREATE TABLE IF NOT EXISTS $settings_table (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    dbDelta($sql2);

    // Add required columns
    $existing_settings_columns = $wpdb->get_col("SHOW COLUMNS FROM $settings_table", 0);
    $required_settings_columns = [
        'setting_key' => 'ALTER TABLE ' . $settings_table . ' ADD COLUMN setting_key varchar(64) NOT NULL',
        'setting_value' => 'ALTER TABLE ' . $settings_table . ' ADD COLUMN setting_value longtext NOT NULL',
    ];
    foreach ($required_settings_columns as $col => $sql_add) {
        if (!in_array($col, $existing_settings_columns)) {
            $wpdb->query($sql_add);
        }
    }

    // Add unique key for setting_key if it doesn't exist
    $indexes = $wpdb->get_results("SHOW INDEX FROM $settings_table WHERE Key_name = 'setting_key'");
    if (empty($indexes)) {
        $wpdb->query("ALTER TABLE $settings_table ADD UNIQUE KEY setting_key (setting_key)");
    }

    // Insert default settings if not present
    $defaults = array(
        'welcome_email_subject' => '💚 Välkommen!',
        'welcome_email_greeting' => 'Hej [user_first_name]!',
        'welcome_email_message' => '<div style="padding: 10px;text-align: center;font-size: 18px;background: #f5f5f5;border-radius: 10px"><p>🎉 Ditt konto på LOOPIS.app är nu aktiverat.<br>→  Logga in med din vanliga webbläsare.</p><p><a href="/faq/tips-till-ny-medlem/">📌 Tips till ny medlem!</a></p></div>',
        'welcome_email_footer' => '<table style="border-collapse: collapse"><tr><td style="vertical-align: middle;padding-right: 5px"><img src="/wp-content/images/LOOPIS_icon.png" alt="LOOPIS_logo" style="height: 32px"></td><td style="vertical-align: middle;width: 275px"><p style="font-size: 11px;font-style: italic;margin: 0;line-height: 1.2">Information från <a href="/">LOOPIS.app</a> <br> angående ditt användarkonto.</p></td></tr></table>',
        'locker_fetch_warning' => '⚠ Det är mycket saker i skåpen just nu! <br>🐎 Hämta gärna så snabbt som möjligt.',
        'locker_leave_warning' => '⚠ Det är mycket saker i skåpen just nu! <br>🐌 Vänta gärna någon dag med att lämna.',
        'event_name' => '🛸 LOOPIS HQ',
        'event_name_history' => serialize(array('🌳 LOOPIS på torget', '🛸 LOOPIS HQ')),
    );
    foreach ($defaults as $key => $value) {
        $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $settings_table WHERE setting_key = %s", $key));
        if (!$exists) {
            $wpdb->insert($settings_table, [
                'setting_key' => $key,
                'setting_value' => $value
            ]);
        }
    }
}