<?php
/**
 * Settings page class
 */

if (!defined('ABSPATH')) {
    exit;
}

class HS_CRM_Settings {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    /**
     * Add settings page to admin menu
     */
    public function add_settings_page() {
        add_submenu_page(
            'hs-crm-enquiries',
            'Settings',
            'Settings',
            'manage_options',
            'hs-crm-settings',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting('hs_crm_settings', 'hs_crm_google_api_key');
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Save settings message
        if (isset($_GET['settings-updated'])) {
            add_settings_error(
                'hs_crm_messages',
                'hs_crm_message',
                'Settings saved successfully.',
                'updated'
            );
        }
        
        settings_errors('hs_crm_messages');
        ?>
        <div class="wrap">
            <h1>Home Shield CRM Settings</h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('hs_crm_settings');
                do_settings_sections('hs_crm_settings');
                ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="hs_crm_google_api_key">Google Maps API Key</label>
                        </th>
                        <td>
                            <input type="text" 
                                   id="hs_crm_google_api_key" 
                                   name="hs_crm_google_api_key" 
                                   value="<?php echo esc_attr(get_option('hs_crm_google_api_key', '')); ?>" 
                                   class="regular-text">
                            <p class="description">
                                Enter your Google Maps API key to enable address autocomplete (restricted to New Zealand).<br>
                                Get your API key from: <a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">Google Maps Platform</a><br>
                                <strong>Required APIs:</strong> Places API, Maps JavaScript API
                            </p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button('Save Settings'); ?>
            </form>
            
            <hr>
            
            <h2>Shortcode Usage</h2>
            <p>Use the following shortcode to display the contact form on any page or post:</p>
            <code>[hs_contact_form]</code>
        </div>
        <?php
    }
}
