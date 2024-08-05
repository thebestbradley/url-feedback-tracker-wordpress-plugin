<?php
class UFT_Reports {
    public function init() {
        add_action('admin_menu', array($this, 'add_reports_page'));
    }

    public function add_reports_page() {
        add_submenu_page(
            'uft-dashboard',
            'URL Reports',
            'Reports',
            'manage_options',
            'uft-reports',
            array($this, 'render_reports_page')
        );
    }

    public function render_reports_page() {
        include plugin_dir_path(__FILE__) . '../views/reports.php';
    }

    public function get_url_stats($days = 30) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'uft_hits';
        $stats = $wpdb->get_results($wpdb->prepare(
            "SELECT url_id, COUNT(*) as hit_count
            FROM $table_name
            WHERE hit_date >= DATE_SUB(CURDATE(), INTERVAL %d DAY)
            GROUP BY url_id",
            $days
        ));
        return $stats;
    }
}
