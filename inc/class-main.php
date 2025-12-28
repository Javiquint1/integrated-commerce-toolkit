<?php
class ICT_Main {
    public function __construct() {
        $this->load_dependencies();
        
        // Example trigger: Fetch data when the plugin loads
        // In a real scenario, this might be triggered by a Cron Job or Admin Button
        $api = new ICT_API();
        $data = $api->get_external_commerce_data();
    }

    private function load_dependencies() {
        require_once ICT_PLUGIN_PATH . 'inc/class-security.php';
        require_once ICT_PLUGIN_PATH . 'inc/class-api.php';
        require_once ICT_PLUGIN_PATH . 'inc/class-db.php';
    }
}