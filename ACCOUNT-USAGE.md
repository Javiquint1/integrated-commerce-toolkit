# Account Status & Pro Usage - Usage Examples

## How to Check Your Account Status

### Method 1: WordPress Admin Dashboard

1. Log into your WordPress admin panel
2. Look for **ICT Account** in the left sidebar menu
3. Click on it to see your full account status including:
   - Account Tier (Free/Pro/Enterprise)
   - Pro Status (Active/Inactive)
   - API Usage Statistics
   - Available Features
   - Subscription Expiry (if applicable)

### Method 2: Shortcode (Frontend)

Add this shortcode to any WordPress page or post:

```
[ict_account_status]
```

This will display your account information to logged-in users.

### Method 3: CLI Script

Run this command from the plugin directory:

```bash
php check-account-status.php
```

Or specify a user ID:

```bash
php check-account-status.php 1
```

### Method 4: Programmatically in PHP

```php
<?php
// Get account status for current user
$account = new ICT_Account();
$status = $account->get_account_status();

// Display basic info
echo "Account Tier: " . $status['tier'];
echo "Pro Status: " . ($status['is_pro'] ? 'Active' : 'Inactive');
echo "API Calls: " . $status['api_calls_count'] . " / " . $status['features']['api_calls_limit'];
```

## Common Use Cases

### Check if User Has Pro Access

```php
$account = new ICT_Account();
if ($account->is_pro_user()) {
    // User has pro access - enable premium features
    echo "Welcome, Pro user!";
} else {
    // Show upgrade prompt
    echo "Upgrade to Pro for more features!";
}
```

### Track API Usage

```php
$account = new ICT_Account();

// Before making an API call
if (!$account->has_reached_api_limit()) {
    // Make API call
    // ...
    
    // Track the usage
    $account->track_api_usage();
} else {
    echo "API limit reached. Please upgrade or wait for reset.";
}
```

### Upgrade User to Pro

```php
$account = new ICT_Account();
$user_id = get_current_user_id();

// Upgrade to Pro with 1 year expiry
$expiry_date = date('Y-m-d', strtotime('+1 year'));
$account->update_account_tier($user_id, 'pro', $expiry_date);

echo "Successfully upgraded to Pro!";
```

### Check Account Tier Features

```php
$account = new ICT_Account();
$status = $account->get_account_status();

echo "Your Plan Features:\n";
echo "- API Calls: " . $status['features']['api_calls_limit'] . " per month\n";
echo "- Sync Frequency: " . $status['features']['sync_frequency'] . "\n";
echo "- External APIs: " . $status['features']['external_apis'] . "\n";
echo "- Priority Support: " . ($status['features']['priority_support'] ? 'Yes' : 'No') . "\n";
```

## Account Tiers Comparison

| Feature | Free | Pro | Enterprise |
|---------|------|-----|------------|
| API Calls/Month | 100 | 1,000 | Unlimited |
| Sync Frequency | Daily | Hourly | Real-time |
| External APIs | 1 | 5 | Unlimited |
| Priority Support | ✗ | ✓ | ✓ |
| Advanced Caching | ✗ | ✓ | ✓ |

## Frequently Asked Questions

### Q: What is my account status?

**A:** Your account status includes your tier (Free/Pro/Enterprise), subscription expiry date (if applicable), and usage statistics. You can check it by:
- Visiting the ICT Account page in WordPress admin
- Using the `[ict_account_status]` shortcode
- Running `php check-account-status.php`

### Q: Do I still have pro usage?

**A:** To check if you have active Pro access:

```php
$account = new ICT_Account();
if ($account->is_pro_user()) {
    echo "YES - You have active Pro access!";
} else {
    echo "NO - You are on the Free tier.";
}
```

Or check the admin page: **WP Admin → ICT Account**

### Q: How do I upgrade to Pro?

**A:** Contact your administrator or use the account management API:

```php
$account = new ICT_Account();
$account->update_account_tier(
    get_current_user_id(), 
    'pro', 
    date('Y-m-d', strtotime('+1 year'))
);
```

### Q: What happens when I reach my API limit?

**A:** When you reach your API limit:
1. The system will prevent additional API calls
2. You'll see a notice in the admin dashboard
3. Options:
   - Wait for the monthly reset
   - Upgrade to a higher tier
   - Contact support for assistance

### Q: How do I check my API usage?

**A:** Check your API usage by:

```php
$account = new ICT_Account();
$status = $account->get_account_status();
echo "Used: " . $status['api_calls_count'];
echo "Limit: " . $status['features']['api_calls_limit'];
```

## Demo & Testing

To see a full demonstration of the account management system, open this file in your browser:

```
/wp-content/plugins/integrated-commerce-toolkit/account-status-demo.php
```

To run automated tests:

```bash
php test-account-system.php
```

## Integration Examples

### Example 1: Conditional Feature Access

```php
$account = new ICT_Account();
$status = $account->get_account_status();

if ($status['features']['advanced_caching']) {
    // Enable advanced caching for Pro/Enterprise users
    enable_advanced_cache();
}

if ($status['features']['external_apis'] > 1) {
    // Allow multiple API connections
    show_multi_api_settings();
}
```

### Example 2: Usage Enforcement

```php
$account = new ICT_Account();

function make_api_call() {
    global $account;
    
    // Check limit before making call
    if ($account->has_reached_api_limit()) {
        return array('error' => 'API limit reached');
    }
    
    // Make the API call
    $result = wp_remote_get('https://api.example.com/data');
    
    // Track usage
    $account->track_api_usage();
    
    return $result;
}
```

### Example 3: Upgrade Prompt

```php
$account = new ICT_Account();
$status = $account->get_account_status();

if (!$status['is_pro']) {
    echo '<div class="notice notice-info">';
    echo '<p><strong>Upgrade to Pro</strong></p>';
    echo '<p>Get ' . (1000 - $status['features']['api_calls_limit']) . ' more API calls per month!</p>';
    echo '</div>';
}
```

## Support

For questions or issues with account management:
- Check the plugin documentation
- Review the demo script: `account-status-demo.php`
- Run the test suite: `php test-account-system.php`
- Contact: https://github.com/Javiquint1/integrated-commerce-toolkit
