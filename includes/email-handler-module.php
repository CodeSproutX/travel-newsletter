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
    $default_replacements = [
        'name' => '',
        'travel_date' => date('Y-m-d')
    ];

    $replacements = array_merge($default_replacements, $replacements);

    $processed_content = str_replace(
        ['{name}', '{travel_date}'],
        [$replacements['name'], $replacements['travel_date']],
        $content
    );

    $processed_subject = str_replace(
        ['{name}', '{travel_date}'],
        [$replacements['name'], $replacements['travel_date']],
        $subject
    );

    $css_path = TN_PATH . 'template-editor.css';
    $css = (file_exists($css_path)) ? file_get_contents($css_path) : '';

    $message = "
        <div class='email-wrapper'>
        <style>
            $css
        </style>
            $processed_content
        </div>";

    $headers = ['Content-Type: text/html; charset=UTF-8'];

    return wp_mail($to, $processed_subject, $message, $headers);
}