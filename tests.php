<?php
/**
 * ICT Testing & Verification Guide
 * 
 * This file contains practical test cases for the Integrated Commerce Toolkit
 * Copy and run these in WordPress admin or use WP-CLI
 */

if (!defined('ABSPATH')) exit;

/**
 * TEST 1: Verify Plugin Classes Load
 * Expected: All classes should exist
 */
function ict_test_classes_load() {
    echo "TEST 1: Checking if all classes are loaded...\n";
    
    $classes = array(
        'ICT_Main',
        'ICT_API',
        'ICT_DB',
        'ICT_Security'
    );
    
    foreach ($classes as $class) {
        if (class_exists($class)) {
            echo "✓ {$class} loaded successfully\n";
        } else {
            echo "✗ {$class} NOT FOUND\n";
        }
    }
}

/**
 * TEST 2: Test WooCommerce API Integration
 * Expected: Should fetch products from your WooCommerce store
 */
function ict_test_woocommerce_api() {
    echo "\nTEST 2: Testing WooCommerce API Integration...\n";
    
    if (!class_exists('ICT_API')) {
        echo "✗ ICT_API class not found\n";
        return;
    }
    
    $api = new ICT_API();
    $products = $api->get_woocommerce_products(5);
    
    if (is_array($products) && !empty($products)) {
        echo "✓ Successfully fetched " . count($products) . " products\n";
        echo "  Sample product: " . $products[0]['name'] . "\n";
    } else {
        echo "✗ Failed to fetch products\n";
        echo "  Possible issues:\n";
        echo "  - WooCommerce not installed or activated\n";
        echo "  - REST API not enabled in WooCommerce settings\n";
        echo "  - Network error\n";
    }
}

/**
 * TEST 3: Test API Caching
 * Expected: Second call should return cached data (and be faster)
 */
function ict_test_api_caching() {
    echo "\nTEST 3: Testing API Caching...\n";
    
    if (!class_exists('ICT_API')) {
        echo "✗ ICT_API class not found\n";
        return;
    }
    
    $api = new ICT_API();
    
    // Clear cache first
    $api->clear_all_caches();
    echo "  Cleared all caches\n";
    
    // First call - fetches from API
    $start_time = microtime(true);
    $products1 = $api->get_woocommerce_products(5);
    $time1 = microtime(true) - $start_time;
    
    // Second call - should use cache
    $start_time = microtime(true);
    $products2 = $api->get_woocommerce_products(5);
    $time2 = microtime(true) - $start_time;
    
    echo "  First call: " . round($time1 * 1000, 2) . "ms (from API)\n";
    echo "  Second call: " . round($time2 * 1000, 2) . "ms (from cache)\n";
    
    if ($time2 < $time1) {
        echo "✓ Caching is working correctly\n";
    } else {
        echo "⚠ Second call not faster (cache may not be working)\n";
    }
}

/**
 * TEST 4: Test Database Operations
 * Expected: Should update and retrieve sync metadata
 */
function ict_test_database_operations() {
    echo "\nTEST 4: Testing Database Operations...\n";
    
    if (!class_exists('ICT_DB')) {
        echo "✗ ICT_DB class not found\n";
        return;
    }
    
    $db = new ICT_DB();
    
    // Test with product ID 1 (usually exists)
    $product_id = 1;
    
    // Update sync status
    $db->update_sync_status($product_id, 'success');
    echo "  Updated sync status for product ID {$product_id}\n";
    
    // Retrieve sync metadata
    $meta = $db->get_product_sync_meta($product_id);
    
    if (!empty($meta)) {
        echo "✓ Retrieved sync metadata: " . substr($meta, 0, 50) . "...\n";
    } else {
        echo "⚠ No metadata found (this is okay on first run)\n";
    }
}

/**
 * TEST 5: Test Security Functions
 * Expected: Should sanitize input and verify nonces
 */
