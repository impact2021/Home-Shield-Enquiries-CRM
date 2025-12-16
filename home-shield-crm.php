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
    // Ensure migration runs on activation
    delete_option('hs_crm_db_version');
    hs_crm_check_db_version();
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
    // Check for database updates
    hs_crm_check_db_version();
    
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
 * Check and update database if needed
 */
function hs_crm_check_db_version() {
    $db_version = get_option('hs_crm_db_version', '1.0.0');
    
    if (version_compare($db_version, '1.1.0', '<')) {
        // Run migration for version 1.1.0
        hs_crm_migrate_to_1_1_0();
        update_option('hs_crm_db_version', '1.1.0');
    }
}

/**
 * Migrate database to version 1.1.0
 * Adds first_name, last_name, email_sent, and admin_notes columns
 */
function hs_crm_migrate_to_1_1_0() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'hs_enquiries';
    
    // Check if columns exist before adding them
    $columns = $wpdb->get_results("SHOW COLUMNS FROM $table_name");
    $column_names = array();
    foreach ($columns as $column) {
        $column_names[] = $column->Field;
    }
    
    // Add first_name column if it doesn't exist
    if (!in_array('first_name', $column_names)) {
        $wpdb->query("ALTER TABLE $table_name ADD COLUMN first_name varchar(255) NOT NULL DEFAULT '' AFTER id");
    }
    
    // Add last_name column if it doesn't exist
    if (!in_array('last_name', $column_names)) {
        $wpdb->query("ALTER TABLE $table_name ADD COLUMN last_name varchar(255) NOT NULL DEFAULT '' AFTER first_name");
    }
    
    // Add email_sent column if it doesn't exist
    if (!in_array('email_sent', $column_names)) {
        $wpdb->query("ALTER TABLE $table_name ADD COLUMN email_sent tinyint(1) NOT NULL DEFAULT 0 AFTER status");
    }
    
    // Add admin_notes column if it doesn't exist
    if (!in_array('admin_notes', $column_names)) {
        $wpdb->query("ALTER TABLE $table_name ADD COLUMN admin_notes text NOT NULL DEFAULT '' AFTER email_sent");
    }
    
    // Migrate existing name data to first_name and last_name
    $wpdb->query("
        UPDATE $table_name 
        SET first_name = SUBSTRING_INDEX(name, ' ', 1),
            last_name = IF(
                SUBSTRING_INDEX(name, ' ', 1) = SUBSTRING_INDEX(name, ' ', -1),
                '',
                TRIM(SUBSTRING(name, LOCATE(' ', name) + 1))
            )
        WHERE first_name = '' AND name != ''
    ");
}

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
