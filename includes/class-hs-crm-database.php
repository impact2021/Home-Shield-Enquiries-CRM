<?php
/**
 * Database operations class
 */

if (!defined('ABSPATH')) {
    exit;
}

class HS_CRM_Database {
    
    /**
     * Create database tables on plugin activation
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'hs_enquiries';
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            first_name varchar(255) NOT NULL,
            last_name varchar(255) NOT NULL,
            name varchar(255) NOT NULL DEFAULT '',
            email varchar(255) NOT NULL,
            phone varchar(50) NOT NULL,
            address text NOT NULL,
            job_type varchar(100) NOT NULL,
            status varchar(50) DEFAULT 'Not Actioned' NOT NULL,
            email_sent tinyint(1) DEFAULT 0 NOT NULL,
            first_email_sent_at datetime DEFAULT NULL,
            admin_notes text DEFAULT '' NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Create notes table
        $notes_table = $wpdb->prefix . 'hs_enquiry_notes';
        $sql_notes = "CREATE TABLE IF NOT EXISTS $notes_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            enquiry_id mediumint(9) NOT NULL,
            note text NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY enquiry_id (enquiry_id)
        ) $charset_collate;";
        
        dbDelta($sql_notes);
    }
    
    /**
     * Insert new enquiry
     */
    public static function insert_enquiry($data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hs_enquiries';
        
        $first_name = sanitize_text_field($data['first_name']);
        $last_name = sanitize_text_field($data['last_name']);
        $full_name = trim($first_name . ' ' . $last_name);
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'first_name' => $first_name,
                'last_name' => $last_name,
                'name' => $full_name,
                'email' => sanitize_email($data['email']),
                'phone' => sanitize_text_field($data['phone']),
                'address' => sanitize_textarea_field($data['address']),
                'job_type' => '',
                'status' => 'Not Actioned',
                'email_sent' => 0,
                'admin_notes' => ''
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s')
        );
        
        return $result !== false ? $wpdb->insert_id : false;
    }
    
    /**
     * Get all enquiries with optional status filter
     */
    public static function get_enquiries($status = null) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hs_enquiries';
        
        if ($status === 'active') {
            // Active leads: Not Actioned, Emailed, or Quoted
            $sql = "SELECT * FROM $table_name WHERE status IN ('Not Actioned', 'Emailed', 'Quoted') ORDER BY created_at DESC";
        } elseif ($status === 'Archived') {
            // Show both "Archived" and "Dead" (for backward compatibility)
            $sql = "SELECT * FROM $table_name WHERE status IN ('Archived', 'Dead') ORDER BY created_at DESC";
        } elseif ($status && $status !== 'all') {
            $sql = $wpdb->prepare(
                "SELECT * FROM $table_name WHERE status = %s ORDER BY created_at DESC",
                $status
            );
        } else {
            $sql = "SELECT * FROM $table_name ORDER BY created_at DESC";
        }
        
        return $wpdb->get_results($sql);
    }
    
    /**
     * Get single enquiry by ID
     */
    public static function get_enquiry($id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hs_enquiries';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $id
        ));
    }
    
    /**
     * Update enquiry status
     */
    public static function update_status($id, $status) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hs_enquiries';
        
        $result = $wpdb->update(
            $table_name,
            array('status' => sanitize_text_field($status)),
            array('id' => $id),
            array('%s'),
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Get count by status
     */
    public static function get_status_counts() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hs_enquiries';
        
        $results = $wpdb->get_results(
            "SELECT status, COUNT(*) as count FROM $table_name GROUP BY status"
        );
        
        $counts = array(
            'all' => 0,
            'active' => 0,
            'Not Actioned' => 0,
            'Emailed' => 0,
            'Quoted' => 0,
            'Completed' => 0,
            'Archived' => 0
        );
        
        foreach ($results as $row) {
            // Map old "Dead" status to "Archived" for display
            if ($row->status === 'Dead') {
                $counts['Archived'] += $row->count;
            } else {
                if (!isset($counts[$row->status])) {
                    $counts[$row->status] = 0;
                }
                $counts[$row->status] += $row->count;
            }
            $counts['all'] += $row->count;
            
            // Count active leads (Not Actioned, Emailed, Quoted)
            if (in_array($row->status, array('Not Actioned', 'Emailed', 'Quoted'))) {
                $counts['active'] += $row->count;
            }
        }
        
        return $counts;
    }
    
    /**
     * Update admin notes
     */
    public static function update_admin_notes($id, $notes) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hs_enquiries';
        
        $result = $wpdb->update(
            $table_name,
            array('admin_notes' => sanitize_textarea_field($notes)),
            array('id' => $id),
            array('%s'),
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Mark email as sent
     */
    public static function mark_email_sent($id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hs_enquiries';
        
        // Check if this is the first email being sent
        $enquiry = self::get_enquiry($id);
        $update_data = array('email_sent' => 1);
        $update_format = array('%d');
        
        // Only set first_email_sent_at if it's null (first email)
        if ($enquiry && is_null($enquiry->first_email_sent_at)) {
            $update_data['first_email_sent_at'] = current_time('mysql');
            $update_format[] = '%s';
        }
        
        $result = $wpdb->update(
            $table_name,
            $update_data,
            array('id' => $id),
            $update_format,
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Add a note to an enquiry
     */
    public static function add_note($enquiry_id, $note) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hs_enquiry_notes';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'enquiry_id' => $enquiry_id,
                'note' => sanitize_textarea_field($note)
            ),
            array('%d', '%s')
        );
        
        return $result !== false ? $wpdb->insert_id : false;
    }
    
    /**
     * Get all notes for an enquiry
     */
    public static function get_notes($enquiry_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hs_enquiry_notes';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE enquiry_id = %d ORDER BY created_at ASC",
            $enquiry_id
        ));
    }
    
    /**
     * Delete a note
     */
    public static function delete_note($note_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hs_enquiry_notes';
        
        $result = $wpdb->delete(
            $table_name,
            array('id' => $note_id),
            array('%d')
        );
        
        return $result !== false;
    }
}
