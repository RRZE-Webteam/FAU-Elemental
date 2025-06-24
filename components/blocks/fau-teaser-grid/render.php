<?php
/**
 * Server-side rendering of the `fau-elemental/fau-teaser-grid` block.
 *
 * @package FAU_Elemental
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Include shared teaser item functions from components directory
require_once get_template_directory() . '/components/blocks/fau-teaser-grid/teaser-item.php';

/**
 * Renders the `fau-elemental/fau-teaser-grid` block on the server.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block default content.
 * @param WP_Block $block      Block instance.
 * @return string Returns the post content with the teaser grid.
 */
function render_block_fau_teaser_grid( $attributes, $content, $block ) {
    $variant = $attributes['variant'] ?? 'post';
    $selection_mode = $attributes['selectionMode'] ?? 'auto';
    $selected_posts = $attributes['selectedPosts'] ?? [];
    $display_style = $attributes['displayStyle'] ?? 'teaser-grid';
    $teaser_layout = $attributes['teaserLayout'] ?? '3m';
    $current_page = $attributes['currentPage'] ?? 1;
    $posts_per_page = $attributes['postsPerPage'] ?? 15;
    $selected_category = $attributes['selectedCategory'] ?? 0;
    $order_by = $attributes['orderBy'] ?? 'date';
    $order = $attributes['order'] ?? 'DESC';
    $heading_level = $attributes['headingLevel'] ?? 'h4';
    $show_load_more = $attributes['showLoadMore'] ?? false;
    
    // Generate unique ID for this grid instance
    $grid_id = 'fau-teaser-grid-' . uniqid();
    
    // Ensure it's a valid heading tag
    $allowed_headings = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
    if (!in_array($heading_level, $allowed_headings)) {
        $heading_level = 'h4'; // Default to h4 if not valid
    }
    
    // Start building the output
    $wrapper_classes = ['fau-list-item'];
    if ($show_load_more) {
        $wrapper_classes[] = 'has-load-more';
    }
    
    $wrapper_attributes = get_block_wrapper_attributes([
        'class' => implode(' ', $wrapper_classes),
        'role' => 'region',
        'aria-label' => __('Content grid', 'fau-elemental'),
        'id' => $grid_id,
        'data-grid-id' => $grid_id,
        'data-variant' => $variant,
        'data-category' => $selected_category,
        'data-posts-per-page' => $posts_per_page,
        'data-display-style' => $display_style,
        'data-teaser-layout' => $teaser_layout,
        'data-order-by' => $order_by,
        'data-order' => $order,
        'data-heading-level' => $heading_level,
        'data-show-load-more' => $show_load_more ? 'true' : 'false',
        'data-nonce' => wp_create_nonce('fau_load_more_nonce')
    ]);

    $grid_classes = ['fau-teaser-grid', $display_style];
    if ($display_style === 'teaser-grid') {
        // Handle special cases for 2s-left and 2s-right layouts
        if ($teaser_layout === '2s-left' || $teaser_layout === '2s-right') {
            $grid_classes[] = 'layout-2s';
            $grid_classes[] = "layout-{$teaser_layout}";
        } else {
            $grid_classes[] = "layout-{$teaser_layout}";
        }
    } elseif ($display_style === 'mini-list') {
        $grid_classes[] = 'style-mini-list';
    }

    $output = sprintf('<section %s>', $wrapper_attributes);
    
    $output .= sprintf(
        '<div class="%s" aria-label="%s" data-variant="%s">', 
        esc_attr(implode(' ', $grid_classes)),
        esc_attr__('Content items', 'fau-elemental'),
        esc_attr($variant)
    );

    if ($selection_mode === 'manual' && !empty($selected_posts)) {
        // Handle manually selected posts
        $teaser_items = [];
        foreach ($selected_posts as $selected_post) {
            $post = get_post($selected_post['id']);
            if ($post) {
                $teaser_items[] = fau_elemental_render_teaser_item($post, $variant, $grid_classes, $heading_level);
            }
        }
        $output .= fau_elemental_wrap_teaser_items($teaser_items, $teaser_layout);
    } else {
        // Handle automatic posts
        $args = [
            'post_type' => $variant,
            'posts_per_page' => $posts_per_page,
            'paged' => 1, // Always start with page 1 for load more
            'orderby' => $order_by,
            'order' => $order,
        ];

        if ($selected_category) {
            $args['cat'] = $selected_category;
        }

        $query = new WP_Query($args);

        if ($query->have_posts()) {
            $teaser_items = [];
            while ($query->have_posts()) {
                $query->the_post();
                $teaser_items[] = fau_elemental_render_teaser_item(get_post(), $variant, $grid_classes, $heading_level);
            }
            wp_reset_postdata();
            $output .= fau_elemental_wrap_teaser_items($teaser_items, $teaser_layout);
        } else {
            $output .= sprintf(
                '<p role="status" class="no-items-found">%s</p>',
                esc_html__('No items found', 'fau-elemental')
            );
        }
    }

    $output .= '</div>'; // Close teaser grid
    
    // Add Load More button outside the grid if enabled and there are more posts
    if ($selection_mode === 'auto' && $show_load_more && isset($query) && $query->max_num_pages > 1) {
        $output .= '<div class="fau-teaser-grid__load-more-wrapper">';
        $output .= '<div class="wp-block-button">';
        $output .= '<button class="wp-block-button__link wp-element-button fau-teaser-grid__load-more-button" ';
        $output .= 'data-page="1" data-max-pages="' . esc_attr($query->max_num_pages) . '" ';
        $output .= 'aria-label="' . esc_attr__('Load more posts', 'fau-elemental') . '">';
        $output .= __('Load More', 'fau-elemental');
        $output .= '</button>';
        $output .= '</div>';
        $output .= '<div class="load-more-spinner" role="status" aria-live="polite">';
        $output .= '<span class="loading-text">' . esc_html__('Loading...', 'fau-elemental') . '</span>';
        $output .= '</div>';
        $output .= '</div>';
    }
    
    $output .= '</section>'; // Close fau-list-item section

    // Enqueue and localize script for load more functionality
    if ($show_load_more) {
        // Only enqueue if not already enqueued
        if (!wp_script_is('fau-teaser-grid-view', 'enqueued')) {
            wp_enqueue_script('fau-teaser-grid-view', get_template_directory_uri() . '/build/blocks/fau-teaser-grid/view.js', [], '1.0.0', true);
            wp_localize_script('fau-teaser-grid-view', 'fauTeaserGrid', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('fau_load_more_nonce'),
            ]);
        }
    }

    return $output;
}

