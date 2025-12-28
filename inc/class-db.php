<?php
/**
 * Database Handler for Integrated Commerce Toolkit
 * Focus: SQL Injection Prevention and Prepared Statements
 */

if (!defined('ABSPATH')) exit;

class ICT_DB {

    /**
     * Retrieve a specific product's metadata securely.
     * Demonstrates use of $wpdb->prepare for security.
     */
    public function get_product_sync_meta($product_id) {
        global $wpdb;

        // 1. HARDENING: Ensure the ID is a clean integer
        $clean_id = absint($product_id);

        // 2. PREPARED STATEMENT: Use placeholders (%d for digit, %s for string)
        // This is the "Senior" way to write SQL in WordPress.
        $query = $wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->prefix}postmeta 
             WHERE post_id = %d AND meta_key = %s",
            $clean_id,
            '_ict_last_sync_time'
        );

        // 3. EXECUTION
        return $wpdb->get_var($query);
    }

    /**
     * Update sync status after a successful API call.
     */
    public function update_sync_status($product_id, $status) {
        global $wpdb;

        // Use the built-in $wpdb->update method which handles preparation automatically
        $wpdb->update(
            "{$wpdb->prefix}postmeta",
            array('meta_value' => current_time('mysql')), // Data to update
            array('post_id' => absint($product_id), 'meta_key' => '_ict_last_sync_time'), // Where clause
            array('%s'), // Data format
            array('%d', '%s') // Where format
        );
    }
}