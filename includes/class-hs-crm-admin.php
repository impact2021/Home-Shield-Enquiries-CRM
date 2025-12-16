<?php
/**
 * Admin interface class
 */

if (!defined('ABSPATH')) {
    exit;
}

class HS_CRM_Admin {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_ajax_hs_crm_update_status', array($this, 'ajax_update_status'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'Home Shield Enquiries',
            'HS Enquiries',
            'manage_options',
            'hs-crm-enquiries',
            array($this, 'render_admin_page'),
            'dashicons-email',
            26
        );
    }
    
    /**
     * Render admin page
     */
    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $current_status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'all';
        $enquiries = HS_CRM_Database::get_enquiries($current_status === 'all' ? null : $current_status);
        $counts = HS_CRM_Database::get_status_counts();
        $job_types = HS_CRM_Form::get_job_types();
        
        ?>
        <div class="wrap hs-crm-admin-wrap">
            <h1>Home Shield Enquiries</h1>
            
            <div class="hs-crm-tabs">
                <a href="?page=hs-crm-enquiries&status=all" 
                   class="hs-crm-tab <?php echo $current_status === 'all' ? 'active' : ''; ?>">
                    All (<?php echo $counts['all']; ?>)
                </a>
                <a href="?page=hs-crm-enquiries&status=Not Actioned" 
                   class="hs-crm-tab <?php echo $current_status === 'Not Actioned' ? 'active' : ''; ?>">
                    Not Actioned (<?php echo $counts['Not Actioned']; ?>)
                </a>
                <a href="?page=hs-crm-enquiries&status=Emailed" 
                   class="hs-crm-tab <?php echo $current_status === 'Emailed' ? 'active' : ''; ?>">
                    Emailed (<?php echo $counts['Emailed']; ?>)
                </a>
                <a href="?page=hs-crm-enquiries&status=Quoted" 
                   class="hs-crm-tab <?php echo $current_status === 'Quoted' ? 'active' : ''; ?>">
                    Quoted (<?php echo $counts['Quoted']; ?>)
                </a>
                <a href="?page=hs-crm-enquiries&status=Completed" 
                   class="hs-crm-tab <?php echo $current_status === 'Completed' ? 'active' : ''; ?>">
                    Completed (<?php echo $counts['Completed']; ?>)
                </a>
                <a href="?page=hs-crm-enquiries&status=Dead" 
                   class="hs-crm-tab <?php echo $current_status === 'Dead' ? 'active' : ''; ?>">
                    Dead (<?php echo $counts['Dead']; ?>)
                </a>
            </div>
            
            <div class="hs-crm-table-container">
                <table class="wp-list-table widefat fixed striped hs-crm-enquiries-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Job Type</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($enquiries)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center;">No enquiries found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($enquiries as $enquiry): ?>
                                <tr data-enquiry-id="<?php echo esc_attr($enquiry->id); ?>">
                                    <td><?php echo esc_html($enquiry->id); ?></td>
                                    <td><?php echo esc_html($enquiry->name); ?></td>
                                    <td><?php echo esc_html($enquiry->phone); ?></td>
                                    <td><?php echo esc_html($enquiry->address); ?></td>
                                    <td><?php echo esc_html($job_types[$enquiry->job_type] ?? $enquiry->job_type); ?></td>
                                    <td>
                                        <span class="hs-crm-status-badge status-<?php echo esc_attr(strtolower(str_replace(' ', '-', $enquiry->status))); ?>">
                                            <?php echo esc_html($enquiry->status); ?>
                                        </span>
                                    </td>
                                    <td><?php echo esc_html(date('Y-m-d H:i', strtotime($enquiry->created_at))); ?></td>
                                    <td>
                                        <select class="hs-crm-status-select" data-enquiry-id="<?php echo esc_attr($enquiry->id); ?>" data-current-status="<?php echo esc_attr($enquiry->status); ?>">
                                            <option value="">Change Status...</option>
                                            <option value="Not Actioned">Not Actioned</option>
                                            <option value="Emailed">Emailed</option>
                                            <option value="Quoted">Quoted</option>
                                            <option value="Completed">Completed</option>
                                            <option value="Dead">Dead</option>
                                        </select>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Email Modal -->
        <div id="hs-crm-email-modal" class="hs-crm-modal" style="display: none;">
            <div class="hs-crm-modal-content">
                <span class="hs-crm-modal-close">&times;</span>
                <h2>Send Quote Email</h2>
                <form id="hs-crm-email-form">
                    <input type="hidden" id="email-enquiry-id" name="enquiry_id">
                    
                    <div class="hs-crm-form-group">
                        <label for="email-to">To:</label>
                        <input type="text" id="email-to" name="email_to" readonly>
                    </div>
                    
                    <div class="hs-crm-form-group">
                        <label for="email-subject">Subject:</label>
                        <input type="text" id="email-subject" name="subject" value="Quote for Painting Services">
                    </div>
                    
                    <div class="hs-crm-form-group">
                        <label for="email-message">Message:</label>
                        <textarea id="email-message" name="message" rows="5">Dear Customer,

