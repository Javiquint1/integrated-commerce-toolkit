<?php
class ICT_Main {
    public function __construct() {
        $this->load_dependencies();
        add_action('init', array($this, 'run_sync_logic'));
        add_action('init', array($this, 'register_shortcodes'));
        add_action('admin_menu', array($this, 'add_account_status_page'));
    }

    private function load_dependencies() {
        require_once ICT_PLUGIN_PATH . 'inc/class-security.php';
        require_once ICT_PLUGIN_PATH . 'inc/class-api.php';
        require_once ICT_PLUGIN_PATH . 'inc/class-db.php';
        require_once ICT_PLUGIN_PATH . 'inc/class-account.php';
    }

    public function run_sync_logic() {
        // Only run this if we are in the admin for testing purposes
        if (!is_admin()) return;

        $api = new ICT_API();
        $db  = new ICT_DB();

        // 1. Get Data from API (Cached)
        $data = $api->get_external_commerce_data();

        // 2. Logic: If we have data, log a sync time in the DB
        if (!empty($data)) {
            // Assume we are syncing product ID 1 for the demo
            $db->update_sync_status(1, 'success');
        }
    }

    /**
     * Register shortcodes for account status display
     */
    public function register_shortcodes() {
        add_shortcode('ict_account_status', array($this, 'account_status_shortcode'));
    }

    /**
     * Shortcode to display account status
     * Usage: [ict_account_status]
     */
    public function account_status_shortcode($atts) {
        if (!is_user_logged_in()) {
            return '<p>' . __('Please log in to view your account status.', 'integrated-commerce-toolkit') . '</p>';
        }

        $account = new ICT_Account();
        $status = $account->get_account_status();

        ob_start();
        ?>
        <div class="ict-account-status">
            <h3><?php _e('Account Status', 'integrated-commerce-toolkit'); ?></h3>
            <table class="ict-status-table">
                <tr>
                    <th><?php _e('Account Tier:', 'integrated-commerce-toolkit'); ?></th>
                    <td><strong><?php echo esc_html(ucfirst($status['tier'])); ?></strong></td>
                </tr>
                <tr>
                    <th><?php _e('Pro Status:', 'integrated-commerce-toolkit'); ?></th>
                    <td><?php echo $status['is_pro'] ? '<span style="color: green;">✓ Active</span>' : '<span style="color: red;">✗ Inactive</span>'; ?></td>
                </tr>
                <?php if (!empty($status['expiry_date'])): ?>
                <tr>
                    <th><?php _e('Expires:', 'integrated-commerce-toolkit'); ?></th>
                    <td><?php echo esc_html($status['expiry_date']); ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th><?php _e('API Calls Used:', 'integrated-commerce-toolkit'); ?></th>
                    <td><?php echo esc_html($status['api_calls_count']); ?> / <?php echo esc_html($status['features']['api_calls_limit']); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Last Sync:', 'integrated-commerce-toolkit'); ?></th>
                    <td><?php echo $status['last_sync'] ? esc_html($status['last_sync']) : __('Never', 'integrated-commerce-toolkit'); ?></td>
                </tr>
            </table>
            <h4><?php _e('Available Features:', 'integrated-commerce-toolkit'); ?></h4>
            <ul class="ict-features-list">
                <li><?php printf(__('Sync Frequency: %s', 'integrated-commerce-toolkit'), esc_html($status['features']['sync_frequency'])); ?></li>
                <li><?php printf(__('External APIs: %s', 'integrated-commerce-toolkit'), esc_html($status['features']['external_apis'])); ?></li>
                <li><?php printf(__('Priority Support: %s', 'integrated-commerce-toolkit'), $status['features']['priority_support'] ? __('Yes', 'integrated-commerce-toolkit') : __('No', 'integrated-commerce-toolkit')); ?></li>
                <li><?php printf(__('Advanced Caching: %s', 'integrated-commerce-toolkit'), $status['features']['advanced_caching'] ? __('Yes', 'integrated-commerce-toolkit') : __('No', 'integrated-commerce-toolkit')); ?></li>
            </ul>
        </div>
        <style>
            .ict-account-status { padding: 20px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 5px; }
            .ict-status-table { width: 100%; margin: 15px 0; }
            .ict-status-table th { text-align: left; padding: 8px; width: 40%; }
            .ict-status-table td { padding: 8px; }
            .ict-features-list { list-style: disc; margin-left: 20px; }
        </style>
        <?php
        return ob_get_clean();
    }

    /**
     * Add admin menu page for account status
     */
    public function add_account_status_page() {
        add_menu_page(
            __('ICT Account Status', 'integrated-commerce-toolkit'),
            __('ICT Account', 'integrated-commerce-toolkit'),
            'read',
            'ict-account-status',
            array($this, 'render_account_status_page'),
            'dashicons-businessman',
            30
        );
    }

