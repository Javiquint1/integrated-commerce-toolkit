<?php
/**
 * Account Management Handler for Integrated Commerce Toolkit
 * Focus: Account Status and Pro Usage Tracking
 */

if (!defined('ABSPATH')) exit;

class ICT_Account {

    const TIER_FREE = 'free';
    const TIER_PRO = 'pro';
    const TIER_ENTERPRISE = 'enterprise';

    /**
     * Get current user's account status
     * 
     * @param int $user_id User ID (defaults to current user)
     * @return array Account status information
     */
    public function get_account_status($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $user_id = absint($user_id);

        // Get account tier from user meta
        $tier = get_user_meta($user_id, '_ict_account_tier', true);
        
        // Default to free tier if not set
        if (empty($tier)) {
            $tier = self::TIER_FREE;
        }

        // Get subscription expiry
        $expiry_date = get_user_meta($user_id, '_ict_pro_expiry', true);
        
        // Get usage stats
        $api_calls = get_user_meta($user_id, '_ict_api_calls_count', true);
        $api_calls = $api_calls ? absint($api_calls) : 0;

        $last_sync = get_user_meta($user_id, '_ict_last_sync_date', true);

        return array(
            'user_id' => $user_id,
            'tier' => $tier,
            'is_pro' => $this->is_pro_user($user_id),
            'expiry_date' => $expiry_date,
            'api_calls_count' => $api_calls,
            'last_sync' => $last_sync,
            'features' => $this->get_tier_features($tier)
        );
    }

    /**
     * Check if user has pro access
     * 
     * @param int $user_id User ID (defaults to current user)
     * @return bool True if user has active pro access
     */
    public function is_pro_user($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $user_id = absint($user_id);
        $tier = get_user_meta($user_id, '_ict_account_tier', true);

        // Check if tier is pro or enterprise
        if ($tier === self::TIER_PRO || $tier === self::TIER_ENTERPRISE) {
            // Check if subscription is still active
            $expiry_date = get_user_meta($user_id, '_ict_pro_expiry', true);
            
            if (empty($expiry_date)) {
                return true; // Lifetime subscription
            }

            // Check if expiry date is in the future
            $expiry_timestamp = strtotime($expiry_date);
            if ($expiry_timestamp !== false && $expiry_timestamp > current_time('timestamp')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get features available for a specific tier
     * 
     * @param string $tier Account tier
     * @return array List of features
     */
    public function get_tier_features($tier) {
        $features = array(
            self::TIER_FREE => array(
                'api_calls_limit' => 100,
                'sync_frequency' => 'daily',
                'external_apis' => 1,
                'priority_support' => false,
                'advanced_caching' => false
            ),
            self::TIER_PRO => array(
                'api_calls_limit' => 1000,
                'sync_frequency' => 'hourly',
                'external_apis' => 5,
                'priority_support' => true,
                'advanced_caching' => true
            ),
            self::TIER_ENTERPRISE => array(
                'api_calls_limit' => 'unlimited',
                'sync_frequency' => 'real-time',
                'external_apis' => 'unlimited',
                'priority_support' => true,
                'advanced_caching' => true
            )
        );

        return isset($features[$tier]) ? $features[$tier] : $features[self::TIER_FREE];
    }

    /**
     * Update account tier
     * 
     * @param int $user_id User ID
     * @param string $tier New tier
     * @param string $expiry_date Optional expiry date (Y-m-d format)
     * @return bool Success status
     */
    public function update_account_tier($user_id, $tier, $expiry_date = null) {
        $user_id = absint($user_id);
        $tier = sanitize_text_field($tier);

        // Validate tier
        $valid_tiers = array(self::TIER_FREE, self::TIER_PRO, self::TIER_ENTERPRISE);
        if (!in_array($tier, $valid_tiers)) {
            return false;
        }

        update_user_meta($user_id, '_ict_account_tier', $tier);

        if ($expiry_date) {
            update_user_meta($user_id, '_ict_pro_expiry', sanitize_text_field($expiry_date));
        }

        return true;
    }

    /**
     * Track API usage for a user
     * 
     * @param int $user_id User ID
     * @return void
     */
    public function track_api_usage($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $user_id = absint($user_id);
        $current_count = get_user_meta($user_id, '_ict_api_calls_count', true);
        $current_count = $current_count ? absint($current_count) : 0;
        
        update_user_meta($user_id, '_ict_api_calls_count', $current_count + 1);
        update_user_meta($user_id, '_ict_last_sync_date', current_time('mysql'));
    }

    /**
     * Check if user has reached their API limit
     * 
     * @param int $user_id User ID
     * @return bool True if limit reached
     */
    public function has_reached_api_limit($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $user_id = absint($user_id);
        $status = $this->get_account_status($user_id);
        
        // Enterprise and unlimited plans never hit limit
        if ($status['features']['api_calls_limit'] === 'unlimited') {
            return false;
        }

        $limit = absint($status['features']['api_calls_limit']);
        $current = $status['api_calls_count'];

        return $current >= $limit;
    }

    /**
     * Reset monthly API usage counter (should be called by cron)
     * 
     * @param int $user_id User ID (if null, resets for all users)
     * @return bool Success status
     */
    public function reset_api_usage($user_id = null) {
        if ($user_id) {
            $user_id = absint($user_id);
            return update_user_meta($user_id, '_ict_api_calls_count', 0);
        } else {
            // Reset for all users
            global $wpdb;
            $result = $wpdb->query(
                $wpdb->prepare(
                    "UPDATE {$wpdb->usermeta} 
                     SET meta_value = %s 
                     WHERE meta_key = %s",
                    '0',
                    '_ict_api_calls_count'
                )
            );
            
            if ($result === false) {
                error_log('ICT: Failed to reset API usage counters');
                return false;
            }
            
            return true;
        }
    }
}
