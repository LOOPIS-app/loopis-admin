<?php
/**
 * Page with settings for LOOPIS admin.
 */
 
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Function to display content of settings page
function loopis_settings_reports() {
    // Ensure default notification settings exist
    loopis_add_setting('cron_reminders_report', 'off');
    loopis_add_setting('cron_reminders_report_email', '');
    loopis_add_setting('cron_raffle_report', 'off');
    loopis_add_setting('cron_raffle_report_email', '');

    // Handle form submission
    if (isset($_POST['submit_reports']) && wp_verify_nonce($_POST['reports_nonce'], 'save_reports')) {
        $reminders_report_enabled = isset($_POST['cron_reminders_report']) ? 'on' : 'off';
        $reminders_report_emails = isset($_POST['cron_reminders_report_email'])
            ? sanitize_textarea_field($_POST['cron_reminders_report_email'])
            : '';
        $raffle_report_enabled = isset($_POST['cron_raffle_report']) ? 'on' : 'off';
        $raffle_report_emails = isset($_POST['cron_raffle_report_email'])
            ? sanitize_textarea_field($_POST['cron_raffle_report_email'])
            : '';

        loopis_update_setting('cron_reminders_report', $reminders_report_enabled);
        loopis_update_setting('cron_reminders_report_email', $reminders_report_emails);
        loopis_update_setting('cron_raffle_report', $raffle_report_enabled);
        loopis_update_setting('cron_raffle_report_email', $raffle_report_emails);

        echo '<div class="notice notice-success is-dismissible"><p>Settings saved!</p></div>';
    }

    $reminders_report_enabled = loopis_get_setting('cron_reminders_report', 'off') === 'on';
    $reminders_report_emails = loopis_get_setting('cron_reminders_report_email', '');
    $raffle_report_enabled = loopis_get_setting('cron_raffle_report', 'off') === 'on';
    $raffle_report_emails = loopis_get_setting('cron_raffle_report_email', '');

    // Page title and description
    echo '<div class="wrap">';
    echo '<h1>📨 Rapporter</h1>';
    echo '<p>💡 Cronjob email reports for admins.</p>';

    echo '<form method="post" action="">';
    wp_nonce_field('save_reports', 'reports_nonce');
    echo '<table class="form-table">';

    echo '<tr>';
    echo '<th scope="row">Reminder report</th>';
    echo '<td>';
    echo '<label><input type="checkbox" name="cron_reminders_report" value="on" ' . checked($reminders_report_enabled, true, false) . '> Enable</label>';
    echo '<p class="description">0-5 email reports per day.</p>';
    echo '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<th scope="row">Recipients</th>';
    echo '<td>';
    echo '<textarea name="cron_reminders_report_email" rows="3" cols="50" class="large-text">' . esc_textarea($reminders_report_emails) . '</textarea>';
    echo '<p class="description">One or more email addresses, separated by commas.</p>';
    echo '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<th scope="row">Raffle report</th>';
    echo '<td>';
    echo '<label><input type="checkbox" name="cron_raffle_report" value="on" ' . checked($raffle_report_enabled, true, false) . '> Enable</label>';
    echo '<p class="description">One email report per day.</p>';
    echo '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<th scope="row">Recipients</th>';
    echo '<td>';
    echo '<textarea name="cron_raffle_report_email" rows="3" cols="50" class="large-text">' . esc_textarea($raffle_report_emails) . '</textarea>';
    echo '<p class="description">One or more email addresses, separated by commas.</p>';
    echo '</td>';
    echo '</tr>';

    echo '</table>';
    echo '<p class="submit"><input type="submit" name="submit_reports" class="button-primary" value="Save settings"></p>';
    echo '</form>';
    echo '</div>';
}