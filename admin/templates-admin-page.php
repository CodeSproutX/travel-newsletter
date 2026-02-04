<?php

if (!function_exists('tn_get_message') && defined('TN_PATH')) {
    require_once TN_PATH . 'lang/newsletter-message.php';
}

global $wpdb;
$table = $wpdb->prefix . 'travel_newsletter_templates';

$edit = null;
if (!empty($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table WHERE id = %d", $edit_id)
    );
}


$templates = $wpdb->get_results("SELECT * FROM $table ORDER BY days_before DESC");
?>


<div class="wrap">
    <style>
        .form-table {
            width: 100% !important;
        }
    </style>
    <div class="my-card">
        <h1><?php echo $edit ? 'Edit Email' : 'Add New Email'; ?></h1>
    </div>
    <div class="my-card">
        <strong>Placeholders:</strong> {name}, {travel_date}
    </div>
    <div class="my-card">
        <?php if (isset($_GET['success'])): ?>
            <div class="notice notice-success is-dismissible">
                <p><?php echo esc_html(tn_get_message('template_saved_successfully')); ?></p>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="notice notice-success is-dismissible">
                <p><?php echo esc_html(tn_get_message('template_deleted_successfully')); ?></p>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="notice notice-error is-dismissible">
                <p><?php
                    if ($_GET['error'] === 'template_not_found') {
                        echo esc_html(tn_get_message('template_not_found'));
                    } elseif ($_GET['error'] === 'test_send_failed') {
                        echo esc_html(tn_get_message('test_email_failed'));
                    } else {
                        echo esc_html(tn_get_message('db_error'));
                    }
                    ?></p>
            </div>
        <?php endif; ?>

        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <input type="hidden" name="action" value="tn_save_template">
            <input type="hidden" name="id" value="<?php echo esc_attr($edit->id ?? ''); ?>">

            <table class="form-table">
                <tr>
                    <th>נושא האימייל:</th>
                    <td>
                        <input type="text" name="subject" class="regular-text"
                            value="<?php echo esc_attr($edit->subject ?? ''); ?>" required>
                    </td>
                </tr>

                <tr>
                    <th>כמה ימים לפני הנסיעה לשלוח?</th>
                    <td>
                        <input type="number" name="days_before"
                            value="<?php echo esc_attr($edit->days_before ?? ''); ?>" required>
                    </td>
                </tr>

                <tr>
                    <th>תוכן ההודעה:</th>
                    <td>
                        <?php
                        wp_editor($edit->content ?? '', 'content', [
                            'textarea_name' => 'content',
                            'textarea_rows' => 40,
                            'tinymce' => [
                                'content_css' => plugins_url('template-editor.css', __FILE__)
                            ]
                        ]);

                        ?>
                    </td>
                </tr>

                <tr>
                    <th>Active</th>
                    <td>
                        <input type="checkbox" name="is_active" value="1" <?php checked($edit->is_active ?? 1, 1); ?>>
                    </td>
                </tr>
            </table>

            <?php wp_nonce_field('tn_template', 'tn_nonce'); ?>
            <div style="display: flex;gap: 10px;align-items: center;">
                <p><button class="button button-primary">Save Email</button></p>
                <?php
                if ($edit) {
                    echo '<p><a href="#" class="tn-view-template button button-primary"
                                data-template-id="' . $edit_id . '">Preview</a></p>';
                    echo ' <a href="#" class="tn-send-test-email button button-primary" data-template-id="' . $edit_id . '">Send
                                Test Email</a>';
                }
                ?>
            </div>
        </form>
    </div>

</div>

