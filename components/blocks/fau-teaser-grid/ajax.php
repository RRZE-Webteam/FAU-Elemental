<?php
/**
 * AJAX handlers for the `fau-elemental/fau-teaser-grid` block.
 *
 * @package FAU_Elemental
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Include shared teaser item functions from components directory
require_once get_template_directory() . '/components/blocks/fau-teaser-grid/teaser-item.php';

function fau_load_more_posts_handler() {
    // Check if we have POST data
    if (empty($_POST)) {
        wp_send_json_error([
            'message' => __('No data received', 'fau-elemental'),
            'code' => 'no_data'
        ], 400);
        return;
    }
    
    // Verify nonce for security
    $nonce = $_POST['nonce'] ?? '';
    if (!wp_verify_nonce($nonce, 'fau_load_more_nonce')) {
        wp_send_json_error([
            'message' => __('Security check failed', 'fau-elemental'),
            'code' => 'nonce_failed'
        ], 403);
        return;
    }

    // Get parameters with better sanitization
    $variant = sanitize_text_field($_POST['variant'] ?? 'post');
    $posts_per_page = absint($_POST['posts_per_page'] ?? 3);
    $page = absint($_POST['page'] ?? 1);
    $category = absint($_POST['category'] ?? 0);
    $order_by = sanitize_text_field($_POST['order_by'] ?? 'date');
    $order = sanitize_text_field($_POST['order'] ?? 'DESC');
    $display_style = sanitize_text_field($_POST['display_style'] ?? 'teaser-grid');
    $teaser_layout = sanitize_text_field($_POST['teaser_layout'] ?? '3m');
    $heading_level = sanitize_text_field($_POST['heading_level'] ?? 'h4');

    // Validate heading level
    $allowed_headings = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
    if (!in_array($heading_level, $allowed_headings)) {
        $heading_level = 'h4';
    }

    // Build query args
    $args = [
        'post_type' => $variant,
        'posts_per_page' => $posts_per_page,
        'paged' => $page,
        'orderby' => $order_by,
        'order' => $order,
        'post_status' => 'publish'
    ];

    if ($category > 0) {
        $args['cat'] = $category;
    }

    // Perform query
    $query = new WP_Query($args);
    
    $grid_classes = ['fau-teaser-grid', $display_style];
    if ($display_style === 'teaser-grid') {
        if ($teaser_layout === '2s-left' || $teaser_layout === '2s-right') {
            $grid_classes[] = 'layout-2s';
            $grid_classes[] = "layout-{$teaser_layout}";
        } else {
            $grid_classes[] = "layout-{$teaser_layout}";
        }
    } elseif ($display_style === 'mini-list') {
        $grid_classes[] = 'style-mini-list';
    }

    $html = '';
    
    if ($query->have_posts()) {
        $teaser_items = [];
        while ($query->have_posts()) {
            $query->the_post();
            $teaser_items[] = fau_elemental_render_teaser_item(get_post(), $variant, $grid_classes, $heading_level);
        }
        wp_reset_postdata();
        $html = fau_elemental_wrap_teaser_items($teaser_items, $teaser_layout);
    }

    $response = [
        'success' => true,
        'data' => [
            'html' => $html,
            'has_more' => $query->max_num_pages > $page,
            'current_page' => $page,
            'max_pages' => $query->max_num_pages,
            'found_posts' => $query->found_posts
        ]
    ];

    wp_send_json($response);
}

// AJAX handler for load more functionality
add_action('wp_ajax_fau_load_more_posts', 'fau_load_more_posts_handler');
add_action('wp_ajax_nopriv_fau_load_more_posts', 'fau_load_more_posts_handler');

 
