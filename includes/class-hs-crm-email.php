<?php
/**
 * Email handling class
 */

if (!defined('ABSPATH')) {
    exit;
}

class HS_CRM_Email {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_ajax_hs_crm_send_email', array($this, 'ajax_send_email'));
    }
    
    /**
     * AJAX handler for sending email
     */
    public function ajax_send_email() {
        check_ajax_referer('hs_crm_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied.'));
        }
        
        $enquiry_id = isset($_POST['enquiry_id']) ? intval($_POST['enquiry_id']) : 0;
        $subject = isset($_POST['subject']) ? sanitize_text_field($_POST['subject']) : '';
        $message = isset($_POST['message']) ? wp_kses_post($_POST['message']) : '';
        $quote_items = isset($_POST['quote_items']) ? $_POST['quote_items'] : array();
        
        if (!$enquiry_id) {
            wp_send_json_error(array('message' => 'Invalid enquiry ID.'));
        }
        
        $enquiry = HS_CRM_Database::get_enquiry($enquiry_id);
        
        if (!$enquiry) {
            wp_send_json_error(array('message' => 'Enquiry not found.'));
        }
        
        // Build quote table HTML
        $quote_html = $this->build_quote_table($quote_items);
        
        // Build full email content
        $email_content = $this->build_email_content($message, $quote_html, $enquiry);
        
        // In a real implementation, you would extract email from the enquiry
        // For now, we'll use the admin email or a custom field
        $to = get_option('admin_email'); // In production, this should be the customer's email
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        $result = wp_mail($to, $subject, $email_content, $headers);
        
        if ($result) {
            wp_send_json_success(array('message' => 'Email sent successfully.'));
        } else {
            wp_send_json_error(array('message' => 'Failed to send email.'));
        }
    }
    
    /**
     * Build quote table HTML
     */
    private function build_quote_table($quote_items) {
        if (empty($quote_items)) {
            return '';
        }
        
        $subtotal = 0;
        $total_gst = 0;
        
        $html = '<table style="width: 100%; border-collapse: collapse; margin: 20px 0;">';
        $html .= '<thead>';
        $html .= '<tr style="background-color: #f5f5f5;">';
        $html .= '<th style="border: 1px solid #ddd; padding: 12px; text-align: left;">Description of Work</th>';
        $html .= '<th style="border: 1px solid #ddd; padding: 12px; text-align: right;">Cost (ex GST)</th>';
        $html .= '<th style="border: 1px solid #ddd; padding: 12px; text-align: right;">GST (15%)</th>';
        $html .= '<th style="border: 1px solid #ddd; padding: 12px; text-align: right;">Total (inc GST)</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';
        
        foreach ($quote_items as $item) {
            if (empty($item['description']) || empty($item['cost'])) {
                continue;
            }
            
            $cost = floatval($item['cost']);
            $gst = $cost * 0.15;
            $total = $cost + $gst;
            
            $subtotal += $cost;
            $total_gst += $gst;
            
            $html .= '<tr>';
            $html .= '<td style="border: 1px solid #ddd; padding: 12px;">' . esc_html($item['description']) . '</td>';
            $html .= '<td style="border: 1px solid #ddd; padding: 12px; text-align: right;">$' . number_format($cost, 2) . '</td>';
            $html .= '<td style="border: 1px solid #ddd; padding: 12px; text-align: right;">$' . number_format($gst, 2) . '</td>';
            $html .= '<td style="border: 1px solid #ddd; padding: 12px; text-align: right;">$' . number_format($total, 2) . '</td>';
            $html .= '</tr>';
        }
        
        $grand_total = $subtotal + $total_gst;
        
        $html .= '</tbody>';
        $html .= '<tfoot>';
        $html .= '<tr style="background-color: #f9f9f9;">';
        $html .= '<td colspan="3" style="border: 1px solid #ddd; padding: 12px; text-align: right;"><strong>Subtotal (ex GST):</strong></td>';
        $html .= '<td style="border: 1px solid #ddd; padding: 12px; text-align: right;"><strong>$' . number_format($subtotal, 2) . '</strong></td>';
        $html .= '</tr>';
        $html .= '<tr style="background-color: #f9f9f9;">';
        $html .= '<td colspan="3" style="border: 1px solid #ddd; padding: 12px; text-align: right;"><strong>Total GST:</strong></td>';
        $html .= '<td style="border: 1px solid #ddd; padding: 12px; text-align: right;"><strong>$' . number_format($total_gst, 2) . '</strong></td>';
        $html .= '</tr>';
        $html .= '<tr style="background-color: #e8f4f8;">';
        $html .= '<td colspan="3" style="border: 1px solid #ddd; padding: 12px; text-align: right;"><strong>Total (inc GST):</strong></td>';
        $html .= '<td style="border: 1px solid #ddd; padding: 12px; text-align: right;"><strong>$' . number_format($grand_total, 2) . '</strong></td>';
        $html .= '</tr>';
        $html .= '</tfoot>';
        $html .= '</table>';
        
        return $html;
    }
    
    /**
     * Build complete email content
     */
    private function build_email_content($message, $quote_html, $enquiry) {
        $html = '<!DOCTYPE html>';
        $html .= '<html>';
        $html .= '<head><meta charset="UTF-8"></head>';
        $html .= '<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">';
        $html .= '<div style="max-width: 600px; margin: 0 auto; padding: 20px;">';
        
        // Header
        $html .= '<div style="background-color: #0073aa; color: white; padding: 20px; text-align: center;">';
        $html .= '<h1 style="margin: 0;">Home Shield Painters</h1>';
        $html .= '</div>';
        
        // Message content
        $html .= '<div style="padding: 20px; background-color: #f9f9f9;">';
        $html .= '<p>' . nl2br(esc_html($message)) . '</p>';
        $html .= '</div>';
        
        // Quote table
        if ($quote_html) {
            $html .= '<div style="padding: 20px;">';
            $html .= '<h2>Quote Details</h2>';
            $html .= $quote_html;
            $html .= '</div>';
        }
        
        // Job details
        $html .= '<div style="padding: 20px; background-color: #f9f9f9; margin-top: 20px;">';
        $html .= '<h3>Job Details</h3>';
        $html .= '<p><strong>Name:</strong> ' . esc_html($enquiry->name) . '</p>';
        $html .= '<p><strong>Address:</strong> ' . esc_html($enquiry->address) . '</p>';
        $html .= '<p><strong>Phone:</strong> ' . esc_html($enquiry->phone) . '</p>';
        $html .= '<p><strong>Job Type:</strong> ' . esc_html($enquiry->job_type) . '</p>';
        $html .= '</div>';
        
        // Footer
        $html .= '<div style="padding: 20px; text-align: center; font-size: 12px; color: #666;">';
        $html .= '<p>Thank you for choosing Home Shield Painters</p>';
        $html .= '<p>This is an automated email. Please do not reply directly to this message.</p>';
        $html .= '</div>';
        
        $html .= '</div>';
        $html .= '</body>';
        $html .= '</html>';
        
        return $html;
    }
}
