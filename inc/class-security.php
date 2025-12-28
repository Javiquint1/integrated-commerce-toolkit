<?php
/**
 * Security Handler for Integrated Commerce Toolkit
 * Focus: Data Hardening and CSRF Prevention
 */

if (!defined('ABSPATH')) exit;

class ICT_Security {

    /**
     * Sanitize user input based on expected type.
     * Use this instead of raw $_POST or $_GET data.
     */
    public static function secure_input($data, $type = 'text') {
        if (is_array($data)) {
            return array_map([self::class, 'secure_input'], $data);
        }

        switch ($type) {
            case 'int':
                return absint($data); // Ensures it's a positive integer
            case 'email':
                return sanitize_email($data);
            case 'url':
                return esc_url_raw($data);
            default:
                return sanitize_text_field($data); // Removes HTML tags and line breaks
        }
    }

    /**
     * Verifies a WordPress Nonce.
     * Prevents Cross-Site Request Forgery (CSRF).
     */
    public static function check_request($action, $nonce_field = '_wpnonce') {
        if (!isset($_REQUEST[$nonce_field]) || !wp_verify_nonce($_REQUEST[$nonce_field], $action)) {
            // Log the failed attempt for security auditing
            error_log("Security Alert: Invalid Nonce for action $action");
            wp_die(__('Unauthorized request.', 'ict-toolkit'), __('Security Error', 'ict-toolkit'), 403);
        }
    }
}