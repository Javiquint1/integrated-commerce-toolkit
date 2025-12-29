<?php
/**
 * Standalone Test for ICT Account Management
 * 
 * This test verifies the account management system works correctly
 * Run this file using: php test-account-system.php
 */

// Mock WordPress functions for standalone testing
if (!function_exists('get_user_meta')) {
    // In-memory storage for testing
    $GLOBALS['mock_user_meta'] = array();
    
    function get_user_meta($user_id, $key, $single = false) {
        if (!isset($GLOBALS['mock_user_meta'][$user_id])) {
            return $single ? '' : array();
        }
        if (!isset($GLOBALS['mock_user_meta'][$user_id][$key])) {
            return $single ? '' : array();
        }
        $value = $GLOBALS['mock_user_meta'][$user_id][$key];
        return $single ? $value : array($value);
    }
    
    function update_user_meta($user_id, $key, $value) {
        if (!isset($GLOBALS['mock_user_meta'][$user_id])) {
            $GLOBALS['mock_user_meta'][$user_id] = array();
        }
        $GLOBALS['mock_user_meta'][$user_id][$key] = $value;
        return true;
    }
    
    function current_time($type) {
        return ($type === 'timestamp') ? time() : date('Y-m-d H:i:s');
    }
    
    function absint($value) {
        return abs((int)$value);
    }
    
    function sanitize_text_field($value) {
        return strip_tags(trim($value));
    }
    
    // Define ABSPATH for the class
    define('ABSPATH', true);
}

// Load the ICT_Account class
require_once __DIR__ . '/inc/class-account.php';

// Test Suite
class ICT_Account_Tests {
    private $account;
    private $test_user_id = 123;
    private $passed = 0;
    private $failed = 0;
    
    public function __construct() {
        $this->account = new ICT_Account();
    }
    
    public function run_all_tests() {
        echo "\n" . str_repeat("=", 70) . "\n";
        echo "ICT ACCOUNT MANAGEMENT - TEST SUITE\n";
        echo str_repeat("=", 70) . "\n\n";
        
        $this->test_default_account_status();
        $this->test_upgrade_to_pro();
        $this->test_pro_status_check();
        $this->test_tier_features();
        $this->test_api_usage_tracking();
        $this->test_api_limit_check();
        $this->test_expired_pro_subscription();
        $this->test_enterprise_tier();
        
        echo "\n" . str_repeat("=", 70) . "\n";
        echo "TEST RESULTS: {$this->passed} passed, {$this->failed} failed\n";
        echo str_repeat("=", 70) . "\n\n";
        
        return $this->failed === 0;
    }
    
    private function assert($condition, $test_name) {
        if ($condition) {
            echo "✓ PASS: $test_name\n";
            $this->passed++;
        } else {
            echo "✗ FAIL: $test_name\n";
            $this->failed++;
        }
    }
    
    private function test_default_account_status() {
        echo "\nTest 1: Default Account Status (Free Tier)\n";
        echo str_repeat("-", 70) . "\n";
        
        $status = $this->account->get_account_status($this->test_user_id);
        
        $this->assert($status['user_id'] === $this->test_user_id, "User ID matches");
        $this->assert($status['tier'] === 'free', "Default tier is 'free'");
        $this->assert($status['is_pro'] === false, "Default pro status is false");
        $this->assert($status['api_calls_count'] === 0, "Initial API calls count is 0");
        $this->assert(isset($status['features']), "Features array exists");
        $this->assert($status['features']['api_calls_limit'] === 100, "Free tier has 100 API limit");
    }
    
    private function test_upgrade_to_pro() {
        echo "\nTest 2: Upgrade to Pro Tier\n";
        echo str_repeat("-", 70) . "\n";
        
        $expiry = date('Y-m-d', strtotime('+1 year'));
        $result = $this->account->update_account_tier($this->test_user_id, 'pro', $expiry);
        
        $this->assert($result === true, "Update account tier returns true");
        
        $status = $this->account->get_account_status($this->test_user_id);
        $this->assert($status['tier'] === 'pro', "Tier updated to 'pro'");
        $this->assert($status['expiry_date'] === $expiry, "Expiry date set correctly");
    }
    
