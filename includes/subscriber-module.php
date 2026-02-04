<?php

/**
 * Subscriber Module
 * Handles subscriber registration, form rendering, and admin pages
 */

/**
 * Shortcode
 */
add_shortcode('travel_newsletter_form', 'tn_render_form');

function tn_render_form()
{
    // Enqueue jQuery for AJAX (it's usually already loaded, but just to be sure)
    wp_enqueue_script('jquery');

    ob_start();
    include TN_PATH . 'public/subscriber-signup-form.php';
    return ob_get_clean();
}

/**
 * Save subscriber
 */
add_action('admin_post_tn_save_subscriber', 'tn_save_subscriber');
add_action('admin_post_nopriv_tn_save_subscriber', 'tn_save_subscriber');

// AJAX handler for subscriber form
add_action('wp_ajax_tn_save_subscriber_ajax', 'tn_save_subscriber_ajax');
add_action('wp_ajax_nopriv_tn_save_subscriber_ajax', 'tn_save_subscriber_ajax');

function tn_save_subscriber()
{
    if (!isset($_POST['tn_nonce']) || !wp_verify_nonce($_POST['tn_nonce'], 'tn_save')) {
        wp_die(__('Invalid request', 'travel-newsletter'));
    }

    // Validate required fields
    if (empty($_POST['name']) || empty($_POST['email']) || empty($_POST['travel_date'])) {
        wp_redirect(add_query_arg('error', 'missing_fields', wp_get_referer()));
        exit;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'travel_newsletter_subscribers';

    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $travel_date = sanitize_text_field($_POST['travel_date']);

    // Validate email
    if (!is_email($email)) {
        wp_redirect(add_query_arg('error', 'invalid_email', wp_get_referer()));
        exit;
    }

    // Validate date format
    $date_parts = explode('-', $travel_date);
    if (count($date_parts) !== 3 || !checkdate((int)$date_parts[1], (int)$date_parts[2], (int)$date_parts[0])) {
        wp_redirect(add_query_arg('error', 'invalid_date', wp_get_referer()));
        exit;
    }

    $result = $wpdb->insert($table, [
        'name' => $name,
        'email' => $email,
        'travel_date' => $travel_date,
    ], ['%s', '%s', '%s']);

    if ($result === false) {
        wp_redirect(add_query_arg('error', 'db_error', wp_get_referer()));
        exit;
    }

    $subscriber_id = $wpdb->insert_id;

    // Fix: Pass the inserted subscriber data (as array) to the notification function.



    // Schedule emails
    if ($subscriber_id) {
        tn_schedule_emails($subscriber_id, $travel_date);
    }

    wp_redirect(add_query_arg('success', '1', wp_get_referer()));
    exit;
}

function tn_send_admin_new_subscriber_notification($email, $name, $travel_date)
{
    // Recipient: WP admin email (or replace with dedicated option if exists)
    $admin_email =  get_option('admin_email');

    if (empty($admin_email)) {
        return false;
    }

    // Prepare subject and content
    $subject = 'New Newsletter Subscriber';

    // Defensive: Check for expected keys
    $subscriber_email = isset($email) ? esc_html($email) : '';
    $subscriber_name = isset($name) ? esc_html($name) : '';
    $subscriber_travel_date = isset($travel_date) ? esc_html($travel_date) : '';

    $content = '
        <strong>New subscriber registered</strong><br><br>
        <strong>Email:</strong> ' . $subscriber_email . '<br>
        <strong>Name:</strong> ' . $subscriber_name . '<br>
        <strong>Travel Date:</strong> ' . $subscriber_travel_date . '<br>
    ';

    // Send using wp_mail directly (or tn_send_email if you prefer)
    $headers = ['Content-Type: text/html; charset=UTF-8'];

    // Extra debug (for troubleshooting)
    // error_log("Sending admin mail to: $admin_email\nSubject: $subject\nContent:\n$content");
    $mail_sent = wp_mail($admin_email, $subject, $content, $headers);

    // Optional: Log the result for troubleshooting (can be removed in production)
    // error_log("Mail sent? " . var_export($mail_sent, true));

    return $mail_sent;
}


function tn_save_subscriber_ajax()
{
    // Verify nonce
    if (!isset($_POST['tn_nonce']) || !wp_verify_nonce($_POST['tn_nonce'], 'tn_save')) {
        wp_send_json_error(['field' => 'general', 'message' => tn_get_message('invalid_request')]);
    }

    $errors = [];

    // Validate required fields
    if (empty($_POST['name'])) {
        $errors['name'] = tn_get_message('name_required');
    }

    if (empty($_POST['email'])) {
        $errors['email'] = tn_get_message('email_required');
    }

    if (empty($_POST['travel_date'])) {
        $errors['travel_date'] = tn_get_message('date_required');
    }

    // If we have validation errors, return them
    if (!empty($errors)) {
        $first_error = reset($errors);
        $first_field = key($errors);
        wp_send_json_error(['field' => $first_field, 'message' => $first_error, 'errors' => $errors]);
    }

    global $wpdb;
    $table = $wpdb->prefix . 'travel_newsletter_subscribers';

    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $travel_date = sanitize_text_field($_POST['travel_date']);

    // Convert date format if needed (dd/mm/yyyy to yyyy-mm-dd)
    if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $travel_date, $matches)) {
        $travel_date = $matches[3] . '-' . $matches[2] . '-' . $matches[1];
    }

    // Validate email format
    if (!is_email($email)) {
        wp_send_json_error([
            'field'   => 'email',
            'message' => tn_get_message('invalid_email')
        ]);
    }

    // Validate date format
    $date_parts = explode('-', $travel_date);
    if (count($date_parts) !== 3 || !checkdate((int)$date_parts[1], (int)$date_parts[2], (int)$date_parts[0])) {
        wp_send_json_error([
            'field'   => 'email',
            'message' => tn_get_message('invalid_date')
        ]);
    }

    // Check if email already exists
    $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $table WHERE email = %s",
        $email
    ));
    if ($existing) {

        wp_send_json_error(['field' => 'email', 'message' => tn_get_message('email_exists_html')]);
    }

    $result = $wpdb->insert($table, [
        'name' => $name,
        'email' => $email,
        'travel_date' => $travel_date,
    ], ['%s', '%s', '%s']);


    if ($result === false) {
        wp_send_json_error(['field' => 'general', 'message' => tn_get_message('db_error')]);
    }

    $subscriber_id = $wpdb->insert_id;

    // Schedule emails
    if ($subscriber_id) {
        tn_schedule_emails($subscriber_id, $travel_date);
    }
    if ($subscriber_id) {
        tn_send_admin_new_subscriber_notification($email, $name, $travel_date);
    }

    wp_send_json_success(['message' => tn_get_message('signup_success')]);
}

