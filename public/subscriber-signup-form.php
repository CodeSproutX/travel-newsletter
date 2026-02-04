<style>
    #tn-subscriber-form-wrapper input {
        margin-bottom: 0px !important;
    }

    input:focus:required:invalid,
    select:focus:required:invalid,
    textarea:focus:required:invalid {
        color: unset;
    }

    .email-field-error .field-button-group {
        height: 66px;
    }

    .tn-form-group-wrapper {
        display: flex;
        align-items: end;
    }

    .field-button-group {
        flex: 0 0 25%;
    }

    .tn-form-group .field-group {
        flex: 0 0 31% !important;
    }

    .tn-form-group {
        flex: 0 0 75%;
        display: flex;
        gap: 13px;
    }

    .subscribe-button {
        border: var(--btns-border-width);
        color: var(--btns-text-color);
        transition: var(--btns-transition);
        line-height: 36.5px;
        font-size: var(--button-font-size);
        font-weight: var(--btns-font-weight);
        margin-top: 0;
        border-radius: var(--btns-border-radius);
        position: var(--button-position-static);
    }

    @media only screen and (max-width: 768px) {

        .tn-form-group,
        .field-group {
            flex: 0 0 100%;
            max-width: 100%;
        }

        .tn-form-group {
            gap: 20px;
        }
    }

    .field-error {
        color: #dc3545;
        font-size: 12px;
        margin-top: 5px;
        display: block;
    }

    .field-error-input {
        border-color: #dc3545 !important;
        border-width: 2px !important;
    }

    .field-error-input:focus {
        border-color: #dc3545 !important;
        outline-color: #dc3545 !important;
    }
</style>
<div id="tn-subscriber-form-wrapper">
    <p class="contact_home_text" style="margin-top: 0;margin-bottom:15px;"><b>הירשמו לניוזלטר וקבלו מידע על אירועים
            ופעילויות בתקופת שהותכם בפורטוגל.</b></p>
    <div id="tn-subscriber-success-message" style="display: none;">
        <p style="color:green; padding: 15px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px;">
            <?php echo esc_html(tn_get_message('signup_success')); ?>
        </p>
    </div>

    <form id="tn-subscriber-form" method="post">
        <div class="tn-form-group-wrapper">
            <div class="tn-form-group">
                <div class="field-group">
                    <label>השם שלך:</label>
                    <input type="text" name="name" id="tn-name" placeholder="שם" required>
                </div>

                <div class="field-group">
                    <div>
                        <label>האימייל שלך:</label>
                        <input type="email" name="email" id="tn-email" placeholder="אימייל" required>
                    </div>
                    <div id="email-error-message" style="display: none;">
                        <div id="email-error-text" style="margin-top: 15px;">

                        </div>
                    </div>
                </div>

                <div class="field-group">
                    <label>תאריך נחיתתכם בפורטוגל:</label>
                    <input type="text" name="travel_date" id="tn-travel-date" placeholder="dd/mm/yyyy" required>
                </div>

                <?php wp_nonce_field('tn_save', 'tn_nonce'); ?>
            </div>
            <div class="field-button-group">
                <div class="field-group button">
                    <button type="submit" class="subscribe-button" id="tn-submit-btn">שלחו לי טיפים על פורטוגל</button>
                </div>
            </div>
        </div>

        <div id="tn-subscriber-error-message" style="display: none;">
            <div id="tn-subscriber-error-text" style="margin-top: 15px;">
                <?php _e('An error occurred. Please try again.', 'travel-newsletter'); ?>
            </div>
        </div>
    </form>
</div>

