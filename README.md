# Travel Newsletter

A WordPress plugin for managing travel date-based newsletter subscriptions with automated email scheduling.

## Description

Travel Newsletter is a WordPress plugin that lets users subscribe through a custom registration form by providing their name, email, and travel date.
Once registered, the plugin automatically schedules and sends email templates based on how many days remain before the subscriber’s travel date.

-- You can create multiple email templates — for example:
-- Email sent 20 days before travel
-- Email sent 10 days before travel
-- Email sent 3 days before travel

Each template is fully customizable, supports placeholders, and can be previewed or tested directly from the WordPress admin panel.

### Key Features

- **Subscriber Management**: Easy-to-use subscription form with AJAX submission
- **Email Templates**: Create and manage multiple email templates with customizable content
- **Automated Scheduling**: Automatically schedules emails based on travel dates
- **Template Preview**: View email templates with placeholder replacements
- **Test Email**: Send test emails directly from the admin panel
- **Modern UI**: Beautiful admin interface with SweetAlert2 modals
- **RTL Support**: Full support for right-to-left languages (Hebrew)
- **Placeholder System**: Use `{name}` and `{travel_date}` placeholders in templates
- **Queue Management**: Automatic email queue system with WordPress cron integration.

### How does the email scheduling work?

-- The plugin uses WordPress cron to check for scheduled emails hourly.
When a subscriber signs up, the plugin stores their details in a database.
-- The user provides:
Name
Email
Travel Date
-- The plugin stores the subscriber details.
-- Based on the travel date, the plugin schedules emails using your predefined templates.
-- The plugin sends the emails to the subscriber's email address.

## Installation

### Manual Installation

1. Download the plugin files
2. Upload the `travel-newsletter` folder to `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. The plugin will automatically create necessary database tables on activation

### Via WordPress Admin

1. Go to **Plugins** → **Add New**
2. Click **Upload Plugin**
3. Choose the `travel-newsletter.zip` file
4. Click **Install Now**
5. Activate the plugin

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

## Usage

### Setting Up Email Templates

1. Navigate to **Travel Newsletter** → **Email Templates** in WordPress admin
2. Click **Add New Template** or edit an existing one
3. Fill in the template details:
   - **Subject**: Email subject line (supports placeholders: `{name}`, `{travel_date}`)
   - **Days Before Travel**: Number of days before travel date to send this email
   - **Content**: Email body content (supports HTML and placeholders)
   - **Active**: Check to enable this template
4. Click **Save Template**

### Adding the Subscription Form

Add the subscription form to any page or post using the shortcode:

```
[travel_newsletter_form]
```

The form includes:

- Name field
- Email field
- Travel date field (dd/mm/yyyy format) (you can use any js library like pickaday or datepicker)
- AJAX submission (no page reload)

### Managing Subscribers

1. Go to **Travel Newsletter** → **Subscribers** in WordPress admin
2. View all registered subscribers
3. Delete subscribers using the delete link (with SweetAlert2 confirmation)

### Viewing Templates

1. Go to **Travel Newsletter** → **Email Templates**
2. Click **View Template** next to any template
3. Preview the template with sample data in a modal popup

### Sending Test Emails

1. Go to **Travel Newsletter** → **Email Templates**
2. Click **Send Test Email** next to any template
3. Enter the recipient email address
4. Click **Send**

## Placeholders

The plugin supports the following placeholders in email templates:

- `{name}` - Subscriber's name
- `{travel_date}` - Subscriber's travel date

These placeholders are automatically replaced when emails are sent.

## File Structure

```
travel-newsletter/
├── admin/
│   ├── subscribers-admin-page.php    # Subscribers management page
│   └── templates-admin-page.php      # Templates management page
├── includes/
│   ├── database-setup-module.php     # Database table creation
│   ├── subscriber-module.php         # Subscriber handling & AJAX
│   ├── template-module.php           # Template management & AJAX
│   ├── email-scheduler-module.php    # Email scheduling logic
│   └── email-handler-module.php      # Email sending functionality
├── public/
│   └── subscriber-signup-form.php    # Public subscription form
└── travel-newsletter.php             # Main plugin file
```

## Database Tables

The plugin creates the following database tables:

- `wp_travel_newsletter_subscribers` - Stores subscriber information
- `wp_travel_newsletter_templates` - Stores email templates
- `wp_travel_newsletter_queue` - Stores scheduled emails queue

## Hooks & Filters

### Actions

- `tn_send_scheduled_emails` - Fires when processing the email queue (hourly cron)

### Functions

- `tn_send_email($to, $subject, $content, $replacements)` - Send email with template
- `tn_schedule_emails($subscriber_id, $travel_date)` - Schedule emails for subscriber

## AJAX Endpoints

- `tn_save_subscriber_ajax` - Save subscriber via AJAX
- `tn_send_test_email_ajax` - Send test email via AJAX
- `tn_view_template_ajax` - Get template data for preview
- `tn_delete_subscriber_ajax` - Delete subscriber via AJAX

## Shortcodes

- `[travel_newsletter_form]` - Display the subscription form for frontend

## Styling

The plugin includes custom CSS for:

- Subscription form (RTL support)
- Admin pages
- SweetAlert2 modals
- Template preview

## Security

- All form submissions use WordPress nonces
- Input sanitization and validation
- Capability checks for admin functions
- SQL injection prevention with prepared statements
- XSS protection with proper escaping

## Changelog

### 1.0.0

- Initial release
- Subscriber registration with AJAX
- Email template management
- Automated email scheduling
- Template preview functionality
- Test email functionality
- SweetAlert2 integration
- RTL language support

### Admin - Subscribers Page

View and manage all newsletter subscribers.

### Admin - Templates Page

Create and manage email templates with preview functionality.

### Subscription Form

User-friendly subscription form with AJAX submission.

### Template Preview Modal

Preview templates with sample data before sending.

---

**Note**: Make sure your WordPress site has proper email configuration (SMTP) for emails to be sent successfully.
