<?php

/**
 * Plugin Name: Travel Newsletter
 * Plugin URI: https://example.com/travel-newsletter
 * Description: Travel date based newsletter system that sends automated emails to subscribers based on their travel dates.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: travel-newsletter
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

define('TN_PATH', plugin_dir_path(__FILE__));
define('TN_URL', plugin_dir_url(__FILE__));
define('TN_VERSION', '1.0.0');

require_once TN_PATH . 'includes/database-setup-module.php';
require_once TN_PATH . 'includes/subscriber-module.php';
require_once TN_PATH . 'includes/template-module.php';
require_once TN_PATH . 'includes/email-scheduler-module.php';
require_once TN_PATH . 'includes/email-handler-module.php';

register_activation_hook(__FILE__, 'tn_install_tables');
register_activation_hook(__FILE__, 'tn_schedule_cron');
register_deactivation_hook(__FILE__, 'tn_unschedule_cron');
