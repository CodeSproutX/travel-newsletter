<?php

global $wpdb;
$table = $wpdb->prefix . 'travel_newsletter_subscribers';
$subscribers = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC");
?>

<div class="wrap">
    <div class="my-card">
        <h1>Newsletter Subscribers</h1>
    </div>
    <div class="my-card">

        <table class="widefat fixed striped">
            <thead>

                <tr>
                    <th>ID</th>
                    <th>שם</th>
                    <th>אימייל</th>
                    <th>תאריך הנחיתה בפורטוגל</th>
                    <th>תאריך הרשמה</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($subscribers): ?>
                    <?php foreach ($subscribers as $sub): ?>
                        <tr>
                            <td><?php echo esc_html($sub->id); ?></td>
                            <td><?php echo esc_html($sub->name); ?></td>
                            <td><?php echo esc_html($sub->email); ?></td>
                            <td>
                                <?php
                                // Format travel_date as dd-mm-yyyy
                                if (!empty($sub->travel_date)) {
                                    $travel_date_formatted = date('d-m-Y', strtotime($sub->travel_date));
                                    echo esc_html($travel_date_formatted);
                                } else {
                                    echo '';
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                // Format created_at as dd-mm-yyyy
                                if (!empty($sub->created_at)) {
                                    $created_at_formatted = date('d-m-Y', strtotime($sub->created_at));
                                    echo esc_html($created_at_formatted);
                                } else {
                                    echo '';
                                }
                                ?>
                            </td>
                            <td>
                                <a href="#" class="tn-delete-subscriber" data-subscriber-id="<?php echo esc_attr($sub->id); ?>"
                                    data-subscriber-name="<?php echo esc_attr($sub->name); ?>">Delete</a>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6"><?php _e('No subscribers found', 'travel-newsletter'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    jQuery(document).ready(function($) {
        // Handle delete subscriber click
        $('.tn-delete-subscriber').on('click', function(e) {
            e.preventDefault();

            var $link = $(this);
            var subscriberId = $link.data('subscriber-id');
            var subscriberName = $link.data('subscriber-name') || 'this subscriber';

            // Show SweetAlert2 confirmation
            Swal.fire({
                title: '<?php echo esc_js(__('Delete Subscriber?', 'travel-newsletter')); ?>',
                text: '<?php echo esc_js(__('Are you sure you want to delete', 'travel-newsletter')); ?> ' +
                    subscriberName +
                    '? <?php echo esc_js(__('This action cannot be undone.', 'travel-newsletter')); ?>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<?php echo esc_js(__('Yes, Delete', 'travel-newsletter')); ?>',
                cancelButtonText: '<?php echo esc_js(__('Cancel', 'travel-newsletter')); ?>',
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading state
                    Swal.fire({
                        title: '<?php echo esc_js(__('Deleting...', 'travel-newsletter')); ?>',
                        text: '<?php echo esc_js(__('Please wait while we delete the subscriber.', 'travel-newsletter')); ?>',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // AJAX request to delete
                    $.ajax({
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        type: 'POST',
                        data: {
                            action: 'tn_delete_subscriber_ajax',
                            subscriber_id: subscriberId,
                            nonce: '<?php echo wp_create_nonce('tn_delete_subscriber_nonce'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                // Show success message
                                Swal.fire({
                                    icon: 'success',
                                    title: '<?php echo esc_js(__('Deleted!', 'travel-newsletter')); ?>',
                                    text: response.data.message,
                                    confirmButtonColor: '#3085d6',
                                    timer: 1500,
                                    timerProgressBar: true
                                }).then(() => {
                                    // Remove the row from table
                                    $link.closest('tr').fadeOut(300,
                                        function() {
                                            $(this).remove();

                                            // Check if table is empty
                                            if ($('.widefat tbody tr')
                                                .length === 0) {
                                                $('.widefat tbody').html(
                                                    '<tr><td colspan="6"><?php echo esc_js(__('No subscribers found', 'travel-newsletter')); ?></td></tr>'
                                                );
                                            }
                                        });
                                });
                            } else {
                                // Show error message
                                Swal.fire({
                                    icon: 'error',
                                    title: '<?php echo esc_js(__('Error', 'travel-newsletter')); ?>',
                                    text: response.data.message ||
                                        '<?php echo esc_js(__('Failed to delete subscriber. Please try again.', 'travel-newsletter')); ?>',
                                    confirmButtonColor: '#3085d6'
                                });
                            }
                        },
                        error: function() {
                            // Show error message
                            Swal.fire({
                                icon: 'error',
                                title: '<?php echo esc_js(__('Error', 'travel-newsletter')); ?>',
                                text: '<?php echo esc_js(__('An error occurred. Please try again.', 'travel-newsletter')); ?>',
                                confirmButtonColor: '#3085d6'
                            });
                        }
                    });
                }
            });
        });
    });
</script>