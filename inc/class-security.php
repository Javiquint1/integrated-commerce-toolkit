<?php
class ICT_Security {
    // Sanitize any input string
    public static function secure_input($data) {
        return sanitize_text_field($data);
    }

    // Check for CSRF during AJAX or form posts
    public static function check_nonce($action, $query_arg = '_wpnonce') {
        if (!isset($_REQUEST[$query_arg]) || !wp_verify_nonce($_REQUEST[$query_arg], $action)) {
            wp_die('Security check failed.');
        }
    }
}