<style>
    .tn-template-preview {
        text-align: right;
        max-width: 100%;
        margin: 0;
        padding: 0;
    }

    .tn-template-preview-header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    tn-template-preview-header-content p {
        margin: 0;
        padding: 0;
    }

    .tn-template-preview-header {
        background: #f8f9fa;
        padding: 5px 15px;
        border-radius: 6px;
        margin-bottom: 20px;
        border: 1px solid #e1e1e1;
    }

    .tn-template-preview-header h3 {
        margin: 0;
        font-size: 20px;
        font-weight: 600;
        line-height: 1.5;
        word-wrap: break-word;
        padding: 0;
    }

    .tn-template-preview-header p {
        margin: 0;
        font-size: 14px;
        color: #555;
        line-height: 1.6;
        padding: 0;
    }

    .tn-template-preview-header p strong {
        color: #333;
        font-weight: 600;
        margin-right: 8px;
    }

    .tn-template-preview-content {
        background: #fff;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        min-height: 200px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .tn-template-preview-content h4 {
        margin-top: 0;
        margin-bottom: 20px;
        color: #333;
        border-bottom: 2px solid #2271b1;
        padding-bottom: 12px;
        font-size: 16px;
        font-weight: 600;
    }

    .tn-template-preview-content p {
        margin: 15px 0;
        line-height: 1.8;
        color: #333;
    }

    .tn-template-preview-content img {
        max-width: 100%;
        height: auto;
        margin: 15px 0;
        border-radius: 4px;
    }

    .tn-template-meta {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 2px solid #e1e1e1;
        font-size: 13px;
    }

    .tn-template-meta span {
        color: #666;
        line-height: 1.6;
    }

    .tn-template-meta strong {
        color: #333;
        font-weight: 600;
        margin-right: 5px;
    }

    .tn-template-preview-wrapper {
        margin: 0 30px;
    }

    /* Custom scrollbar styling */
    .tn-template-preview-wrapper::-webkit-scrollbar {
        width: 8px;
    }

    .tn-template-preview-wrapper::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .tn-template-preview-wrapper::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .tn-template-preview-wrapper::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Ensure SweetAlert2 content is visible and properly spaced */
    .swal2-html-container {
        overflow: auto !important;
        max-height: none !important;
        padding: 0 !important;
        margin: 0 !important;
        flex: 1 1 auto !important;
        min-height: 0 !important;
    }

    .swal2-popup {
        padding: 10px !important;
        display: flex !important;
        flex-direction: column !important;
        max-height: 90vh !important;
    }

    .swal2-title {
        padding-bottom: 1.5rem !important;
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        flex-shrink: 0 !important;
    }

    .swal2-actions {
        flex-shrink: 0 !important;
        margin-top: 1rem !important;
    }
