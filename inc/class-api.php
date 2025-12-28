<?php
class ICT_API {
    public function fetch_external_data() {
        $cache_key = 'ict_external_api_cache';
        $cached = get_transient($cache_key);

        if ($cached !== false) return $cached;

        $response = wp_remote_get('https://api.example.com/data');
        
        if (is_wp_error($response)) return false;

        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        // Cache for 1 hour to optimize performance
        set_transient($cache_key, $data, HOUR_IN_SECONDS);
        
        return $data;
    }
}