    private function test_pro_status_check() {
        echo "\nTest 3: Pro Status Verification\n";
        echo str_repeat("-", 70) . "\n";
        
        $is_pro = $this->account->is_pro_user($this->test_user_id);
        $this->assert($is_pro === true, "User is recognized as Pro");
        
        $status = $this->account->get_account_status($this->test_user_id);
        $this->assert($status['is_pro'] === true, "Status shows is_pro as true");
        $this->assert($status['features']['api_calls_limit'] === 1000, "Pro tier has 1000 API limit");
    }
    
    private function test_tier_features() {
        echo "\nTest 4: Tier Features\n";
        echo str_repeat("-", 70) . "\n";
        
        $free_features = $this->account->get_tier_features('free');
        $this->assert($free_features['api_calls_limit'] === 100, "Free tier: 100 API calls");
        $this->assert($free_features['sync_frequency'] === 'daily', "Free tier: daily sync");
        $this->assert($free_features['priority_support'] === false, "Free tier: no priority support");
        
        $pro_features = $this->account->get_tier_features('pro');
        $this->assert($pro_features['api_calls_limit'] === 1000, "Pro tier: 1000 API calls");
        $this->assert($pro_features['sync_frequency'] === 'hourly', "Pro tier: hourly sync");
        $this->assert($pro_features['priority_support'] === true, "Pro tier: has priority support");
        
        $enterprise_features = $this->account->get_tier_features('enterprise');
        $this->assert($enterprise_features['api_calls_limit'] === 'unlimited', "Enterprise tier: unlimited API calls");
        $this->assert($enterprise_features['sync_frequency'] === 'real-time', "Enterprise tier: real-time sync");
    }
    
    private function test_api_usage_tracking() {
        echo "\nTest 5: API Usage Tracking\n";
        echo str_repeat("-", 70) . "\n";
        
        // Track 5 API calls
        for ($i = 0; $i < 5; $i++) {
            $this->account->track_api_usage($this->test_user_id);
        }
        
        $status = $this->account->get_account_status($this->test_user_id);
        $this->assert($status['api_calls_count'] === 5, "API calls tracked correctly (5 calls)");
        $this->assert(!empty($status['last_sync']), "Last sync timestamp set");
    }
    
    private function test_api_limit_check() {
        echo "\nTest 6: API Limit Checking\n";
        echo str_repeat("-", 70) . "\n";
        
        // Pro tier has 1000 calls, we have 5, should not be at limit
        $has_reached = $this->account->has_reached_api_limit($this->test_user_id);
        $this->assert($has_reached === false, "Has not reached API limit (5/1000)");
        
        // Simulate reaching the limit
        update_user_meta($this->test_user_id, '_ict_api_calls_count', 1000);
        $has_reached = $this->account->has_reached_api_limit($this->test_user_id);
        $this->assert($has_reached === true, "Has reached API limit (1000/1000)");
        
        // Reset for next tests
        update_user_meta($this->test_user_id, '_ict_api_calls_count', 0);
    }
    
    private function test_expired_pro_subscription() {
        echo "\nTest 7: Expired Pro Subscription\n";
        echo str_repeat("-", 70) . "\n";
        
        // Set expiry date to past
        $past_date = date('Y-m-d', strtotime('-1 day'));
        $this->account->update_account_tier($this->test_user_id, 'pro', $past_date);
        
        $is_pro = $this->account->is_pro_user($this->test_user_id);
        $this->assert($is_pro === false, "Expired subscription shows as not pro");
        
        // Reset to future date
        $future_date = date('Y-m-d', strtotime('+1 year'));
        $this->account->update_account_tier($this->test_user_id, 'pro', $future_date);
    }
    
    private function test_enterprise_tier() {
        echo "\nTest 8: Enterprise Tier\n";
        echo str_repeat("-", 70) . "\n";
        
        $this->account->update_account_tier($this->test_user_id, 'enterprise', null);
        
        $status = $this->account->get_account_status($this->test_user_id);
        $this->assert($status['tier'] === 'enterprise', "Tier updated to enterprise");
        $this->assert($status['is_pro'] === true, "Enterprise tier recognized as pro");
        $this->assert($status['features']['api_calls_limit'] === 'unlimited', "Enterprise has unlimited API calls");
        
        // Enterprise should never reach limit
        update_user_meta($this->test_user_id, '_ict_api_calls_count', 999999);
        $has_reached = $this->account->has_reached_api_limit($this->test_user_id);
        $this->assert($has_reached === false, "Enterprise never reaches API limit");
    }
}

// Run the tests
$tests = new ICT_Account_Tests();
$success = $tests->run_all_tests();

exit($success ? 0 : 1);
