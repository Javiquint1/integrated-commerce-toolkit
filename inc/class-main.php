<?php
class ICT_Main {
    public function __construct() {
        $this->load_dependencies();
        add_action('init', array($this, 'run_sync_logic'));
    }

    private function load_dependencies() {
        require_once ICT_PLUGIN_PATH . 'inc/class-security.php';
        require_once ICT_PLUGIN_PATH . 'inc/class-api.php';
        require_once ICT_PLUGIN_PATH . 'inc/class-db.php';
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
}