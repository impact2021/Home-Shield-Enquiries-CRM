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
            name varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            phone varchar(50) NOT NULL,
            address text NOT NULL,
            job_type varchar(100) NOT NULL,
            status varchar(50) DEFAULT 'Not Actioned' NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Insert new enquiry
     */
    public static function insert_enquiry($data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hs_enquiries';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'name' => sanitize_text_field($data['name']),
                'email' => sanitize_email($data['email']),
                'phone' => sanitize_text_field($data['phone']),
                'address' => sanitize_textarea_field($data['address']),
                'job_type' => sanitize_text_field($data['job_type']),
                'status' => 'Not Actioned'
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s')
        );
        
        return $result !== false ? $wpdb->insert_id : false;
    }
    
    /**
     * Get all enquiries with optional status filter
     */
    public static function get_enquiries($status = null) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hs_enquiries';
        
        if ($status && $status !== 'all') {
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
            'Not Actioned' => 0,
            'Emailed' => 0,
            'Quoted' => 0,
            'Completed' => 0,
            'Dead' => 0
        );
        
        foreach ($results as $row) {
            $counts[$row->status] = $row->count;
            $counts['all'] += $row->count;
        }
        
        return $counts;
    }
}
