<?php
/**
 * API Handler for Integrated Commerce Toolkit
 * Focus: Performance Caching and Reliable Fetching
 */

if (!defined('ABSPATH')) exit;

class ICT_API {

    // The endpoint we are targeting
    private $api_endpoint = 'https://api.mockaroo.com/api/test_data'; 

    /**
     * Get external data with Transient Caching
     */
    public function get_external_commerce_data() {
        $cache_key = 'ict_external_sync_data';
        
        // 1. PERFORMANCE: Check if we have a cached version first
        $cached_data = get_transient($cache_key);
        if (false !== $cached_data) {
            return $cached_data; 
        }

        // 2. FETCH: Use the WP standard for remote requests
        $response = wp_remote_get($this->api_endpoint, [
            'timeout' => 15,
            'headers' => [
                'Accept' => 'application/json',
                // 'Authorization' => 'Bearer ' . ICT_API_KEY // If using a key
            ]
        ]);

        // 3. RELIABILITY: Handle errors gracefully
        if (is_wp_error($response)) {
            error_log('ICT API Sync Error: ' . $response->get_error_message());
            return []; // Return empty array so the site doesn't crash
        }

        $status_code = wp_remote_retrieve_response_code($response);
        if (200 !== $status_code) {
            return [];
        }

        // 4. PROCESSING: Sanitize the body
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (empty($data)) {
            return [];
        }

        // 5. CACHING: Save for 1 hour to optimize server resources
        set_transient($cache_key, $data, HOUR_IN_SECONDS);

        return $data;
    }
}