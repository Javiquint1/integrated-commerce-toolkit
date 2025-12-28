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

    /**
     * Fetch WooCommerce products from local WordPress REST API
     * 
     * @param int $limit Number of products to fetch
     * @return array Products data or empty array on error
     */
    public function get_woocommerce_products($limit = 10) {
        $cache_key = 'ict_woo_products_' . $limit;
        
        // Check cache first
        $cached_data = get_transient($cache_key);
        if (false !== $cached_data) {
            return $cached_data;
        }

        // Build REST API URL for WooCommerce
        $url = rest_url('wc/v3/products');
        $url = add_query_arg(array('per_page' => absint($limit)), $url);

        $response = wp_remote_get($url, array(
            'headers' => array('Content-Type' => 'application/json'),
            'timeout' => 10
        ));

        if (is_wp_error($response)) {
            error_log('ICT WooCommerce API Error: ' . $response->get_error_message());
            return array();
        }

        $status_code = wp_remote_retrieve_response_code($response);
        if (200 !== $status_code) {
            error_log('ICT WooCommerce API Status: ' . $status_code);
            return array();
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (empty($data)) {
            return array();
        }

        // Cache for 1 hour
        set_transient($cache_key, $data, HOUR_IN_SECONDS);

        return $data;
    }

    /**
     * Clear all ICT API caches (useful for testing)
     */
    public function clear_all_caches() {
        delete_transient('ict_external_sync_data');
        delete_transient('ict_woo_products_10');
        delete_transient('ict_woo_products_20');
        delete_transient('ict_woo_products_50');
    }
}