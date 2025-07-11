<?php
/**
 * AJAX handlers for FAU Elemental theme blocks
 *
 * @package FAU_Elemental
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Add AJAX handler for load more functionality
add_action('wp_ajax_fau_load_more_content', 'fau_elemental_ajax_load_more_content');
add_action('wp_ajax_nopriv_fau_load_more_content', 'fau_elemental_ajax_load_more_content');

/**
 * Generic AJAX handler for load more functionality.
 * Works with different block types by calling specific render functions.
 */
function fau_elemental_ajax_load_more_content() {
    // Verify nonce for security
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'fau_load_more_nonce')) {
        wp_die('Security check failed');
    }

    // Get the request data
    $block_type = sanitize_text_field($_POST['blockType'] ?? '');
    $page = intval($_POST['page'] ?? 1);
    $attributes = isset($_POST['attributes']) ? json_decode(stripslashes($_POST['attributes']), true) : [];
    
    // Check if attributes were decoded properly
    if (empty($attributes)) {
        wp_die('No attributes available');
    }
    
    $output = '';
    
    // Handle different block types
    switch ($block_type) {
        case 'fau-teaser-grid':
            $output = fau_elemental_render_teaser_grid_content($attributes, $page);
            break;
        case 'fau-facts-grid':
            // Add facts grid handler when needed
            $output = fau_elemental_render_facts_grid_content($attributes, $page);
            break;
        default:
            wp_die('Unknown block type');
    }
    
    // Return the content
    echo $output;
    wp_die();
}

/**
 * Renders teaser grid content for AJAX load more.
 *
 * @param array $attributes Block attributes.
 * @param int   $page       Page number to load.
 * @return string           The rendered teaser items HTML.
 */
function fau_elemental_render_teaser_grid_content($attributes, $page) {
    // Extract attributes
    $variant = $attributes['variant'] ?? 'post';
    $posts_per_page = $attributes['postsPerPage'] ?? 6;
    $selected_category = $attributes['category'] ?? 0;
    $order_by = $attributes['orderBy'] ?? 'date';
    $order = $attributes['order'] ?? 'DESC';
    $heading_level = $attributes['headingLevel'] ?? 'h4';
    $teaser_layout = $attributes['teaserLayout'] ?? '3m';
    $display_style = $attributes['displayStyle'] ?? 'teaser-grid';
    
    // Build grid classes
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
    
    // Query for posts
    $args = [
        'post_type' => $variant,
        'posts_per_page' => $posts_per_page,
        'paged' => $page,
        'orderby' => $order_by,
        'order' => $order,
    ];

    if ($selected_category) {
        $args['cat'] = $selected_category;
    }

    $query = new WP_Query($args);
    
    $teaser_items = [];
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $teaser_items[] = fau_elemental_render_teaser_item(get_post(), $variant, $grid_classes, $heading_level);
        }
        wp_reset_postdata();
    }
    
    $result = fau_elemental_wrap_teaser_items($teaser_items, $teaser_layout);
    
    return $result;
}

/**
 * Renders facts grid content for AJAX load more.
 * Placeholder for future facts grid implementation.
 *
 * @param array $attributes Block attributes.
 * @param int   $page       Page number to load.
 * @return string           The rendered facts items HTML.
 */
function fau_elemental_render_facts_grid_content($attributes, $page) {
    // Placeholder for facts grid implementation
    return '';
} 