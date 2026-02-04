<?php

if (!defined('ABSPATH')) exit;

function tn_get_message($key)
{
    $messages = [

        // General
        'invalid_request'   => __('Invalid request.', 'travel-newsletter'),
        'db_error'          => __('An error occurred. Please try again later.', 'travel-newsletter'),

        // Validation
        'missing_fields'    => __('Please fill in all required fields.', 'travel-newsletter'),
        'name_required'     => __('Name is required.', 'travel-newsletter'),
        'email_required'    => __('Email is required.', 'travel-newsletter'),
        'invalid_email'     => __('Please enter a valid email address.', 'travel-newsletter'),
        'date_required'     => __('Travel date is required.', 'travel-newsletter'),
        'invalid_date'      => __('Please enter a valid travel date.', 'travel-newsletter'),

        'subscriber_id_required' => __('Subscriber ID is required.', 'travel-newsletter'),
        'delete_failed'     => __('Failed to delete subscriber. Please try again.', 'travel-newsletter'),
        'template_id_required' => __('Template ID is required.', 'travel-newsletter'),
        'template_not_found' => __('Template not found.', 'travel-newsletter'),
        'test_email_failed' => __('Failed to send test email. Please check your email configuration.', 'travel-newsletter'),
        'he_invalid_email' => 'כתובת אימייל לא חוקית',
        'he_required_email' => 'אימייל נדרש.',
        'he_sender' => 'שולח…',

        'template_saved_successfully' => __('Template saved successfully!', 'travel-newsletter'),
        'template_deleted_successfully' => __('Template deleted successfully!', 'travel-newsletter'),
        'send_test_email' => __('Send Test Email', 'travel-newsletter'),
        'test_email_sent_successfully' => __('Test email sent successfully!', 'travel-newsletter'),

        // Success
        'signup_success'    => __('Thank you for signing up!', 'travel-newsletter'),
        'delete_success'    => __('Subscriber deleted successfully!', 'travel-newsletter'),
        'test_email_sent'   => __('Test email sent successfully!', 'travel-newsletter'),

        // Custom HTML example
        'email_exists_html' => '<div style="padding:15px;border:1px solid #f5c6cb;border-radius:4px;font-size:16px;">
        email already exists
        </div>',
    ];

    return $messages[$key] ?? 'Invalid Request';
}