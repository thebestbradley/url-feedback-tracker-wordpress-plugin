<?php
/*
Plugin Name: URL Feedback Tracker
Description: Tracks feedback via URL parameters and provides a thank you response.
Version: 1.1
Author: HypedUp Studios
*/

defined('ABSPATH') or die('No script kiddies please!');

register_activation_hook(__FILE__, 'uft_create_vote_page');
register_activation_hook(__FILE__, 'uft_create_vote_table');
register_deactivation_hook(__FILE__, 'uft_delete_vote_page');

// Create the thank-you page with embedded custom template content
function uft_create_vote_page() {
    $page_title = 'Thank You';
    $page_check = get_page_by_title($page_title);
    if (!isset($page_check->ID)) {
        $page = array(
            'post_type' => 'page',
            'post_title' => $page_title,
            'post_content' => '[uft_thank_you_page_content]',
            'post_status' => 'publish',
            'post_author' => 1,
        );
        wp_insert_post($page);
    }
}

// Create a database table to store votes and source URLs
function uft_create_vote_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'votes';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        vote varchar(3) NOT NULL,
        source_url varchar(255) DEFAULT '' NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Delete the thank-you page
function uft_delete_vote_page() {
    $page_title = 'Thank You';
    $page = get_page_by_title($page_title);
    if ($page) {
        wp_delete_post($page->ID, true);
    }
}

add_shortcode('uft_thank_you_page_content', 'uft_thank_you_page_content_func');
function uft_thank_you_page_content_func() {
    if (isset($_GET['source']) && filter_var($_GET['source'], FILTER_VALIDATE_URL)) {
        $source_url = esc_url($_GET['source']);
        return "<h1>Thank you for your feedback!</h1><p><a href='$source_url'>Click here to return home</a></p>";
    } else {
        return "<h1>Thank you for your feedback!</h1><p>There was an error with your submission.</p>";
    }
}

add_action('template_redirect', 'uft_handle_incoming_vote');
function uft_handle_incoming_vote() {
    if (isset($_GET['vote']) && in_array($_GET['vote'], ['yes', 'no'])) {
        $vote = $_GET['vote'];
        $source = isset($_GET['source']) ? esc_url_raw($_GET['source']) : '';

        global $wpdb;
        $table_name = $wpdb->prefix . 'votes';
        $wpdb->insert($table_name, [
            'vote' => $vote,
            'source_url' => $source
        ]);

        $thank_you_page = get_page_by_title('Thank You');
        if ($thank_you_page) {
            $redirect_url = add_query_arg(['source' => rawurlencode($source)], get_permalink($thank_you_page->ID));
            wp_redirect($redirect_url);
            exit;
        }
    }
}