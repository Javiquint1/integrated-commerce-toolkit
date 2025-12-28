<?php
/**
 * Plugin Name: Integrated Commerce Toolkit
 * Plugin URI: https://github.com/Javiquint1/integrated-commerce-toolkit
 * Description: A modular OOP framework for integrating external commerce services with WordPress using secure practices and API caching.
 * Version: 1.0.0
 * Author: Javiquint1
 * Author URI: https://github.com/Javiquint1
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: integrated-commerce-toolkit
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ICT_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('ICT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ICT_VERSION', '1.0.0');

// Load main plugin class
require_once ICT_PLUGIN_PATH . 'inc/class-main.php';

// Initialize the plugin
function ict_init() {
    new ICT_Main();
}

// Hook to WordPress init
add_action('plugins_loaded', 'ict_init');