Thank you for your enquiry. Please find our quote below:</textarea>
                    </div>
                    
                    <div class="hs-crm-form-group">
                        <label>Quote Items:</label>
                        <div id="quote-table-container">
                            <table id="quote-items-table" class="hs-crm-quote-table">
                                <thead>
                                    <tr>
                                        <th style="width: 50%;">Description of Work</th>
                                        <th style="width: 20%;">Cost (ex GST)</th>
                                        <th style="width: 20%;">GST (15%)</th>
                                        <th style="width: 10%;"></th>
                                    </tr>
                                </thead>
                                <tbody id="quote-items-body">
                                    <tr class="quote-item-row">
                                        <td><input type="text" class="quote-description" placeholder="e.g., Interior wall painting"></td>
                                        <td><input type="number" class="quote-cost" placeholder="0.00" step="0.01" min="0"></td>
                                        <td class="quote-gst">$0.00</td>
                                        <td><button type="button" class="remove-quote-item button">×</button></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4">
                                            <button type="button" id="add-quote-item" class="button">+ Add Item</button>
                                        </td>
                                    </tr>
                                    <tr class="quote-totals">
                                        <td colspan="1" style="text-align: right;"><strong>Subtotal (ex GST):</strong></td>
                                        <td id="quote-subtotal">$0.00</td>
                                        <td colspan="2"></td>
                                    </tr>
                                    <tr class="quote-totals">
                                        <td colspan="1" style="text-align: right;"><strong>Total GST:</strong></td>
                                        <td id="quote-total-gst">$0.00</td>
                                        <td colspan="2"></td>
                                    </tr>
                                    <tr class="quote-totals">
                                        <td colspan="1" style="text-align: right;"><strong>Total (inc GST):</strong></td>
                                        <td id="quote-total"><strong>$0.00</strong></td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    
                    <div class="hs-crm-form-group">
                        <button type="submit" class="button button-primary">Send Email</button>
                        <button type="button" class="button hs-crm-modal-close">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }
    
    /**
     * AJAX handler for status update
     */
    public function ajax_update_status() {
        check_ajax_referer('hs_crm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied.'));
        }
        
        $enquiry_id = isset($_POST['enquiry_id']) ? intval($_POST['enquiry_id']) : 0;
        $new_status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
        $old_status = isset($_POST['old_status']) ? sanitize_text_field($_POST['old_status']) : '';
        
        if (!$enquiry_id || !$new_status) {
            wp_send_json_error(array('message' => 'Invalid data.'));
        }
        
        // Update status
        $result = HS_CRM_Database::update_status($enquiry_id, $new_status);
        
        if ($result) {
            // Check if we need to trigger email (status change to Emailed, Quoted, or Completed)
            $trigger_email = in_array($new_status, array('Emailed', 'Quoted', 'Completed'));
            
            if ($trigger_email) {
                $enquiry = HS_CRM_Database::get_enquiry($enquiry_id);
                wp_send_json_success(array(
                    'message' => 'Status updated successfully.',
                    'trigger_email' => true,
                    'enquiry' => $enquiry
                ));
            } else {
                wp_send_json_success(array(
                    'message' => 'Status updated successfully.',
                    'trigger_email' => false
                ));
            }
        } else {
            wp_send_json_error(array('message' => 'Failed to update status.'));
        }
    }
}
