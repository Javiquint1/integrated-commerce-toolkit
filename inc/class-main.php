<?php
class ICT_Main {
    public function __construct() {
        $this->load_dependencies();
    }

    private function load_dependencies() {
        require_once ICT_PLUGIN_PATH . 'inc/class-security.php';
        require_once ICT_PLUGIN_PATH . 'inc/class-api.php';
        require_once ICT_PLUGIN_PATH . 'inc/class-db.php';
    }
}