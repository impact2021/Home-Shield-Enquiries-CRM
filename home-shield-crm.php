<?php
/**
 * Plugin Name: Home Shield Enquiries CRM
 * Plugin URI: https://github.com/impact2021/Home-Shield-Enquiries-CRM
 * Description: A CRM system for managing painter enquiries with contact form and admin dashboard
 * Version: 1.0.0
 * Author: Home Shield
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: home-shield-crm
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('HS_CRM_VERSION', '1.0.0');
define('HS_CRM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('HS_CRM_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once HS_CRM_PLUGIN_DIR . 'includes/class-hs-crm-database.php';
require_once HS_CRM_PLUGIN_DIR . 'includes/class-hs-crm-form.php';
require_once HS_CRM_PLUGIN_DIR . 'includes/class-hs-crm-admin.php';
require_once HS_CRM_PLUGIN_DIR . 'includes/class-hs-crm-email.php';
require_once HS_CRM_PLUGIN_DIR . 'includes/class-hs-crm-settings.php';

/**
 * Activation hook - Create database tables
 */
function hs_crm_activate() {
    HS_CRM_Database::create_tables();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'hs_crm_activate');

/**
 * Deactivation hook
 */
function hs_crm_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'hs_crm_deactivate');

/**
 * Initialize plugin
 */
function hs_crm_init() {
    // Initialize form shortcode
    $form = new HS_CRM_Form();
    
    // Initialize admin interface
    if (is_admin()) {
        $admin = new HS_CRM_Admin();
        $settings = new HS_CRM_Settings();
    }
    
    // Initialize email handler
    $email = new HS_CRM_Email();
}
add_action('plugins_loaded', 'hs_crm_init');

/**
 * Enqueue styles and scripts
 */
function hs_crm_enqueue_assets() {
    wp_enqueue_style('hs-crm-styles', HS_CRM_PLUGIN_URL . 'assets/css/styles.css', array(), HS_CRM_VERSION);
    
    // Enqueue Google Places API
    $google_api_key = get_option('hs_crm_google_api_key', '');
    if (!empty($google_api_key)) {
        wp_enqueue_script('google-maps-places', 'https://maps.googleapis.com/maps/api/js?key=' . esc_attr($google_api_key) . '&libraries=places', array(), null, false);
    }
    
    wp_enqueue_script('hs-crm-scripts', HS_CRM_PLUGIN_URL . 'assets/js/scripts.js', array('jquery'), HS_CRM_VERSION, true);
    
    // Localize script for AJAX
    wp_localize_script('hs-crm-scripts', 'hsCrmAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('hs_crm_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'hs_crm_enqueue_assets');
add_action('admin_enqueue_scripts', 'hs_crm_enqueue_assets');
