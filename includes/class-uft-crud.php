<?php
class UFT_CRUD {
    public function init() {
        add_action('admin_init', array($this, 'handle_actions'));
        add_action('admin_menu', array($this, 'add_submenu_pages'));
    }

    public function add_submenu_pages() {
        add_submenu_page(
            'uft-dashboard',
            'Add New Property',
            'Add New Property',
            'manage_options',
            'uft-add-property',
            array($this, 'render_add_property_page')
        );
        add_submenu_page(
            'uft-dashboard',
            'Add New URL',
            'Add New URL',
            'manage_options',
            'uft-add-url',
            array($this, 'render_add_url_page')
        );
    }

    public function render_add_property_page() {
        include plugin_dir_path(__FILE__) . '../views/add-property.php';
    }

    public function render_add_url_page() {
        include plugin_dir_path(__FILE__) . '../views/add-url.php';
    }

    public function handle_actions() {
        if (isset($_POST['uft_action'])) {
            switch ($_POST['uft_action']) {
                case 'add_property':
                    $this->create_property();
                    break;
                case 'add_url':
                    $this->create_url();
                    break;
                // Add cases for update and delete actions
            }
        }
    }

    private function create_property() {
        // Implement property creation logic
    }

    private function create_url() {
        // Implement URL creation logic
    }

    // Add methods for update and delete functionality
}
