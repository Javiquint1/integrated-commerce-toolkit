#!/usr/bin/env php
<?php
/**
 * CLI Helper Script: Check Account Status
 * 
 * This script allows you to quickly check your ICT account status
 * Usage: php check-account-status.php [user_id]
 * 
 * If user_id is not provided, it checks for the first admin user
 */

// Try to load WordPress
$wp_load_paths = array(
    __DIR__ . '/../../../../wp-load.php',  // Standard WordPress installation
    __DIR__ . '/../../../wp-load.php',      // Alternative path
    __DIR__ . '/../../wp-load.php',         // Another alternative
);

$wp_loaded = false;
foreach ($wp_load_paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $wp_loaded = true;
        break;
    }
}

if (!$wp_loaded) {
    echo "Error: Could not find WordPress installation.\n";
    echo "Please run this script from the WordPress plugin directory.\n";
    exit(1);
}

// Check if ICT is loaded
if (!class_exists('ICT_Account')) {
    echo "Error: Integrated Commerce Toolkit is not activated.\n";
    exit(1);
}

// Get user ID from command line or use first admin
$user_id = isset($argv[1]) ? absint($argv[1]) : null;

if (!$user_id) {
    // Get first admin user
    $admins = get_users(array('role' => 'administrator', 'number' => 1));
    if (empty($admins)) {
        echo "Error: No users found. Please specify a user ID.\n";
        echo "Usage: php check-account-status.php [user_id]\n";
        exit(1);
    }
    $user_id = $admins[0]->ID;
}

// Get account status
$account = new ICT_Account();
$status = $account->get_account_status($user_id);

// Display results
echo "\n" . str_repeat("=", 60) . "\n";
echo "INTEGRATED COMMERCE TOOLKIT - ACCOUNT STATUS\n";
echo str_repeat("=", 60) . "\n\n";

echo "User ID:           " . $status['user_id'] . "\n";
echo "Account Tier:      " . strtoupper($status['tier']) . "\n";
echo "Pro Status:        " . ($status['is_pro'] ? '✓ ACTIVE' : '✗ INACTIVE') . "\n";

if (!empty($status['expiry_date'])) {
    echo "Expires:           " . $status['expiry_date'] . "\n";
}

echo "API Calls Used:    " . $status['api_calls_count'] . " / " . $status['features']['api_calls_limit'] . "\n";
echo "Last Sync:         " . ($status['last_sync'] ? $status['last_sync'] : 'Never') . "\n";

echo "\n--- PLAN FEATURES ---\n";
echo "Sync Frequency:    " . ucfirst($status['features']['sync_frequency']) . "\n";
echo "External APIs:     " . $status['features']['external_apis'] . "\n";
echo "Priority Support:  " . ($status['features']['priority_support'] ? 'Yes' : 'No') . "\n";
echo "Advanced Caching:  " . ($status['features']['advanced_caching'] ? 'Yes' : 'No') . "\n";

echo "\n" . str_repeat("=", 60) . "\n";

// Answer the specific questions
echo "\nQUICK ANSWERS:\n";
echo "• Account Status: " . strtoupper($status['tier']) . " tier" . ($status['is_pro'] ? ' (Pro Active)' : ' (Free tier)') . "\n";
echo "• Do you still have pro usage? " . ($status['is_pro'] ? 'YES ✓' : 'NO ✗') . "\n";
echo "\n";

exit(0);