    /**
     * Render the account status admin page
     */
    public function render_account_status_page() {
        if (!current_user_can('read')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'integrated-commerce-toolkit'));
        }

        $account = new ICT_Account();
        $status = $account->get_account_status();
        ?>
        <div class="wrap">
            <h1><?php _e('Integrated Commerce Toolkit - Account Status', 'integrated-commerce-toolkit'); ?></h1>
            
            <div class="ict-admin-account-status">
                <h2><?php _e('Your Account Information', 'integrated-commerce-toolkit'); ?></h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('User ID', 'integrated-commerce-toolkit'); ?></th>
                        <td><?php echo esc_html($status['user_id']); ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Account Tier', 'integrated-commerce-toolkit'); ?></th>
                        <td>
                            <strong style="font-size: 16px;"><?php echo esc_html(strtoupper($status['tier'])); ?></strong>
                            <?php if ($status['is_pro']): ?>
                                <span class="dashicons dashicons-yes-alt" style="color: green;"></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Pro Status', 'integrated-commerce-toolkit'); ?></th>
                        <td>
                            <?php if ($status['is_pro']): ?>
                                <span style="color: green; font-weight: bold;">✓ <?php _e('Active', 'integrated-commerce-toolkit'); ?></span>
                            <?php else: ?>
                                <span style="color: red;">✗ <?php _e('Inactive', 'integrated-commerce-toolkit'); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php if (!empty($status['expiry_date'])): ?>
                    <tr>
                        <th scope="row"><?php _e('Subscription Expires', 'integrated-commerce-toolkit'); ?></th>
                        <td><?php echo esc_html(date('F j, Y', strtotime($status['expiry_date']))); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <th scope="row"><?php _e('API Usage', 'integrated-commerce-toolkit'); ?></th>
                        <td>
                            <?php 
                            $limit = $status['features']['api_calls_limit'];
                            $used = $status['api_calls_count'];
                            if ($limit === 'unlimited') {
                                echo sprintf(__('%d calls (Unlimited)', 'integrated-commerce-toolkit'), $used);
                            } else {
                                $percentage = $limit > 0 ? ($used / $limit) * 100 : 0;
                                echo sprintf(__('%d / %d calls (%d%%)', 'integrated-commerce-toolkit'), $used, $limit, $percentage);
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Last Sync', 'integrated-commerce-toolkit'); ?></th>
                        <td>
                            <?php 
                            if ($status['last_sync']) {
                                echo esc_html(date('F j, Y g:i a', strtotime($status['last_sync'])));
                            } else {
                                _e('Never', 'integrated-commerce-toolkit');
                            }
                            ?>
                        </td>
                    </tr>
                </table>

                <h2><?php _e('Plan Features', 'integrated-commerce-toolkit'); ?></h2>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th><?php _e('Feature', 'integrated-commerce-toolkit'); ?></th>
                            <th><?php _e('Your Plan', 'integrated-commerce-toolkit'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php _e('API Calls per Month', 'integrated-commerce-toolkit'); ?></td>
                            <td><strong><?php echo esc_html($status['features']['api_calls_limit']); ?></strong></td>
                        </tr>
                        <tr>
                            <td><?php _e('Sync Frequency', 'integrated-commerce-toolkit'); ?></td>
                            <td><strong><?php echo esc_html(ucfirst($status['features']['sync_frequency'])); ?></strong></td>
                        </tr>
                        <tr>
                            <td><?php _e('External API Connections', 'integrated-commerce-toolkit'); ?></td>
                            <td><strong><?php echo esc_html($status['features']['external_apis']); ?></strong></td>
                        </tr>
                        <tr>
                            <td><?php _e('Priority Support', 'integrated-commerce-toolkit'); ?></td>
                            <td>
                                <?php if ($status['features']['priority_support']): ?>
                                    <span style="color: green;">✓ <?php _e('Included', 'integrated-commerce-toolkit'); ?></span>
                                <?php else: ?>
                                    <span style="color: #999;">✗ <?php _e('Not Available', 'integrated-commerce-toolkit'); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><?php _e('Advanced Caching', 'integrated-commerce-toolkit'); ?></td>
                            <td>
                                <?php if ($status['features']['advanced_caching']): ?>
                                    <span style="color: green;">✓ <?php _e('Enabled', 'integrated-commerce-toolkit'); ?></span>
                                <?php else: ?>
                                    <span style="color: #999;">✗ <?php _e('Not Available', 'integrated-commerce-toolkit'); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <?php if (!$status['is_pro']): ?>
                <div class="notice notice-info" style="margin-top: 20px;">
                    <p><strong><?php _e('Upgrade to Pro', 'integrated-commerce-toolkit'); ?></strong></p>
                    <p><?php _e('Get access to more API calls, faster sync frequency, and priority support.', 'integrated-commerce-toolkit'); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}