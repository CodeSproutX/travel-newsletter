<?php

/**
 * Email Scheduler Module
 * Handles scheduling emails based on travel dates and WordPress cron setup.
 * Emails are sent by a daily cron; only future send dates are queued (no retroactive sends).
 * Template is chosen by days_before (one template per "X days before travel").
 */

/** Days-before sequence: 30, 25, 20, 15, 10, 5, 2 */
define('TN_DAYS_BEFORE_SEQUENCE', [30, 25, 20, 15, 10, 5, 2]);

/**
 * Queue only emails that are still in the future relative to travel date.
 * Template is selected by days_before (each template has a days_before value).
 */
function tn_schedule_emails($subscriber_id, $travel_date)
{
    global $wpdb;

    $today = date('Y-m-d');
    $table = $wpdb->prefix . 'travel_newsletter_templates';
    $queue_table = $wpdb->prefix . 'travel_newsletter_queue';

    // Load active templates keyed by days_before so we pick the right template per step
    $templates = $wpdb->get_results(
        "SELECT * FROM $table WHERE is_active = 1",
        OBJECT_K
    );

    if (empty($templates)) {
        return;
    }

    // Build a map days_before => template (prefer one template per days_before)
    $by_days_before = [];
    foreach ($templates as $t) {
        $by_days_before[intval($t->days_before)] = $t;
    }

    foreach (TN_DAYS_BEFORE_SEQUENCE as $days_before) {
        $template = isset($by_days_before[$days_before]) ? $by_days_before[$days_before] : null;
        if (!$template) {
            continue;
        }

        $send_date = date(
            'Y-m-d',
            strtotime($travel_date . ' - ' . intval($days_before) . ' days')
        );

        if ($send_date < $today) {
            continue; // Missed emails are never sent
        }

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
                    'send_date' => $send_date,
                ],
                ['%d', '%d', '%s']
            );
        }
    }
}

/**
 * Daily cron: send all queued emails whose send_date is today or in the past.
 * Each email is sent only once (queue status set to 'sent' after send).
 */
add_action('tn_send_scheduled_emails', 'tn_process_scheduled_emails');

function tn_process_scheduled_emails()
{
    global $wpdb;

    $today = date('Y-m-d');
    $queue_table = $wpdb->prefix . 'travel_newsletter_queue';
    $subscribers_table = $wpdb->prefix . 'travel_newsletter_subscribers';
    $templates_table = $wpdb->prefix . 'travel_newsletter_templates';

    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT q.id AS queue_id, q.subscriber_id, q.template_id
         FROM $queue_table q
         WHERE q.send_date <= %s AND q.status = 'pending'
         ORDER BY q.send_date ASC",
        $today
    ), ARRAY_A);

    if (empty($rows)) {
        return;
    }

    foreach ($rows as $row) {
        $subscriber = $wpdb->get_row($wpdb->prepare(
            "SELECT id, name, email, travel_date FROM $subscribers_table WHERE id = %d",
            $row['subscriber_id']
        ), OBJECT);

        $template = $wpdb->get_row($wpdb->prepare(
            "SELECT id, subject, content FROM $templates_table WHERE id = %d",
            $row['template_id']
        ), OBJECT);

        if (!$subscriber || !$template) {
            continue;
        }

        $sent = tn_send_email(
            $subscriber->email,
            $template->subject,
            $template->content,
            [
                'name' => $subscriber->name,
                'travel_date' => $subscriber->travel_date,
            ]
        );

        if ($sent) {
            $wpdb->update(
                $queue_table,
                ['status' => 'sent', 'sent_at' => current_time('mysql')],
                ['id' => $row['queue_id']],
                ['%s', '%s'],
                ['%d']
            );
        }
    }
}

function tn_schedule_cron()
{
    if (!wp_next_scheduled('tn_send_scheduled_emails')) {
        wp_schedule_event(time(), 'daily', 'tn_send_scheduled_emails');
    }
}

function tn_unschedule_cron()
{
    wp_clear_scheduled_hook('tn_send_scheduled_emails');
}