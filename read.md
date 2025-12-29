# Integrated Commerce Toolkit (ICT)

A modular, enterprise-grade WordPress plugin framework for integrating external commerce services with secure practices, API caching, and prepared database queries.

## Features

✅ **Modular OOP Architecture** - Clean separation of concerns with dedicated classes  
✅ **Security-First Design** - Input sanitization, nonce verification, prepared statements  
✅ **Performance Optimization** - API response caching with WordPress transients  
✅ **WooCommerce Integration** - Fetch products from local WooCommerce REST API  
✅ **External API Support** - Easily extend to connect Shopify, custom APIs, etc.  
✅ **Database Safety** - All queries use prepared statements to prevent SQL injection  
✅ **Account Management** - Track user tiers (Free, Pro, Enterprise) and subscription status  
✅ **Usage Tracking** - Monitor API usage limits and enforce tier-based restrictions  
✅ **Pro Status Verification** - Check if users have active Pro subscriptions  

## Installation

1. **Upload to WordPress:**
   ```
   /wp-content/plugins/integrated-commerce-toolkit/
   ```

2. **Activate in WordPress Admin:**
   - Go to `wp-admin/plugins.php`
   - Find "Integrated Commerce Toolkit"
   - Click **Activate**

3. **Verify Installation:**
   - Plugin will auto-load on `plugins_loaded` hook
   - Check `wp-content/debug.log` for any errors (if WP_DEBUG enabled)

## Architecture

```
integrated-commerce-toolkit/
├── integrated-commerce-toolkit.php    # Main plugin file with header & bootloader
├── inc/
│   ├── class-main.php                 # Plugin initializer & orchestrator
│   ├── class-api.php                  # External API & WooCommerce integration
│   ├── class-db.php                   # Secure database operations
│   ├── class-security.php             # Input sanitization & CSRF protection
│   └── class-account.php              # Account & subscription management
├── check-account-status.php           # CLI tool for checking account status
├── account-status-demo.php            # Demo script showing account features
└── README.md                          # This file
```

## Components

### 1. **ICT_Main** (Orchestrator)
- Loads all dependencies
- Hooks plugin sync logic into WordPress `init` action
- Coordinates API and database operations

### 2. **ICT_API** (External Data Integration)
- Fetches WooCommerce products via REST API
- Fetches external commerce APIs (Shopify, custom endpoints)
- Implements automatic caching (1-hour transients)
- Error handling for failed requests

**Methods:**
- `get_external_commerce_data()` - Fetch external API data
- `get_woocommerce_products($limit)` - Fetch WooCommerce products
- `clear_all_caches()` - Clear transient caches (for testing)

### 3. **ICT_DB** (Secure Database Operations)
- Prepared statements using `$wpdb->prepare()`
- SQL injection prevention
- Sync status tracking in postmeta

**Methods:**
- `get_product_sync_meta($product_id)` - Retrieve product sync metadata
- `update_sync_status($product_id, $status)` - Update last sync time

### 4. **ICT_Security** (Input Validation)
- Input sanitization using WordPress standards
- CSRF/nonce verification

**Methods:**
- `secure_input($data)` - Sanitize text input
- `check_nonce($action, $query_arg)` - Verify nonce for CSRF protection

### 5. **ICT_Account** (Account & Subscription Management)
- User account tier management (Free, Pro, Enterprise)
- Pro subscription status tracking
- API usage limits and monitoring
- Feature access control based on tier

**Methods:**
- `get_account_status($user_id)` - Get complete account status
- `is_pro_user($user_id)` - Check if user has active pro access
- `get_tier_features($tier)` - Get features for a specific tier
- `update_account_tier($user_id, $tier, $expiry_date)` - Update account tier
- `track_api_usage($user_id)` - Track API call usage
- `has_reached_api_limit($user_id)` - Check if API limit reached
- `reset_api_usage($user_id)` - Reset monthly usage counter