<script>
    jQuery(document).ready(function($) {
        const messages = {
            invalidEmail: <?php echo json_encode(tn_get_message('he_invalid_email')); ?>,
            nameRequired: <?php echo json_encode(tn_get_message('name_required')); ?>,
            dateRequired: <?php echo json_encode(tn_get_message('date_required')); ?>,
            heInvalidEmail: <?php echo json_encode(tn_get_message('he_invalid_email')); ?>,
            heRequiredEmail: <?php echo json_encode(tn_get_message('he_required_email')); ?>,
            sender: <?php echo json_encode(tn_get_message('he_sender')); ?>,
            heDbError: <?php echo json_encode(tn_get_message('db_error')); ?>,

        };

        // Email validation function
        function isValidEmail(email) {
            var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        // Email live validation as you stop typing (debounced)
        let emailInputTimer = null;
        $('#tn-email').on('input', function() {
            // As soon as the user starts typing, remove the error message
            $('#email-error-message').hide();
            $('#email-error-text').html('');
            $('#tn-email').removeClass('field-error-input');
            $('.tn-form-group-wrapper').removeClass('email-field-error');
            // Debounce: only validate after they pause typing for 500ms
            clearTimeout(emailInputTimer);
            let $this = $(this);
            emailInputTimer = setTimeout(function() {
                var emailVal = $this.val().trim();
                if (emailVal === '') {
                    // No error if empty, let 'required' or submit handle it
                    return;
                }
                if (!isValidEmail(emailVal)) {
                    $('#email-error-message').show();
                    $('#email-error-text').html(messages.heInvalidEmail);
                    $this.addClass('field-error-input');
                    $('.tn-form-group-wrapper').addClass('email-field-error');
                }
            }, 500);
        });

        // Also validate email on blur (when user is done, regardless of debounce)
        $('#tn-email').on('blur', function() {
            var emailVal = $(this).val().trim();
            if (emailVal === '') {
                // No error if empty, let 'required' or submit handle it
                $('#email-error-message').hide();
                $('#email-error-text').html('');
                $(this).removeClass('field-error-input');
                return;
            }
            if (!isValidEmail(emailVal)) {
                $('#email-error-message').show();
                $('#email-error-text').html(messages.heInvalidEmail);
                $(this).addClass('field-error-input');
            } else {
                $('#email-error-message').hide();
                $('#email-error-text').html('');
                $(this).removeClass('field-error-input');
            }
        });

        // Function to clear all errors
        function clearErrors() {
            $('#tn-subscriber-error-message').hide();
            $('#tn-subscriber-error-text').html('');
            $('.field-error-input').removeClass('field-error-input');
            $('#email-error-message').hide();
            $('#email-error-text').html('');
            $('#tn-email').removeClass('field-error-input');
        }

        // Function to show error messages in the main error div and highlight field
        function showFieldError(fieldId, message) {
            var field = $('#' + fieldId);

            // Add error class to input
            field.addClass('field-error-input');

            // Show the error message in the main error div
            $('#tn-subscriber-error-text').html(message);
            $('#tn-subscriber-error-message').show();
        }

        // Handle form submission
        $('#tn-subscriber-form').on('submit', function(e) {
            e.preventDefault();

            var form = $(this);
            var submitBtn = $('#tn-submit-btn');
            var successMsg = $('#tn-subscriber-success-message');

            // Clear previous errors
            clearErrors();
            successMsg.hide();

            // Get form data
            var formData = {
                action: 'tn_save_subscriber_ajax',
                name: $('#tn-name').val().trim(),
                email: $('#tn-email').val().trim(),
                travel_date: $('#tn-travel-date').val().trim(),
                tn_nonce: $('#tn_nonce').val()
            };

            // Basic client-side validation
            var hasErrors = false;
            if (!formData.name) {
                showFieldError('tn-name', messages.nameRequired);
                hasErrors = true;
            } else if (!formData.email) {
                showFieldError('tn-email', messages.heRequiredEmail);
                hasErrors = true;
            } else if (!isValidEmail(formData.email)) {
                showFieldError('tn-email', messages.heInvalidEmail);
                hasErrors = true;
            } else if (!formData.travel_date) {
                showFieldError('tn-travel-date', messages.dateRequired);
                hasErrors = true;
            }

            if (hasErrors) {
                return;
            }

            // Disable submit button
            var originalBtnText = submitBtn.text();
            submitBtn.prop('disabled', true).text(messages.sender);

            // AJAX request
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        successMsg.show();
                        // Hide form
                        form.hide();
                        // Reset form
                        form[0].reset();
                        clearErrors();
                    } else {
                        // Show field-specific error or general error
                        var errorField = response.data.field || 'general';
                        var errorMessage = response.data.message ||
                            messages.heDbError;

                        // Map field names to input IDs
                        var fieldMap = {
                            'name': 'tn-name',
                            'email': 'tn-email',
                            'travel_date': 'tn-travel-date',
                            'general': null
                        };

                        if (fieldMap[errorField]) {
                            showFieldError(fieldMap[errorField], errorMessage);
                        } else {
                            // If general error, show on first field
                            $('#tn-subscriber-error-text').html(errorMessage);
                            $('#tn-subscriber-error-message').show();
                        }

                        // Re-enable submit button
                        submitBtn.prop('disabled', false).text(originalBtnText);
                    }
                },
                error: function() {
                    // Show error in general error div
                    $('#tn-subscriber-error-text').html(messages.heDbError);
                    $('#tn-subscriber-error-message').show();
                    // Re-enable submit button
                    submitBtn.prop('disabled', false).text(originalBtnText);
                }
            });
        });

        // Clear field-specific error when user types for all fields except email
        $('#tn-name, #tn-travel-date').on('input', function() {
            var fieldId = $(this).attr('id');
            $('#' + fieldId).removeClass('field-error-input');
            $('#tn-subscriber-error-text').text('');
            $('#tn-subscriber-error-message').hide();
        });
        // (Note: #tn-email is handled above for immediate error clearing)
    });
</script>