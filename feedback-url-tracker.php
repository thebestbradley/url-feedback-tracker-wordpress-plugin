<?php
/*
Plugin Name: URL Feedback Tracker
Description: Tracks feedback via URL parameters and provides a thank you response.
Version: 1.1
Author: HypedUp Studios
*/

/* global wp */

defined('ABSPATH') or die('No script kiddies please!');

register_activation_hook(__FILE__, 'uft_create_vote_page');
register_activation_hook(__FILE__, 'uft_create_vote_table');
register_deactivation_hook(__FILE__, 'uft_delete_vote_page');

error_log('Plugin File Path: ' . __FILE__);
error_log('Plugin Directory Path: ' . plugin_dir_path(__FILE__));
error_log('Attempted Include Path: ' . plugin_dir_path(__FILE__) . 'includes/class-uft-crud.php');

// Create the thank-you page with embedded custom template content
function uft_get_page_by_title($page_title) {
    $query = new WP_Query(
        array(
            'post_type'              => 'page',
            'title'                  => $page_title,
            'post_status'            => 'all',
            'posts_per_page'         => 1,
            'no_found_rows'          => true,
            'ignore_sticky_posts'    => true,
            'update_post_term_cache' => false,
            'update_post_meta_cache' => false,
            'orderby'                => 'post_date ID',
            'order'                  => 'ASC',
        )
    );

    if ( ! empty( $query->post ) ) {
        return $query->post;
    }

    return null;
}

// Create a database table to store votes and source URLs
function uft_create_vote_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'votes';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,d
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
    $page = uft_get_page_by_title($page_title);
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

        $thank_you_page = uft_get_page_by_title('Thank You');
        if ($thank_you_page) {
            $redirect_url = add_query_arg(['source' => rawurlencode($source)], get_permalink($thank_you_page->ID));
            wp_redirect($redirect_url);
            exit;
        }
    }
}

// Add a menu item to the WordPress admin menu / custtom post type
function uft_register_post_type() {
    $labels = array(
        'name'               => 'Feedback URLs',
        'singular_name'      => 'Feedback URL',
        'menu_name'          => 'Feedback URLs',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Feedback URL',
        'edit_item'          => 'Edit Feedback URL',
        'new_item'           => 'New Feedback URL',
        'view_item'          => 'View Feedback URL',
        'search_items'       => 'Search Feedback URLs',
        'not_found'          => 'No Feedback URLs found',
        'not_found_in_trash' => 'No Feedback URLs found in Trash',
    );

    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'query_var'           => true,
        'rewrite'             => array( 'slug' => 'feedback-url' ),
        'capability_type'     => 'post',
        'has_archive'         => true,
        'hierarchical'        => false,
        'menu_position'       => null,
        'supports'            => array( 'title', 'editor', 'author', 'custom-fields' ),
    );

    register_post_type( 'feedback_url', $args );
}
add_action( 'init', 'uft_register_post_type' );

function uft_register_post_types() {
    // Register Property post type
    register_post_type('uft_property', [
        'labels' => [
            'name' => 'Properties',
            'singular_name' => 'Property',
        ],
        'public' => true,
        'has_archive' => true,
        'hierarchical' => false,
        'supports' => ['title', 'editor'],
    ]);

    // Register URL post type
    register_post_type('uft_url', [
        'labels' => [
            'name' => 'URLs',
            'singular_name' => 'URL',
        ],
        'public' => true,
        'has_archive' => false,
        'hierarchical' => false,
        'supports' => ['title'],
    ]);
}
add_action('init', 'uft_register_post_types');

function uft_add_meta_boxes() {
    add_meta_box('uft_url_details', 'URL Details', 'uft_url_details_callback', 'uft_url', 'normal', 'high');
    add_meta_box('uft_property_stats', 'Property Statistics', 'uft_property_stats_callback', 'uft_property', 'normal', 'high');
}
add_action('add_meta_boxes', 'uft_add_meta_boxes');