## Quick Start

### Check Your Account Status

**Method 1: Admin Dashboard**
1. Log into WordPress Admin
2. Go to **ICT Account** in the sidebar menu
3. View your account tier, pro status, and usage statistics

**Method 2: Shortcode (Frontend)**
Add this shortcode to any page or post:
```
[ict_account_status]
```

**Method 3: CLI Script**
```bash
php check-account-status.php [user_id]
```

**Method 4: Programmatically**
```php
$account = new ICT_Account();
$status = $account->get_account_status();

// Check if user has pro access
if ($account->is_pro_user()) {
    echo "You have Pro access!";
}
```

### Activate the Plugin
1. Go to WordPress Admin → Plugins
2. Find "Integrated Commerce Toolkit"
3. Click **Activate**

### Test WooCommerce Integration
```php
$api = new ICT_API();
$products = $api->get_woocommerce_products(10);
// Returns your WooCommerce products with caching
```

### Clear Cache for Testing
```php
$api = new ICT_API();
$api->clear_all_caches();
```

## Testing Guide

### Account Management Testing

**Check Account Status:**
```php
$account = new ICT_Account();
$status = $account->get_account_status();
print_r($status);
```

**Upgrade to Pro:**
```php
$account = new ICT_Account();
$user_id = get_current_user_id();
$account->update_account_tier($user_id, 'pro', date('Y-m-d', strtotime('+1 year')));
```

**Track API Usage:**
```php
$account = new ICT_Account();
$account->track_api_usage();
```

**Check if API Limit Reached:**
```php
$account = new ICT_Account();
if ($account->has_reached_api_limit()) {
    echo "API limit reached!";
}
```

### 1. Enable Debug Logging
Add to `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

### 2. Verify Plugin Loaded
Check that classes exist:
```php
if (class_exists('ICT_Main')) {
    echo "Plugin loaded!";
}
```

### 3. Test API Caching
```php
$api = new ICT_API();

// First call - fetches from API
$products = $api->get_woocommerce_products(5);

// Second call within 1 hour - returns cached data
$products = $api->get_woocommerce_products(5);

// Clear cache for testing
$api->clear_all_caches();
```

### 4. Test Database Operations
```php
$db = new ICT_DB();

// Get sync metadata
$meta = $db->get_product_sync_meta(1);

// Update sync status
$db->update_sync_status(1, 'synced');
```

### 5. Check Debug Log
```
/wp-content/debug.log
```

## Security

✅ **SQL Injection Prevention** - All queries use prepared statements  
✅ **CSRF Protection** - Nonce verification available  
✅ **XSS Prevention** - All input sanitized  
✅ **Direct Access Prevention** - ABSPATH check in all files  

## Account Tiers & Features

The plugin supports three account tiers with different feature sets:

### Free Tier
- 100 API calls per month
- Daily sync frequency
- 1 external API connection
- Standard support
- Basic caching

### Pro Tier
- 1,000 API calls per month
- Hourly sync frequency
- 5 external API connections
- Priority support
- Advanced caching

### Enterprise Tier
- Unlimited API calls
- Real-time sync frequency
- Unlimited external API connections
- Priority support
- Advanced caching

**To check your account status:**
- Visit the **ICT Account** page in WordPress Admin
- Use the `[ict_account_status]` shortcode on any page
- Run `php check-account-status.php` from the plugin directory

## Troubleshooting

**Plugin not appearing in admin?**
- Ensure main file has proper WordPress header
- Check plugin folder name matches plugin slug

**WooCommerce API errors?**
- Verify WooCommerce is active
- Check REST API is enabled
- Enable debug logging to see detailed errors

**Cache issues?**
```php
$api = new ICT_API();
$api->clear_all_caches();
```

## License

GPL-2.0-or-later

---

**Version:** 1.0.0  
**Status:** Production Ready  
**Last Updated:** December 28, 2025