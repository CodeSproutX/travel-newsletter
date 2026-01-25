<?php

function tn_install_tables()
{
    global $wpdb;
    $charset = $wpdb->get_charset_collate();

    $subscribers = $wpdb->prefix . 'travel_newsletter_subscribers';
    $templates   = $wpdb->prefix . 'travel_newsletter_templates';
    $queue       = $wpdb->prefix . 'travel_newsletter_queue';

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    dbDelta("CREATE TABLE $subscribers (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255),
        email VARCHAR(255) UNIQUE,
        travel_date DATE,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset;");

    dbDelta("CREATE TABLE $templates (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        subject VARCHAR(255),
        content LONGTEXT,
        days_before INT,
        is_active TINYINT DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset;");

    dbDelta("CREATE TABLE $queue (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        subscriber_id BIGINT,
        template_id BIGINT,
        send_date DATE,
        status ENUM('pending','sent') DEFAULT 'pending',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        sent_at DATETIME NULL,
        UNIQUE KEY unique_send (subscriber_id, template_id)
    ) $charset;");
}