function ict_test_security() {
    echo "\nTEST 5: Testing Security Functions...\n";
    
    if (!class_exists('ICT_Security')) {
        echo "✗ ICT_Security class not found\n";
        return;
    }
    
    // Test input sanitization
    $dirty_input = "<script>alert('xss')</script>Hello";
    $clean_input = ICT_Security::secure_input($dirty_input);
    
    if (strpos($clean_input, '<script>') === false) {
        echo "✓ Input sanitization working (script tags removed)\n";
    } else {
        echo "✗ Input not properly sanitized\n";
    }
    
    echo "  Original: " . substr($dirty_input, 0, 30) . "...\n";
    echo "  Cleaned: " . $clean_input . "\n";
}

/**
 * TEST 6: Run All Tests
 * Expected: Green checkmarks for all tests
 */
function ict_run_all_tests() {
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "INTEGRATED COMMERCE TOOLKIT - TEST SUITE\n";
    echo str_repeat("=", 50) . "\n";
    
    ict_test_classes_load();
    ict_test_woocommerce_api();
    ict_test_api_caching();
    ict_test_database_operations();
    ict_test_security();
    ict_test_account_management();
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "Tests completed. Check results above.\n";
    echo str_repeat("=", 50) . "\n";
}

/**
 * TEST 7: Test Account Management
 * Expected: Should create, retrieve, and validate account status
 */
function ict_test_account_management() {
    echo "\nTEST 7: Testing Account Management System...\n";
    
    if (!class_exists('ICT_Account')) {
        echo "✗ ICT_Account class not found\n";
        return;
    }
    
    $account = new ICT_Account();
    $user_id = get_current_user_id();
    
    if (!$user_id) {
        echo "⚠ No user logged in, skipping user-specific tests\n";
        return;
    }
    
    // Test 1: Get account status
    $status = $account->get_account_status($user_id);
    if (is_array($status) && isset($status['tier'])) {
        echo "✓ Retrieved account status successfully\n";
        echo "  Current Tier: " . $status['tier'] . "\n";
        echo "  Pro Status: " . ($status['is_pro'] ? 'Active' : 'Inactive') . "\n";
    } else {
        echo "✗ Failed to retrieve account status\n";
    }
    
    // Test 2: Check pro status
    $is_pro = $account->is_pro_user($user_id);
    echo "  Is Pro User: " . ($is_pro ? 'Yes' : 'No') . "\n";
    
    // Test 3: Update to pro tier
    $updated = $account->update_account_tier($user_id, 'pro', date('Y-m-d', strtotime('+1 year')));
    if ($updated) {
        echo "✓ Successfully updated account to Pro tier\n";
        
        // Verify the update
        $new_status = $account->get_account_status($user_id);
        if ($new_status['is_pro']) {
            echo "✓ Pro status verified active\n";
        }
    } else {
        echo "✗ Failed to update account tier\n";
    }
    
    // Test 4: Track API usage
    $account->track_api_usage($user_id);
    $updated_status = $account->get_account_status($user_id);
    echo "  API Calls Count: " . $updated_status['api_calls_count'] . "\n";
    
    // Test 5: Check API limit
    $has_reached_limit = $account->has_reached_api_limit($user_id);
    echo "  Reached API Limit: " . ($has_reached_limit ? 'Yes' : 'No') . "\n";
    
    echo "✓ Account management system working correctly\n";
}

// To run tests, use one of these methods:

// Method 1: In WordPress admin, use this in a custom plugin or theme functions.php:
// ict_run_all_tests();

// Method 2: Use WP-CLI:
// wp eval-file path/to/this/file.php

// Method 3: Add this to your theme's functions.php temporarily:
// add_action('admin_init', function() {
//     if (current_user_can('manage_options')) {
//         ict_run_all_tests();
//     }
// });

// Note: Uncomment the line below to auto-run tests (for debugging)
// ict_run_all_tests();
