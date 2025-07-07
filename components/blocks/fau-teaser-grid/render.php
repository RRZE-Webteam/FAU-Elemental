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
if ( ! function_exists( 'render_block_fau_teaser_grid' ) ) {
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
    $filter_block_id = $attributes['filterBlockId'] ?? '';
    $pagination_block_id = $attributes['paginationBlockId'] ?? '';
    $custom_block_id = $attributes['customBlockId'] ?? '';
    

    
    // Fallback detection: Check if we're in a template that should use JavaScript pagination
    $template_file = function_exists('get_page_template_slug') ? get_page_template_slug() : ''; // phpcs:ignore
    $is_all_posts_template = (strpos($template_file, 'page-all-posts') !== false) || 
                            (function_exists('is_page') && is_page() && function_exists('get_page_template_slug') && get_page_template_slug() === 'page-all-posts.php'); // phpcs:ignore
    

    
    // Generate unique ID for this grid instance, or use custom ID if provided
    $grid_id = !empty($custom_block_id) ? $custom_block_id : 'fau-teaser-grid-' . uniqid();
    
    // Determine if we should use JavaScript-based pagination/filtering
    $has_filter_integration = !empty($filter_block_id);
    $has_pagination_integration = !empty($pagination_block_id);
    
    // Also check if filter or pagination blocks exist on the same page/post
    // This helps when blocks are manually added in the editor
    if (!$has_filter_integration || !$has_pagination_integration) {
        global $post;
        if ($post && function_exists('has_blocks') && has_blocks($post->post_content)) { // phpcs:ignore
            $blocks = function_exists('parse_blocks') ? parse_blocks($post->post_content) : []; // phpcs:ignore
            foreach ($blocks as $block) {
                if (!$has_filter_integration && $block['blockName'] === 'fau-elemental/fau-list-filters') {
                    $has_filter_integration = true;
                    // Try to get the filter block's ID if available
                    if (empty($filter_block_id) && !empty($block['attrs']['customBlockId'])) {
                        $filter_block_id = $block['attrs']['customBlockId'];
                    }
                }
                if (!$has_pagination_integration && $block['blockName'] === 'fau-elemental/fau-pagination') {
                    $has_pagination_integration = true;
                    // Try to get the pagination block's ID if available
                    if (empty($pagination_block_id) && !empty($block['attrs']['customBlockId'])) {
                        $pagination_block_id = $block['attrs']['customBlockId'];
                    }
                }
            }
        }
    }
    
    // Also enable JS pagination if we're in the all-posts template (fallback)
    $use_js_pagination = $has_filter_integration || $has_pagination_integration || $is_all_posts_template;
    

    
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
        'data-custom-block-id' => $custom_block_id,
        'data-variant' => $variant,
        'data-category' => $selected_category,
        'data-posts-per-page' => $posts_per_page,
        'data-display-style' => $display_style,
        'data-teaser-layout' => $teaser_layout,
        'data-order-by' => $order_by,
        'data-order' => $order,
        'data-heading-level' => $heading_level,
        'data-show-load-more' => $show_load_more ? 'true' : 'false',
        'data-nonce' => wp_create_nonce('fau_load_more_nonce'),
        'data-filterable' => 'true',
        'data-filter-block-id' => $filter_block_id,
        'data-pagination-block-id' => $pagination_block_id
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
        '<div class="%s" aria-label="%s" data-variant="%s" data-filter-block-id="%s" data-pagination-block-id="%s" data-js-pagination="%s">', 
        esc_attr(implode(' ', $grid_classes)),
        esc_attr__('Content items', 'fau-elemental'),
        esc_attr($variant),
        esc_attr($filter_block_id),
        esc_attr($pagination_block_id),
        esc_attr($use_js_pagination ? 'true' : 'false')
    );

    // Add CSS for JavaScript pagination if needed
    if ($use_js_pagination) {
        $output .= '<style>
            .fau-teaser-grid .js-paginated-item.hidden {
                display: none !important;
            }
            .fau-teaser-grid .js-paginated-item {
                transition: opacity 0.3s ease-in-out;
            }
            .fau-teaser-grid .js-paginated-hidden {
                display: none !important;
            }
        </style>';
        
        // Generate fallback IDs if they're empty
        if (empty($custom_block_id)) {
            $custom_block_id = 'fau-teaser-grid-' . uniqid();
            $grid_id = $custom_block_id;
        }
        if (empty($filter_block_id)) {
            $filter_block_id = 'fau-list-filters-' . uniqid();
        }
        if (empty($pagination_block_id)) {
            $pagination_block_id = 'fau-pagination-' . uniqid();
        }
    }

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
        // When integrated with filters or pagination blocks, load ALL posts and let JavaScript handle pagination
        if ($use_js_pagination) {
            // Load ALL posts for JavaScript-based filtering and pagination
            $args = [
                'post_type' => $variant,
                'posts_per_page' => -1, // Load all posts
                'orderby' => $order_by,
                'order' => $order,
            ];
            

        } else {
            // Use traditional server-side pagination
            $paged = $current_page;
            if ($paged <= 1) {
                $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
            }
            
            $args = [
                'post_type' => $variant,
                'posts_per_page' => $posts_per_page,
                'paged' => $paged,
                'orderby' => $order_by,
                'order' => $order,
            ];
            

        }

        if ($selected_category) {
            $args['cat'] = $selected_category;
        }

        $query = new WP_Query($args);

        if ($query->have_posts()) {
            $teaser_items = [];
            $item_index = 0;
            while ($query->have_posts()) {
                $query->the_post();
                $item_classes = [];
                
                // When using JavaScript pagination, don't add any pagination classes here
                // The JavaScript will handle showing/hiding items based on filters and pagination
                
                $teaser_items[] = fau_elemental_render_teaser_item(get_post(), $variant, $grid_classes, $heading_level);
                $item_index++;
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

    // Enqueue script for JavaScript pagination and load more functionality
    if ($use_js_pagination || $show_load_more) {
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
}

/**
 * Generates pagination HTML.
 *
 * @param int $current_page Current page number.
 * @param int $total_pages Total number of pages.
 * @return string The pagination HTML.
 */
if ( ! function_exists( 'fau_elemental_generate_pagination' ) ) {
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
}

echo render_block_fau_teaser_grid($attributes, $content, $block); 