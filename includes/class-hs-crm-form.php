<?php
/**
 * Contact form class
 */

if (!defined('ABSPATH')) {
    exit;
}

class HS_CRM_Form {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_shortcode('hs_contact_form', array($this, 'render_form'));
        add_action('wp_ajax_hs_crm_submit_form', array($this, 'handle_submission'));
        add_action('wp_ajax_nopriv_hs_crm_submit_form', array($this, 'handle_submission'));
    }
    

    
    /**
     * Render contact form
     */
    public function render_form($atts) {
        ob_start();
        ?>
        <div class="hs-crm-form-container">
            <form id="hs-crm-contact-form" class="hs-crm-form" method="post">
                <div class="hs-crm-form-messages"></div>
                
                <div class="hs-crm-form-row">
                    <div class="hs-crm-form-group hs-crm-form-half">
                        <input type="text" id="hs_first_name" name="first_name" placeholder="First Name *" required>
                    </div>
                    
                    <div class="hs-crm-form-group hs-crm-form-half">
                        <input type="text" id="hs_last_name" name="last_name" placeholder="Last Name *" required>
                    </div>
                </div>
                
                <div class="hs-crm-form-row">
                    <div class="hs-crm-form-group hs-crm-form-half">
                        <input type="email" id="hs_email" name="email" placeholder="Email *" required>
                    </div>
                    
                    <div class="hs-crm-form-group hs-crm-form-half">
                        <input type="tel" id="hs_phone" name="phone" placeholder="Phone Number *" required>
                    </div>
                </div>
                
                <div class="hs-crm-form-group">
                    <input type="text" id="hs_address" name="address" placeholder="Address *" required>
                </div>
                
                <div class="hs-crm-form-group">
                    <button type="submit" class="hs-crm-submit-btn">Submit Enquiry</button>
                </div>
                
                <?php wp_nonce_field('hs_crm_form_submit', 'hs_crm_nonce'); ?>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Handle form submission via AJAX
     */
    public function handle_submission() {
        // Verify nonce
        if (!isset($_POST['hs_crm_nonce']) || !wp_verify_nonce($_POST['hs_crm_nonce'], 'hs_crm_form_submit')) {
            wp_send_json_error(array('message' => 'Security verification failed.'));
        }
        
        // Validate required fields
        $required_fields = array('first_name', 'last_name', 'email', 'phone', 'address');
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                wp_send_json_error(array('message' => 'Please fill in all required fields.'));
            }
        }
        
        // Validate email format
        if (!is_email($_POST['email'])) {
            wp_send_json_error(array('message' => 'Please enter a valid email address.'));
        }
        
        // Prepare data
        $data = array(
            'first_name' => sanitize_text_field($_POST['first_name']),
            'last_name' => sanitize_text_field($_POST['last_name']),
            'email' => sanitize_email($_POST['email']),
            'phone' => sanitize_text_field($_POST['phone']),
            'address' => sanitize_textarea_field($_POST['address'])
        );
        
        // Insert into database
        $result = HS_CRM_Database::insert_enquiry($data);
        
        if ($result) {
            wp_send_json_success(array('message' => 'Thank you! Your enquiry has been submitted successfully.'));
        } else {
            wp_send_json_error(array('message' => 'There was an error submitting your enquiry. Please try again.'));
        }
    }
}
