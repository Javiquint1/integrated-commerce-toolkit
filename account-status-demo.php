<?php
/**
 * Account Status Demo Script
 * 
 * This demonstrates the new account management features
 * Usage: Place this file in wp-content/plugins/integrated-commerce-toolkit/
 *        and access it via WordPress admin or WP-CLI
 */

if (!defined('ABSPATH')) {
    // If running standalone, try to load WordPress
    $wp_load_paths = array(
        __DIR__ . '/../../../../wp-load.php',
        __DIR__ . '/../../../wp-load.php',
        __DIR__ . '/../../wp-load.php',
    );
    
    foreach ($wp_load_paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            break;
        }
    }
}

// Make sure ICT is loaded
if (!class_exists('ICT_Account')) {
    die('Error: ICT_Account class not found. Please activate the plugin first.');
}

echo "<h1>ICT Account Status Demo</h1>";
echo "<hr>";

// Create account instance
$account = new ICT_Account();

// Demo 1: Get current user account status
echo "<h2>Demo 1: Check Current User Account Status</h2>";
$user_id = get_current_user_id();

if ($user_id) {
    $status = $account->get_account_status($user_id);
    
    echo "<p><strong>User ID:</strong> {$status['user_id']}</p>";
    echo "<p><strong>Account Tier:</strong> " . strtoupper($status['tier']) . "</p>";
    echo "<p><strong>Pro Status:</strong> " . ($status['is_pro'] ? '<span style="color:green">✓ Active</span>' : '<span style="color:red">✗ Inactive</span>') . "</p>";
    
    if (!empty($status['expiry_date'])) {
        echo "<p><strong>Expires:</strong> {$status['expiry_date']}</p>";
    }
    
    echo "<p><strong>API Calls:</strong> {$status['api_calls_count']} / {$status['features']['api_calls_limit']}</p>";
    echo "<p><strong>Last Sync:</strong> " . ($status['last_sync'] ? $status['last_sync'] : 'Never') . "</p>";
    
    echo "<h3>Available Features:</h3>";
    echo "<ul>";
    echo "<li>Sync Frequency: {$status['features']['sync_frequency']}</li>";
    echo "<li>External APIs: {$status['features']['external_apis']}</li>";
    echo "<li>Priority Support: " . ($status['features']['priority_support'] ? 'Yes' : 'No') . "</li>";
    echo "<li>Advanced Caching: " . ($status['features']['advanced_caching'] ? 'Yes' : 'No') . "</li>";
    echo "</ul>";
} else {
    echo "<p>No user logged in.</p>";
}

echo "<hr>";

// Demo 2: Simulate upgrading to Pro
echo "<h2>Demo 2: Upgrade to Pro Tier</h2>";
if ($user_id) {
    // Set to pro with 1 year expiry
    $expiry = date('Y-m-d', strtotime('+1 year'));
    $result = $account->update_account_tier($user_id, 'pro', $expiry);
    
    if ($result) {
        echo "<p style='color:green'>✓ Successfully upgraded to Pro tier!</p>";
        echo "<p><strong>Expiry Date:</strong> $expiry</p>";
        
        // Verify pro status
        $is_pro = $account->is_pro_user($user_id);
        echo "<p><strong>Pro Status Verified:</strong> " . ($is_pro ? '<span style="color:green">✓ Active</span>' : '<span style="color:red">✗ Failed</span>') . "</p>";
    } else {
        echo "<p style='color:red'>✗ Failed to upgrade account.</p>";
    }
} else {
    echo "<p>No user logged in.</p>";
}

echo "<hr>";

// Demo 3: Track API Usage
echo "<h2>Demo 3: Track API Usage</h2>";
if ($user_id) {
    // Track 5 API calls
    for ($i = 1; $i <= 5; $i++) {
        $account->track_api_usage($user_id);
        echo "<p>✓ API call #{$i} tracked</p>";
    }
    
    $status = $account->get_account_status($user_id);
    echo "<p><strong>Total API Calls:</strong> {$status['api_calls_count']}</p>";
    
    // Check if limit reached
    $limit_reached = $account->has_reached_api_limit($user_id);
    echo "<p><strong>Limit Reached:</strong> " . ($limit_reached ? '<span style="color:red">Yes</span>' : '<span style="color:green">No</span>') . "</p>";
} else {
    echo "<p>No user logged in.</p>";
}

echo "<hr>";

// Demo 4: Compare Tiers
echo "<h2>Demo 4: Compare Account Tiers</h2>";
$tiers = array('free', 'pro', 'enterprise');
echo "<table border='1' cellpadding='10' style='border-collapse:collapse; width:100%;'>";
echo "<tr><th>Feature</th><th>Free</th><th>Pro</th><th>Enterprise</th></tr>";

$free_features = $account->get_tier_features('free');
$pro_features = $account->get_tier_features('pro');
$enterprise_features = $account->get_tier_features('enterprise');

echo "<tr>";
echo "<td><strong>API Calls/Month</strong></td>";
echo "<td>{$free_features['api_calls_limit']}</td>";
echo "<td>{$pro_features['api_calls_limit']}</td>";
echo "<td>{$enterprise_features['api_calls_limit']}</td>";
echo "</tr>";

echo "<tr>";
echo "<td><strong>Sync Frequency</strong></td>";
echo "<td>{$free_features['sync_frequency']}</td>";
echo "<td>{$pro_features['sync_frequency']}</td>";
echo "<td>{$enterprise_features['sync_frequency']}</td>";
echo "</tr>";

echo "<tr>";
echo "<td><strong>External APIs</strong></td>";
echo "<td>{$free_features['external_apis']}</td>";
echo "<td>{$pro_features['external_apis']}</td>";
echo "<td>{$enterprise_features['external_apis']}</td>";
echo "</tr>";

echo "<tr>";
echo "<td><strong>Priority Support</strong></td>";
echo "<td>" . ($free_features['priority_support'] ? '✓' : '✗') . "</td>";
echo "<td>" . ($pro_features['priority_support'] ? '✓' : '✗') . "</td>";
echo "<td>" . ($enterprise_features['priority_support'] ? '✓' : '✗') . "</td>";
echo "</tr>";

echo "<tr>";
echo "<td><strong>Advanced Caching</strong></td>";
echo "<td>" . ($free_features['advanced_caching'] ? '✓' : '✗') . "</td>";
echo "<td>" . ($pro_features['advanced_caching'] ? '✓' : '✗') . "</td>";
echo "<td>" . ($enterprise_features['advanced_caching'] ? '✓' : '✗') . "</td>";
echo "</tr>";

echo "</table>";

echo "<hr>";
echo "<h2>Quick Answer</h2>";
echo "<div style='background:#f0f0f0; padding:20px; border-radius:5px;'>";
echo "<h3>Question: What is my account status?</h3>";
if ($user_id) {
    $status = $account->get_account_status($user_id);
    echo "<p style='font-size:18px;'><strong>Answer:</strong> Your account is on the <strong>" . strtoupper($status['tier']) . "</strong> tier.</p>";
    
    echo "<h3>Question: Do I still have pro usage?</h3>";
    echo "<p style='font-size:18px;'><strong>Answer:</strong> ";
    if ($status['is_pro']) {
        echo "<span style='color:green; font-weight:bold;'>YES ✓</span> - Your Pro status is active";
        if (!empty($status['expiry_date'])) {
            echo " (expires on {$status['expiry_date']})";
        }
    } else {
        echo "<span style='color:red; font-weight:bold;'>NO ✗</span> - You are currently on the Free tier";
    }
    echo "</p>";
} else {
    echo "<p>Please log in to check your account status.</p>";
}
echo "</div>";
