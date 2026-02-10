<?php
/**
 * Page with settings for LOOPIS admin.
 */
 
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Function to display content of settings page
function loopis_settings_welcome() {
    // Page title and description
    echo '<h1>⚙ Welcome email</h1>';
    echo '<p>💡 Email sent when a new user account is activated.</p>';

    // Check if the form was submitted
    if (isset($_POST['submit'])) {
        // Sanitize the input
        $welcome_email_subject = sanitize_text_field($_POST['welcome_email_subject']);
        $welcome_email_greeting = sanitize_text_field($_POST['welcome_email_greeting']);
        $welcome_email_message = wp_kses_post($_POST['welcome_email_message']);
        $welcome_email_footer = wp_kses_post($_POST['welcome_email_footer']);

        // Save new settings to custom table
        loopis_update_setting('welcome_email_subject', $welcome_email_subject);
        loopis_update_setting('welcome_email_greeting', $welcome_email_greeting);
        loopis_update_setting('welcome_email_message', $welcome_email_message);
        loopis_update_setting('welcome_email_footer', $welcome_email_footer);
    }

    // Get current settings from custom table
    $welcome_email_subject = loopis_get_setting('welcome_email_subject', '');
    $welcome_email_greeting = loopis_get_setting('welcome_email_greeting', '');
    $welcome_email_message = loopis_get_setting('welcome_email_message', '');
    $welcome_email_footer = loopis_get_setting('welcome_email_footer', '');

    // Display the form
    ?>
        <form method="post">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="welcome_email_subject">Subject</label></th>
                    <td>
                        <input type="text" id="welcome_email_subject" name="welcome_email_subject" value="<?php echo esc_attr($welcome_email_subject); ?>" class="regular-text" />
                        <p class="description">Email subject.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="welcome_email_greeting">Greeting</label></th>
                    <td>
                        <input type="text" id="welcome_email_greeting" name="welcome_email_greeting" value="<?php echo esc_attr($welcome_email_greeting); ?>" class="regular-text" />
                        <p class="description">Email title. Use [user_first_name] to input user name.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="welcome_email_message">Message</label></th>
                    <td>
                        <textarea id="welcome_email_message" name="welcome_email_message" rows="5" class="regular-text" style="width: 100%;"><?php echo esc_textarea($welcome_email_message); ?></textarea>
                        <p class="description">Email message. Use HTML tags to format.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="welcome_email_footer">Footer</label></th>
                    <td>
                        <textarea id="welcome_email_footer" name="welcome_email_footer" rows="5" class="regular-text" style="width: 100%;"><?php echo esc_textarea($welcome_email_footer); ?></textarea>
                        <p class="description">Email footer. Use HTML tags to format.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button('Save Changes', 'primary', 'submit'); ?>
        </form>
    <?php
}