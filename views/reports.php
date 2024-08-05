
<div class="wrap">
    <h1>URL Tracking Reports</h1>
    <form method="get">
        <input type="hidden" name="page" value="uft-reports">
        <select name="days">
            <option value="7">Last 7 days</option>
            <option value="30" selected>Last 30 days</option>
            <option value="90">Last 90 days</option>
        </select>
        <?php submit_button('Generate Report', 'secondary', 'submit', false); ?>
    </form>

    <?php
    $days = isset($_GET['days']) ? intval($_GET['days']) : 30;
    $stats = $this->get_url_stats($days);
    ?>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>URL</th>
                <th>Property</th>
                <th>Hit Count</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($stats as $stat): ?>
                <?php
                $url = get_post($stat->url_id);
                $property_id = get_post_meta($stat->url_id, '_uft_property_id', true);
                $property = get_post($property_id);
                ?>
                <tr>
                    <td><?php echo esc_html($url->post_title); ?></td>
                    <td><?php echo esc_html($property->post_title); ?></td>
                    <td><?php echo esc_html($stat->hit_count); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