/**
 * Generates pagination HTML.
 *
 * @param int $current_page Current page number.
 * @param int $total_pages Total number of pages.
 * @return string The pagination HTML.
 */
function fau_elemental_generate_pagination($current_page, $total_pages) {
    $output = '<div class="pagination">';

    // Previous button
    $prev_disabled = $current_page === 1 ? ' disabled' : '';
    $prev_url = $current_page > 1 ? add_query_arg('page', $current_page - 1) : '#';
    $output .= sprintf(
        '<a href="%s" class="page-number prev%s"%s>Prev</a>',
        esc_url($prev_url),
        $prev_disabled,
        $prev_disabled ? ' disabled' : ''
    );

    // Page numbers
    for ($i = 1; $i <= $total_pages; $i++) {
        if ($i === 1 || $i === $total_pages || ($i >= $current_page - 1 && $i <= $current_page + 1)) {
            $active = $current_page === $i ? ' active' : '';
            $page_url = add_query_arg('page', $i);
            $output .= sprintf(
                '<a href="%s" class="page-number%s">%d</a>',
                esc_url($page_url),
                $active,
                $i
            );
        } elseif ($i === $current_page - 2 || $i === $current_page + 2) {
            $output .= '<span class="page-ellipsis">...</span>';
        }
    }

    // Next button
    $next_disabled = $current_page === $total_pages ? ' disabled' : '';
    $next_url = $current_page < $total_pages ? add_query_arg('page', $current_page + 1) : '#';
    $output .= sprintf(
        '<a href="%s" class="page-number next%s"%s>Next</a>',
        esc_url($next_url),
        $next_disabled,
        $next_disabled ? ' disabled' : ''
    );

    $output .= '</div>';
    return $output;
}

echo render_block_fau_teaser_grid($attributes, $content, $block); 