/**
 * Admin menu
 */
add_action('admin_menu', function () {
    add_menu_page(
        'LS Newsletter',
        'LS Newsletter',
        'manage_options',
        'ls-newsletter',
        'tn_subscribers_page',
        'dashicons-email',
        10
    );
    add_submenu_page(
        'ls-newsletter',
        'Subscribers',
        'Subscribers',
        'manage_options',
        'travel-newsletter-subscribers',
        'tn_templates_subscribers_page'
    );
});
function tn_subscribers_page()
{
    include TN_PATH . 'admin/templates-main-page.php';
}
function tn_templates_subscribers_page()
{
    // Enqueue jQuery
    wp_enqueue_script('jquery');

    // Enqueue SweetAlert2
    wp_enqueue_style('sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css', [], '11.0.0');
    wp_enqueue_script('sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11', [], '11.0.0', true);

    include TN_PATH . 'admin/subscribers-admin-page.php';
}

// AJAX handler for deleting subscriber
add_action('wp_ajax_tn_delete_subscriber_ajax', 'tn_delete_subscriber_ajax');

function tn_delete_subscriber_ajax()
{
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'tn_delete_subscriber_nonce')) {
        wp_send_json_error(['message' => tn_get_message('invalid_request')]);
    }

    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => __('You do not have sufficient permissions.', 'travel-newsletter')]);
    }

    // Validate input
    if (empty($_POST['subscriber_id'])) {
        wp_send_json_error(['message' => tn_get_message('subscriber_id_required')]);
    }

    $subscriber_id = intval($_POST['subscriber_id']);

    global $wpdb;
    $table = $wpdb->prefix . 'travel_newsletter_subscribers';
    $queue_table = $wpdb->prefix . 'travel_newsletter_queue';

    // Delete from queue first (foreign key constraint)
    $wpdb->delete($queue_table, ['subscriber_id' => $subscriber_id], ['%d']);

    // Delete subscriber
    $result = $wpdb->delete($table, ['id' => $subscriber_id], ['%d']);

    if ($result === false) {
        wp_send_json_error(['message' => tn_get_message('delete_failed')]);
    }


    wp_send_json_success(['message' => tn_get_message('delete_success')]);
}