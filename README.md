# Integrated Commerce Toolkit 🚀

A high-performance WooCommerce integration engine designed to bridge the gap between external REST APIs and local commerce data. Built with a focus on security, scalability, and the WordPress "Senior" coding standards.

## 🏗 Architecture & Design
This plugin follows **Object-Oriented Programming (OOP)** principles to ensure a clean "Separation of Concerns." Logic is modularized into dedicated classes:
- **`ICT_API`**: Handles external communications using `wp_remote_get/post`.
- **`ICT_DB`**: Manages custom database interactions via `$wpdb`.
- **`ICT_Security`**: Centralizes Nonce verification and data hardening.

## 🔒 Security Hardening (InfoSec Focus)
- **SQLi Prevention:** 100% usage of `$wpdb->prepare()` for all custom database queries.
- **CSRF Protection:** Strict Nonce (Number used once) validation on all AJAX and Administrative actions.
- **Data Integrity:** Strict input sanitization (`sanitize_text_field`, `absint`) and output escaping (`esc_html`, `esc_attr`).

## ⚡ Performance Optimization
- **Transients API:** Implementation of the WordPress Transients API to cache expensive API responses, significantly reducing external latency.
- **Optimized Hooks:** Scripts and logic are conditionally loaded only where required to minimize the memory footprint.

## 🛠 Tech Stack
- **PHP:** 8.1+
- **APIs:** WooCommerce REST, WP-Cron, Transients API.
- **Environment:** LocalWP, WP-CLI, VS Code.

## 📦 Installation & Setup
1. Clone this repository into your `/wp-content/plugins/` directory.
2. Activate the plugin via WP-CLI:
   ```bash
   wp plugin activate integrated-commerce-toolkit