</style>
<script>
    jQuery(document).ready(function($) {
        // Handle View Template click
        $('.tn-view-template').on('click', function(e) {
            e.preventDefault();
            var templateId = $(this).data('template-id');

            // Show loading state
            Swal.fire({
                title: '<?php echo esc_js(__('Loading...', 'travel-newsletter')); ?>',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // AJAX request to get template
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'tn_view_template_ajax',
                    template_id: templateId,
                    nonce: '<?php echo wp_create_nonce('tn_view_template_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        var template = response.data;
                        var statusBadge = template.is_active ?
                            '<span style="background: #00a32a; color: white; padding: 3px 8px; border-radius: 3px; font-size: 11px;">Active</span>' :
                            '<span style="background: #d63638; color: white; padding: 3px 8px; border-radius: 3px; font-size: 11px;">Inactive</span>';

                        Swal.fire({
                            title: '<?php echo esc_js(__('Preview Mode', 'travel-newsletter')); ?>',
                            html: '<div class="tn-template-preview-scroll">' +
                                '<div class="tn-template-preview-wrapper">' +
                                '<div class="tn-template-preview">' +
                                '<div class="tn-template-preview-header">' +
                                '<div class="tn-template-preview-header-content">' +
                                '<h3>' + template.preview_subject + '</h3>' +
                                '<p><strong><?php echo esc_js(__('Status:', 'travel-newsletter')); ?></strong> ' +
                                statusBadge + '</p>' +
                                '</div>' +
                                '</div>' +
                                '<div class="tn-template-preview-content">' +
                                template.preview_content +
                                '</div>' +
                                '<div class="tn-template-meta">' +
                                '<span><strong><?php echo esc_js(__('Created:', 'travel-newsletter')); ?></strong> ' +
                                (template.created_at ? template.created_at.split(' ')[0] : '') + '</span>' +
                                '</div>' +
                                '</div>' + // tn-template-preview
                                '</div>' + // tn-template-preview-wrapper
                                '</div>', // tn-template-preview-scroll
                            width: '682px',
                            padding: '10px',
                            confirmButtonText: '<?php echo esc_js(__('Close', 'travel-newsletter')); ?>',
                            confirmButtonColor: '#2271b1',
                            showCancelButton: false,
                            customClass: {
                                popup: 'tn-template-preview-popup',
                                htmlContainer: 'tn-template-preview-html'
                            },
                            didOpen: () => {
                                // Ensure proper flex layout for scrolling
                                var popup = document.querySelector('.swal2-popup');
                                var htmlContainer = document.querySelector(
                                    '.swal2-html-container');
                                if (popup && htmlContainer) {
                                    popup.style.display = 'flex';
                                    popup.style.flexDirection = 'column';
                                    popup.style.maxHeight = '90vh';
                                    htmlContainer.style.flex = '1 1 auto';
                                    htmlContainer.style.minHeight = '0';
                                    htmlContainer.style.overflow = 'hidden';
                                }
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: '<?php echo esc_js(__('Error', 'travel-newsletter')); ?>',
                            text: response.data.message,
                            confirmButtonColor: '#2271b1'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: '<?php echo esc_js(__('Error', 'travel-newsletter')); ?>',
                        text: '<?php echo esc_js(__('An error occurred. Please try again.', 'travel-newsletter')); ?>',
                        confirmButtonColor: '#2271b1'
                    });
                }
            });
        });

        // Handle Send Test Email click
        $('.tn-send-test-email').on('click', function(e) {
            e.preventDefault();
            var templateId = $(this).data('template-id');
            var defaultEmail = '<?php echo esc_js(get_option('admin_email')); ?>';

            // Show SweetAlert2 popup with input
            Swal.fire({
                title: '<?php echo esc_js(tn_get_message('send_test_email')); ?>',
                html: '<input id="swal-email-input" type="email" class="swal2-input" placeholder="<?php echo esc_js(__('Email Address', 'travel-newsletter')); ?>" value="' +
                    defaultEmail + '" required>' +
                    '<p style="text-align: left; margin-top: 10px; color: #666; font-size: 13px;"><?php echo esc_js(__('Enter the email address where you want to receive the test email.', 'travel-newsletter')); ?></p>',
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: '<?php echo esc_js(__('Send', 'travel-newsletter')); ?>',
                cancelButtonText: '<?php echo esc_js(__('Cancel', 'travel-newsletter')); ?>',
                confirmButtonColor: '#2271b1',
                cancelButtonColor: '#a7aaad',
                reverseButtons: true,
                focusConfirm: false,
                preConfirm: () => {
                    const email = document.getElementById('swal-email-input').value;
                    if (!email) {
                        Swal.showValidationMessage(
                            '<?php echo esc_js(__('Please enter an email address.', 'travel-newsletter')); ?>'
                        );
                        return false;
                    }
                    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                        Swal.showValidationMessage(
                            '<?php echo esc_js(__('Please enter a valid email address.', 'travel-newsletter')); ?>'
                        );
                        return false;
                    }
                    return email;
                },
                didOpen: () => {
                    // Focus on email input
                    setTimeout(() => {
                        document.getElementById('swal-email-input').focus();
                    }, 100);
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    var testEmail = result.value;

                    // Show loading state
                    Swal.fire({
                        title: '<?php echo esc_js(__('Sending...', 'travel-newsletter')); ?>',
                        text: '<?php echo esc_js(__('Please wait while we send the test email.', 'travel-newsletter')); ?>',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // AJAX request
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'tn_send_test_email_ajax',
                            template_id: templateId,
                            test_email: testEmail,
                            nonce: '<?php echo wp_create_nonce('tn_test_email_nonce'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: '<?php echo esc_js(__('Success!', 'travel-newsletter')); ?>',
                                    text: response.data.message,
                                    confirmButtonColor: '#2271b1'
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: '<?php echo esc_js(__('Error', 'travel-newsletter')); ?>',
                                    text: response.data.message,
                                    confirmButtonColor: '#2271b1'
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: '<?php echo esc_js(__('Error', 'travel-newsletter')); ?>',
                                text: '<?php echo esc_js(__('An error occurred. Please try again.', 'travel-newsletter')); ?>',
                                confirmButtonColor: '#2271b1'
                            });
                        }
                    });
                }
            });
        });
    });
</script>