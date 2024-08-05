<?php

function uft_add_admin_menu() {
    add_menu_page(
        'Feedback URL Tracker',
        'Feedback URLs',
        'manage_options',
        'uft-dashboard',
        'uft_dashboard_page',
        'dashicons-chart-line',
        6
    );
}
add_action('admin_menu', 'uft_add_admin_menu');

function uft_dashboard_page() {
    ?>
    <div class="wrap">
        <h1>Feedback URL Tracker Dashboard</h1>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Property</th>
                    <th>URLs</th>
                    <th>Total Hits</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $properties = get_posts(['post_type' => 'uft_property', 'numberposts' => -1]);
                foreach ($properties as $property) {
                    $urls = get_posts([
                        'post_type' => 'uft_url',
                        'meta_key' => '_uft_property_id',
                        'meta_value' => $property->ID,
                        'numberposts' => -1
                    ]);
                    $total_hits = 0;
                    foreach ($urls as $url) {
                        $total_hits += intval(get_post_meta($url->ID, '_uft_hit_count', true));
                    }
                    ?>
                    <tr>
                        <td><?php echo esc_html($property->post_title); ?></td>
                        <td><?php echo count($urls); ?></td>
                        <td><?php echo $total_hits; ?></td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php
}
