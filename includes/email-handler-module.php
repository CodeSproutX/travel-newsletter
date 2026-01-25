<?php

/**
 * Email Handler Module
 * Handles sending emails using templates with placeholder replacements
 */

/**
 * Reusable function to send email using a template
 * 
 * @param string $to Recipient email address
 * @param string $subject Email subject (with placeholders)
 * @param string $content Email content (with placeholders)
 * @param array $replacements Array of placeholder replacements (e.g., ['name' => 'John', 'travel_date' => '2024-01-15'])
 * @return bool Whether the email was sent successfully
 */
function tn_send_email($to, $subject, $content, $replacements = [])
{
    // Default replacements
    $default_replacements = [
        'name' => '',
        'travel_date' => date('Y-m-d')
    ];

    // Merge with provided replacements
    $replacements = array_merge($default_replacements, $replacements);

    // Replace placeholders in content
    $processed_content = str_replace(
        ['{name}', '{travel_date}'],
        [$replacements['name'], $replacements['travel_date']],
        $content
    );

    // Replace placeholders in subject
    $processed_subject = str_replace(
        ['{name}', '{travel_date}'],
        [$replacements['name'], $replacements['travel_date']],
        $subject
    );

    // Convert content to HTML if needed
    $message = wpautop($processed_content);
    $headers = ['Content-Type: text/html; charset=UTF-8'];

    // Send email
    $sent = wp_mail($to, $processed_subject, $message, $headers);

    return $sent;
}
