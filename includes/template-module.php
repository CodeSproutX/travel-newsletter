<?php

/**
 * Template Module
 * Handles email template management, CRUD operations, and test email functionality
 */

add_action('admin_menu', function () {
    add_submenu_page(
        'newsletter',
        'Email Sequences',
        'Email Sequences',
        'manage_options',
        'travel-newsletter-templates-list',
        'tn_templates_page_list'
    );
    add_submenu_page(
        'newsletter',
        'Add New Email',
        'Add New Email',
        'manage_options',
        'travel-newsletter-templates',
        'tn_templates_page'
    );
});

function tn_templates_page_list()
{
    wp_enqueue_script('jquery');

    // Enqueue SweetAlert2
    wp_enqueue_style('sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css', [], '11.0.0');
    wp_enqueue_script('sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11', [], '11.0.0', true);
    include TN_PATH . 'admin/templates-admin-list-pages.php';
}

function tn_templates_page()
{
    // Enqueue jQuery (it's usually already loaded in admin, but just to be sure)
    wp_enqueue_script('jquery');

    // Enqueue SweetAlert2
    wp_enqueue_style('sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css', [], '11.0.0');
    wp_enqueue_script('sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11', [], '11.0.0', true);

    include TN_PATH . 'admin/templates-admin-page.php';
}

add_action('admin_post_tn_save_template', 'tn_save_template');
add_action('admin_post_tn_delete_template', 'tn_delete_template');

// AJAX handler for test email
add_action('wp_ajax_tn_send_test_email_ajax', 'tn_send_test_email_ajax');

// AJAX handler for viewing template
add_action('wp_ajax_tn_view_template_ajax', 'tn_view_template_ajax');

function tn_save_template()
{
    if (!isset($_POST['tn_nonce']) || !wp_verify_nonce($_POST['tn_nonce'], 'tn_template')) {
        wp_die(__('Invalid request', 'travel-newsletter'));
    }

    global $wpdb;
    $table = $wpdb->prefix . 'travel_newsletter_templates';

    $data = [
        'subject' => sanitize_text_field($_POST['subject']),
        'content' => wp_kses_post(wp_unslash($_POST['content'])),
        'days_before' => intval($_POST['days_before']),
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
    ];

    $format = ['%s', '%s', '%d', '%d'];

    if (!empty($_POST['id'])) {
        $id = intval($_POST['id']);
        $result = $wpdb->update($table, $data, ['id' => $id], $format, ['%d']);
    } else {
        $result = $wpdb->insert($table, $data, $format);
    }

    if ($result === false) {

        wp_redirect(admin_url('admin.php?page=travel-newsletter-templates&error=db_error'));
        exit;
    }

    // If updating existing template → stay on edit screen
    if (!empty($_POST['id'])) {
        $id = intval($_POST['id']);
        wp_redirect(admin_url('admin.php?page=travel-newsletter-templates&edit=' . $id . '&success=1'));
        exit;
    }

    // If new insert → go back to list
    wp_redirect(admin_url('admin.php?page=travel-newsletter-templates&success=1'));
    exit;
}

function tn_delete_template()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'travel-newsletter'));
    }

    if (!isset($_GET['id']) || !isset($_GET['_wpnonce'])) {
        wp_die(__('Invalid request', 'travel-newsletter'));
    }

    if (!wp_verify_nonce($_GET['_wpnonce'], 'tn_delete_template_' . intval($_GET['id']))) {
        wp_die(__('Invalid request', 'travel-newsletter'));
    }

    global $wpdb;
    $table = $wpdb->prefix . 'travel_newsletter_templates';
    $id = intval($_GET['id']);

    $wpdb->delete($table, ['id' => $id], ['%d']);

    wp_redirect(admin_url('admin.php?page=travel-newsletter-templates-list&deleted=1'));
    exit;
}

function tn_send_test_email_ajax()
{
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'tn_test_email_nonce')) {
        wp_send_json_error(['message' => __('Invalid request', 'travel-newsletter')]);
    }

    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => __('You do not have sufficient permissions.', 'travel-newsletter')]);
    }

    // Validate input
    if (empty($_POST['template_id']) || empty($_POST['test_email'])) {
        wp_send_json_error(['message' => __('Template ID and email address are required.', 'travel-newsletter')]);
    }

    $template_id = intval($_POST['template_id']);
    $test_email = sanitize_email($_POST['test_email']);

    // Validate email
    if (!is_email($test_email)) {
        wp_send_json_error(['message' => __('Please enter a valid email address.', 'travel-newsletter')]);
    }

    global $wpdb;
    $table = $wpdb->prefix . 'travel_newsletter_templates';

    // Get template
    $template = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $template_id));

    if (!$template) {
        wp_send_json_error(['message' => __('Template not found.', 'travel-newsletter')]);
    }

    // Send test email with sample data
    $replacements = [
        'name' => 'Test User',
        'travel_date' => date('Y-m-d', strtotime('+7 days'))
    ];

    $sent = tn_send_email($test_email, $template->subject, $template->content, $replacements);

    if ($sent) {
        wp_send_json_success(['message' => __('Test email sent successfully!', 'travel-newsletter')]);
    } else {
        wp_send_json_error(['message' => __('Failed to send test email. Please check your email configuration.', 'travel-newsletter')]);
    }
}

function tn_view_template_ajax()
{
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'tn_view_template_nonce')) {
        wp_send_json_error(['message' => __('Invalid request', 'travel-newsletter')]);
    }

    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => __('You do not have sufficient permissions.', 'travel-newsletter')]);
    }

    // Validate input
    if (empty($_POST['template_id'])) {
        wp_send_json_error(['message' => __('Template ID is required.', 'travel-newsletter')]);
    }

    $template_id = intval($_POST['template_id']);

    global $wpdb;
    $table = $wpdb->prefix . 'travel_newsletter_templates';

    // Get template
    $template = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $template_id));

    if (!$template) {
        wp_send_json_error(['message' => __('Template not found.', 'travel-newsletter')]);
    }

    // Process content with placeholders replaced for preview
    $preview_content = str_replace(
        ['{name}', '{travel_date}'],
        ['John Doe', date('Y-m-d', strtotime('+7 days'))],
        $template->content
    );

    $preview_subject = str_replace(
        ['{name}', '{travel_date}'],
        ['John Doe', date('Y-m-d', strtotime('+7 days'))],
        $template->subject
    );

    wp_send_json_success([
        'subject' => $template->subject,
        'content' => $template->content,
        'preview_subject' => $preview_subject,
        'preview_content' => wpautop($preview_content),
        'days_before' => $template->days_before,
        'is_active' => $template->is_active,
        'created_at' => $template->created_at
    ]);
}
