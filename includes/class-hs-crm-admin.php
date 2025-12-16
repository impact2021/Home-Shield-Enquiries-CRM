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
        add_action('wp_ajax_hs_crm_save_notes', array($this, 'ajax_save_notes'));
        add_action('wp_ajax_hs_crm_get_enquiry', array($this, 'ajax_get_enquiry'));
        add_action('wp_ajax_hs_crm_add_note', array($this, 'ajax_add_note'));
        add_action('wp_ajax_hs_crm_delete_note', array($this, 'ajax_delete_note'));
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
        
        $current_status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'active';
        $enquiries = HS_CRM_Database::get_enquiries($current_status === 'all' ? null : $current_status);
        $counts = HS_CRM_Database::get_status_counts();
        
        ?>
        <div class="wrap hs-crm-admin-wrap">
            <h1>Home Shield Enquiries</h1>
            
            <div class="hs-crm-tabs">
                <a href="?page=hs-crm-enquiries&status=active" 
                   class="hs-crm-tab <?php echo $current_status === 'active' ? 'active' : ''; ?>">
                    Active leads (<?php echo $counts['active']; ?>)
                </a>
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
                <a href="?page=hs-crm-enquiries&status=Archived" 
                   class="hs-crm-tab <?php echo $current_status === 'Archived' ? 'active' : ''; ?>">
                    Archived (<?php echo $counts['Archived']; ?>)
                </a>
            </div>
            
            <?php if (empty($enquiries)): ?>
                <div class="hs-crm-no-enquiries">
                    <p style="text-align: center; padding: 20px;">No enquiries found.</p>
                </div>
            <?php else: ?>
                <?php 
                $row_index = 0;
                foreach ($enquiries as $enquiry): 
                    $notes = HS_CRM_Database::get_notes($enquiry->id);
                    $row_class = ($row_index % 2 === 0) ? 'hs-crm-even-row' : 'hs-crm-odd-row';
                    $row_index++;
                ?>
                    <!-- Individual table for each enquiry -->
                    <table class="wp-list-table widefat fixed hs-crm-enquiries-table hs-crm-single-enquiry-table">
                        <tbody>
                            <!-- Customer Header Row -->
                            <tr class="hs-crm-customer-header-row <?php echo $row_class; ?>">
                                <th style="width: 10%;">Created</th>
                                <th style="width: 18%;">Contact Info</th>
                                <th style="width: 20%;">Address</th>
                                <th style="width: 10%;">Status</th>
                                <th style="width: 18%;">Status Change</th>
                                <th style="width: 23%;">Action</th>
                            </tr>
                            <tr class="hs-crm-enquiry-row <?php echo $row_class; ?>" data-enquiry-id="<?php echo esc_attr($enquiry->id); ?>">
                                <td><?php echo esc_html(hs_crm_format_date($enquiry->created_at)); ?></td>
                                <td>
                                    <strong><?php echo esc_html($enquiry->first_name . ' ' . $enquiry->last_name); ?></strong><br>
                                    <small style="color: #666;"><?php echo esc_html($enquiry->email); ?></small><br>
                                    <small style="color: #666;"><?php echo esc_html($enquiry->phone); ?></small>
                                </td>
                                <td><?php echo esc_html($enquiry->address); ?></td>
                                <td>
                                    <span class="hs-crm-status-badge status-<?php echo esc_attr(strtolower(str_replace(' ', '-', $enquiry->status))); ?>">
                                        <?php echo esc_html($enquiry->status); ?>
                                    </span>
                                </td>
                                <td>
                                    <select class="hs-crm-status-select" data-enquiry-id="<?php echo esc_attr($enquiry->id); ?>" data-current-status="<?php echo esc_attr($enquiry->status); ?>">
                                        <option value="">Change Status...</option>
                                        <option value="Not Actioned">Not Actioned</option>
                                        <option value="Emailed">Emailed</option>
                                        <option value="Quoted">Quoted</option>
                                        <option value="Completed">Completed</option>
                                        <option value="Archived">Archived</option>
                                    </select>
                                </td>
                                <td>
                                    <select class="hs-crm-action-select" data-enquiry-id="<?php echo esc_attr($enquiry->id); ?>">
                                        <option value="">Select Action...</option>
                                        <option value="send_quote">Send Quote</option>
                                        <option value="send_invoice">Send Invoice</option>
                                        <option value="send_receipt">Send Receipt</option>
                                    </select>
                                </td>
                            </tr>
                            
                            <!-- Notes rows -->
                            <?php if (!empty($notes)): ?>
                                <?php foreach ($notes as $note): ?>
                                    <tr class="hs-crm-note-row <?php echo $row_class; ?>" data-note-id="<?php echo esc_attr($note->id); ?>" data-enquiry-id="<?php echo esc_attr($enquiry->id); ?>">
                                        <td class="hs-crm-note-date">
                                            <?php echo esc_html(hs_crm_format_date($note->created_at)); ?>
                                        </td>
                                        <td colspan="3" class="hs-crm-note-content">
                                            <div class="hs-crm-note-text"><?php echo esc_html(stripslashes($note->note)); ?></div>
                                        </td>
                                        <td colspan="2" class="hs-crm-note-actions">
                                            <button type="button" class="button button-small hs-crm-delete-note" data-note-id="<?php echo esc_attr($note->id); ?>">Delete</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            
                            <!-- Add note row -->
                            <tr class="hs-crm-add-note-row <?php echo $row_class; ?>" data-enquiry-id="<?php echo esc_attr($enquiry->id); ?>">
                                <td colspan="5">
                                    <textarea class="hs-crm-new-note" data-enquiry-id="<?php echo esc_attr($enquiry->id); ?>" rows="2" placeholder="Add a new note..."></textarea>
                                </td>
                                <td>
                                    <button type="button" class="button button-small hs-crm-add-note" data-enquiry-id="<?php echo esc_attr($enquiry->id); ?>">Add Note</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Email Modal -->
        <div id="hs-crm-email-modal" class="hs-crm-modal" style="display: none;">
            <div class="hs-crm-modal-content">
                <span class="hs-crm-modal-close">&times;</span>
                <h2 id="email-modal-title">Send Email</h2>
                <form id="hs-crm-email-form">
                    <input type="hidden" id="email-enquiry-id" name="enquiry_id">
                    <input type="hidden" id="email-type" name="email_type">
                    
                    <div class="hs-crm-form-group">
                        <label for="email-to">To:</label>
                        <input type="email" id="email-to" name="email_to" readonly>
                    </div>
                    
                    <div class="hs-crm-form-group">
                        <label for="email-customer">Customer:</label>
                        <input type="text" id="email-customer" name="email_customer" readonly>
                    </div>
                    
                    <div class="hs-crm-form-group">
                        <label for="email-subject">Subject:</label>
                        <input type="text" id="email-subject" name="subject">
                    </div>
                    
                    <div class="hs-crm-form-group">
                        <label for="email-message">Message:</label>
                        <textarea id="email-message" name="message" rows="5"></textarea>
                    </div>
                    
                    <input type="hidden" id="email-customer-name" name="customer_first_name">
                    
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
            // Add automatic note for status change
            if (!empty($old_status)) {
                $note_text = sprintf('Status changed from "%s" to "%s"', $old_status, $new_status);
            } else {
                $note_text = sprintf('Status changed to "%s"', $new_status);
            }
            $note_id = HS_CRM_Database::add_note($enquiry_id, $note_text);
            
            // Get current time formatted with plugin timezone
            $formatted_date = hs_crm_current_time_formatted('d/m/Y H:i');
            
            wp_send_json_success(array(
                'message' => 'Status updated successfully.',
                'note' => array(
                    'id' => $note_id,
                    'text' => $note_text,
                    'created_at' => current_time('mysql'),
                    'formatted_date' => $formatted_date
                )
            ));
        } else {
            wp_send_json_error(array('message' => 'Failed to update status.'));
        }
    }
    
    /**
     * AJAX handler for saving notes
     */
    public function ajax_save_notes() {
        check_ajax_referer('hs_crm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied.'));
        }
        
        $enquiry_id = isset($_POST['enquiry_id']) ? intval($_POST['enquiry_id']) : 0;
        $notes = isset($_POST['notes']) ? sanitize_textarea_field($_POST['notes']) : '';
        
        if (!$enquiry_id) {
            wp_send_json_error(array('message' => 'Invalid enquiry ID.'));
        }
        
        $result = HS_CRM_Database::update_admin_notes($enquiry_id, $notes);
        
        if ($result) {
            wp_send_json_success(array('message' => 'Notes saved successfully.'));
        } else {
            wp_send_json_error(array('message' => 'Failed to save notes.'));
        }
    }
    
    /**
     * AJAX handler for getting enquiry data
     */
    public function ajax_get_enquiry() {
        check_ajax_referer('hs_crm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied.'));
        }
        
        $enquiry_id = isset($_POST['enquiry_id']) ? intval($_POST['enquiry_id']) : 0;
        
        if (!$enquiry_id) {
            wp_send_json_error(array('message' => 'Invalid enquiry ID.'));
        }
        
        $enquiry = HS_CRM_Database::get_enquiry($enquiry_id);
        
        if ($enquiry) {
            wp_send_json_success(array('enquiry' => $enquiry));
        } else {
            wp_send_json_error(array('message' => 'Enquiry not found.'));
        }
    }
    
    /**
     * AJAX handler for adding a note
     */
    public function ajax_add_note() {
        check_ajax_referer('hs_crm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied.'));
        }
        
        $enquiry_id = isset($_POST['enquiry_id']) ? intval($_POST['enquiry_id']) : 0;
        $note = isset($_POST['note']) ? sanitize_textarea_field($_POST['note']) : '';
        
        if (!$enquiry_id || empty($note)) {
            wp_send_json_error(array('message' => 'Invalid data.'));
        }
        
        $note_id = HS_CRM_Database::add_note($enquiry_id, $note);
        
        if ($note_id) {
            // Get current time formatted with plugin timezone
            $formatted_date = hs_crm_current_time_formatted('d/m/Y H:i');
            
            wp_send_json_success(array(
                'message' => 'Note added successfully.',
                'note_id' => $note_id,
                'created_at' => current_time('mysql'),
                'formatted_date' => $formatted_date
            ));
        } else {
            wp_send_json_error(array('message' => 'Failed to add note.'));
        }
    }
    
    /**
     * AJAX handler for deleting a note
     */
    public function ajax_delete_note() {
        check_ajax_referer('hs_crm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied.'));
        }
        
        $note_id = isset($_POST['note_id']) ? intval($_POST['note_id']) : 0;
        
        if (!$note_id) {
            wp_send_json_error(array('message' => 'Invalid note ID.'));
        }
        
        // Verify the note exists before attempting to delete
        global $wpdb;
        $notes_table = $wpdb->prefix . 'hs_enquiry_notes';
        $note = $wpdb->get_row($wpdb->prepare("SELECT * FROM $notes_table WHERE id = %d", $note_id));
        
        if (!$note) {
            wp_send_json_error(array('message' => 'Note not found.'));
        }
        
        $result = HS_CRM_Database::delete_note($note_id);
        
        if ($result) {
            wp_send_json_success(array('message' => 'Note deleted successfully.'));
        } else {
            wp_send_json_error(array('message' => 'Failed to delete note.'));
        }
    }
}