function uft_url_details_callback($post) {
    wp_nonce_field('uft_save_meta_box_data', 'uft_meta_box_nonce');
    $target_url = get_post_meta($post->ID, '_uft_target_url', true);
    $hit_count = get_post_meta($post->ID, '_uft_hit_count', true);
    $property_id = get_post_meta($post->ID, '_uft_property_id', true);

    echo '<p><label for="uft_target_url">Target URL:</label>';
    echo '<input type="url" id="uft_target_url" name="uft_target_url" value="' . esc_attr($target_url) . '" size="25" /></p>';

    echo '<p><label for="uft_property_id">Property:</label>';
    wp_dropdown_pages([
        'post_type' => 'uft_property',
        'selected' => $property_id,
        'name' => 'uft_property_id',
        'show_option_none' => 'Select a property',
    ]);
    echo '</p>';

    echo '<p>Hit Count: ' . intval($hit_count) . '</p>';
}

function uft_property_stats_callback($post) {
    $urls = get_posts([
        'post_type' => 'uft_url',
        'meta_key' => '_uft_property_id',
        'meta_value' => $post->ID,
        'posts_per_page' => -1,
    ]);

    echo '<table>';
    echo '<tr><th>URL</th><th>Target</th><th>Hits</th></tr>';
    foreach ($urls as $url) {
        $target = get_post_meta($url->ID, '_uft_target_url', true);
        $hits = get_post_meta($url->ID, '_uft_hit_count', true);
        echo "<tr><td>{$url->post_title}</td><td>{$target}</td><td>{$hits}</td></tr>";
    }
    echo '</table>';
}

function uft_handle_incoming_request() {
    if (isset($_GET['uft_url'])) {
        $url_id = intval($_GET['uft_url']);
        $url_post = get_post($url_id);

        if ($url_post && $url_post->post_type === 'uft_url') {
            $target_url = get_post_meta($url_id, '_uft_target_url', true);
            $hit_count = get_post_meta($url_id, '_uft_hit_count', true);

            // Increment hit count
            $hit_count = $hit_count ? $hit_count + 1 : 1;
            update_post_meta($url_id, '_uft_hit_count', $hit_count);

            // Handle session tracking here if needed

            // Redirect to target URL
            wp_redirect($target_url);
            exit;
        }
    }
}
add_action('template_redirect', 'uft_handle_incoming_request');

function uft_generate_tracking_url($url_id) {
    return add_query_arg('uft_url', $url_id, home_url('/'));
}

// Include the CRUD functionality
require_once plugin_dir_path(__FILE__) . 'includes/class-uft-crud.php';

// Initialize the CRUD class
function uft_init_crud() {
    $crud = new UFT_CRUD();
    $crud->init();
}
add_action('init', 'uft_init_crud');

// Add this to your main plugin file (feedback-url-tracker.php)

function uft_tracking_link_shortcode($atts) {
    $atts = shortcode_atts(array(
        'id' => 0,
    ), $atts, 'uft_tracking_link');

    $url_id = intval($atts['id']);
    $url_post = get_post($url_id);

    if (!$url_post || $url_post->post_type !== 'uft_url') {
        return 'Invalid URL ID';
    }

    $target_url = get_post_meta($url_id, '_uft_target_url', true);
    $tracking_url = add_query_arg('uft_url', $url_id, home_url('/'));

    return '<a href="' . esc_url($tracking_url) . '" target="_blank">' . esc_html($url_post->post_title) . '</a>';
}
add_shortcode('uft_tracking_link', 'uft_tracking_link_shortcode');

require_once plugin_dir_path(__FILE__) . 'includes/class-uft-reports.php';

function uft_init_reports() {
    $reports = new UFT_Reports();
    $reports->init();
}
add_action('init', 'uft_init_reports');


// Views

function uft_dashboard_page() {
    include plugin_dir_path(__FILE__) . 'views/dashboard.php';
}
