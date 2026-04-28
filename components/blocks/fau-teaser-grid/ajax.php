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

    // Strict allowlists — this is a public (nopriv) endpoint, so every
    // enum-like parameter must be validated against the values the block
    // legitimately emits. Anything else is rejected to prevent scraping
    // of non-targeted post types or expensive queries (DoS).
    $allowed_variants       = ['post', 'page'];
    $allowed_order_by       = ['date', 'title'];
    $allowed_order          = ['ASC', 'DESC'];
    $allowed_display_styles = ['teaser-grid', 'mini-list'];
    $allowed_teaser_layouts = ['1xl', '2l', 'l2s', '2sl', '3m', '2s-left', '2s-right'];
    $allowed_headings       = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];

    // Hard caps. The editor's RangeControl maxes postsPerPage at 12; allow a
    // little headroom but never the unbounded value absint() would accept.
    $max_posts_per_page = 24;
    $max_page           = 1000;

    $variant = sanitize_key($_POST['variant'] ?? 'post');
    if (!in_array($variant, $allowed_variants, true)) {
        $variant = 'post';
    }

    $posts_per_page = absint($_POST['posts_per_page'] ?? 3);
    if ($posts_per_page < 1) {
        $posts_per_page = 3;
    }
    if ($posts_per_page > $max_posts_per_page) {
        $posts_per_page = $max_posts_per_page;
    }

    $page = absint($_POST['page'] ?? 1);
    if ($page < 1) {
        $page = 1;
    }
    if ($page > $max_page) {
        $page = $max_page;
    }

    $selected_category = absint($_POST['selected_category'] ?? 0);
    $selected_author   = absint($_POST['selected_author'] ?? 0);
    $selected_year     = absint($_POST['selected_year'] ?? 0);
    $selected_month    = absint($_POST['selected_month'] ?? 0);
    $selected_day      = absint($_POST['selected_day'] ?? 0);

    $order_by = sanitize_key($_POST['order_by'] ?? 'date');
    if (!in_array($order_by, $allowed_order_by, true)) {
        $order_by = 'date';
    }

    $order = strtoupper(sanitize_key($_POST['order'] ?? 'DESC'));
    if (!in_array($order, $allowed_order, true)) {
        $order = 'DESC';
    }

    $display_style = sanitize_key($_POST['display_style'] ?? 'teaser-grid');
    if (!in_array($display_style, $allowed_display_styles, true)) {
        $display_style = 'teaser-grid';
    }

    $teaser_layout = sanitize_key($_POST['teaser_layout'] ?? '3m');
    if (!in_array($teaser_layout, $allowed_teaser_layouts, true)) {
        $teaser_layout = '3m';
    }

    $heading_level = sanitize_key($_POST['heading_level'] ?? 'h4');
    if (!in_array($heading_level, $allowed_headings, true)) {
        $heading_level = 'h4';
    }

    // Build query args
    $query_args = [
        'post_type' => $variant,
        'posts_per_page' => $posts_per_page,
        'paged' => $page,
        'orderby' => $order_by,
        'order' => $order,
        'post_status' => 'publish'
    ];

    if ($selected_category > 0) {
        $query_args['cat'] = $selected_category;
    }

    if ($selected_author > 0) {
        $query_args['author'] = $selected_author;
    }

    if ($selected_year > 0) {
        $query_args['year'] = $selected_year;
    }

    if ($selected_month > 0) {
        $query_args['monthnum'] = $selected_month;
    }

    if ($selected_day > 0) {
        $query_args['day'] = $selected_day;
    }

    // Add query optimization to prevent memory issues
    $query_args['no_found_rows'] = false; // We need found_posts for pagination
    $query_args['update_post_meta_cache'] = true;
    $query_args['update_post_term_cache'] = true;

    // Perform query
    $query = new WP_Query($query_args);
    
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
    $max_pages = 0;
    $found_posts = 0;
    
    if ($query->have_posts()) {
        $teaser_items = [];
        while ($query->have_posts()) {
            $query->the_post();
            $teaser_items[] = fau_elemental_render_teaser_item(get_post(), $variant, $grid_classes, $heading_level);
        }
        wp_reset_postdata();
        $html = fau_elemental_wrap_teaser_items($teaser_items, $teaser_layout);
        $max_pages = $query->max_num_pages;
        $found_posts = $query->found_posts;
    }

    // Clean up query object before sending response
    unset($query);

    $response = [
        'success' => true,
        'data' => [
            'html' => $html,
            'has_more' => $max_pages > $page,
            'current_page' => $page,
            'max_pages' => $max_pages,
            'found_posts' => $found_posts
        ]
    ];

    wp_send_json($response);
}

// AJAX handler for load more functionality
add_action('wp_ajax_fau_load_more_posts', 'fau_load_more_posts_handler');
add_action('wp_ajax_nopriv_fau_load_more_posts', 'fau_load_more_posts_handler');

 
