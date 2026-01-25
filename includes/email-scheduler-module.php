<?php

/**
 * Email Scheduler Module
 * Handles scheduling emails based on travel dates and WordPress cron setup
 */

function tn_schedule_emails($subscriber_id, $travel_date)
{
    global $wpdb;

    $today = date('Y-m-d');
    $table = $wpdb->prefix . 'travel_newsletter_templates';
    $queue_table = $wpdb->prefix . 'travel_newsletter_queue';

    $templates = $wpdb->get_results(
        "SELECT * FROM $table WHERE is_active = 1"
    );

    if (empty($templates)) {
        return;
    }

    foreach ($templates as $template) {
        $send_date = date(
            'Y-m-d',
            strtotime($travel_date . ' - ' . intval($template->days_before) . ' days')
        );

        if ($send_date < $today) {
            continue; // skip missed emails
        }

        // Check if already scheduled
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $queue_table WHERE subscriber_id = %d AND template_id = %d",
            $subscriber_id,
            $template->id
        ));

        if (!$existing) {
            $wpdb->insert(
                $queue_table,
                [
                    'subscriber_id' => $subscriber_id,
                    'template_id' => $template->id,
                    'send_date' => $send_date
                ],
                ['%d', '%d', '%s']
            );
        }
    }
}

function tn_schedule_cron()
{
    if (!wp_next_scheduled('tn_send_scheduled_emails')) {
        wp_schedule_event(time(), 'hourly', 'tn_send_scheduled_emails');
    }
}

function tn_unschedule_cron()
{
    $timestamp = wp_next_scheduled('tn_send_scheduled_emails');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'tn_send_scheduled_emails');
    }
}
