# Integrated Commerce Toolkit (ICT)

A modular, enterprise-grade WordPress plugin framework for integrating external commerce services with secure practices, API caching, and prepared database queries.

## Features

✅ **Modular OOP Architecture** - Clean separation of concerns with dedicated classes  
✅ **Security-First Design** - Input sanitization, nonce verification, prepared statements  
✅ **Performance Optimization** - API response caching with WordPress transients  
✅ **WooCommerce Integration** - Fetch products from local WooCommerce REST API  
✅ **External API Support** - Easily extend to connect Shopify, custom APIs, etc.  
✅ **Database Safety** - All queries use prepared statements to prevent SQL injection  

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
│   └── class-security.php             # Input sanitization & CSRF protection
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

## Quick Start

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