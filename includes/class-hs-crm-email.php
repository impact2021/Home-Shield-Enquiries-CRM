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
        $email_type = isset($_POST['email_type']) ? sanitize_text_field($_POST['email_type']) : 'send_quote';
        
        if (!$enquiry_id) {
            wp_send_json_error(array('message' => 'Invalid enquiry ID.'));
        }
        
        $enquiry = HS_CRM_Database::get_enquiry($enquiry_id);
        
        if (!$enquiry) {
            wp_send_json_error(array('message' => 'Enquiry not found.'));
        }
        
        // Build quote/invoice/receipt table HTML
        $quote_html = $this->build_quote_table($quote_items, $email_type);
        
        // Build full email content
        $email_content = $this->build_email_content($message, $quote_html, $enquiry, $email_type);
        
        // Send to customer email
        $to = $enquiry->email;
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        $result = wp_mail($to, $subject, $email_content, $headers);
        
        if ($result) {
            // Mark email as sent
            HS_CRM_Database::mark_email_sent($enquiry_id);
            
            // Add automatic note for email sent
            $email_type_label = '';
            switch($email_type) {
                case 'send_quote':
                    $email_type_label = 'Quote';
                    break;
                case 'send_invoice':
                    $email_type_label = 'Invoice';
                    break;
                case 'send_receipt':
                    $email_type_label = 'Receipt';
                    break;
                default:
                    $email_type_label = 'Email';
            }
            $note_text = sprintf('%s sent to %s', $email_type_label, $enquiry->email);
            HS_CRM_Database::add_note($enquiry_id, $note_text);
            
            wp_send_json_success(array('message' => 'Email sent successfully.'));
        } else {
            wp_send_json_error(array('message' => 'Failed to send email.'));
        }
    }
    
    /**
     * Build quote table HTML
     */
    private function build_quote_table($quote_items, $email_type = 'send_quote') {
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
    private function build_email_content($message, $quote_html, $enquiry, $email_type = 'send_quote') {
        // Determine section title based on email type
        $section_title = 'Quote Details';
        switch($email_type) {
            case 'send_invoice':
                $section_title = 'Invoice Details';
                break;
            case 'send_receipt':
                $section_title = 'Receipt Details';
                break;
        }
        
        $html = '<!DOCTYPE html>';
        $html .= '<html>';
        $html .= '<head><meta charset="UTF-8"></head>';
        $html .= '<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">';
        
        // Header
        $html .= '<h1>Home Shield Painters</h1>';
        
        // Message content
        $html .= '<p>' . nl2br(esc_html($message)) . '</p>';
        
        // Quote/Invoice/Receipt table - ensure it's always included if provided
        if (!empty($quote_html)) {
            $html .= '<h2>' . esc_html($section_title) . '</h2>';
            $html .= $quote_html;
        }
        
        // Job details
        $html .= '<h3>Job Details</h3>';
        $html .= '<p><strong>Name:</strong> ' . esc_html($enquiry->first_name . ' ' . $enquiry->last_name) . '</p>';
        $html .= '<p><strong>Email:</strong> ' . esc_html($enquiry->email) . '</p>';
        $html .= '<p><strong>Address:</strong> ' . esc_html($enquiry->address) . '</p>';
        $html .= '<p><strong>Phone:</strong> ' . esc_html($enquiry->phone) . '</p>';
        
        // Footer
        $html .= '<p style="font-size: 12px; color: #666;">Thank you for choosing Home Shield Painters</p>';
        $html .= '<p style="font-size: 12px; color: #666;">This is an automated email. Please do not reply directly to this message.</p>';
        
        $html .= '</body>';
        $html .= '</html>';
        
        return $html;
    }
}
