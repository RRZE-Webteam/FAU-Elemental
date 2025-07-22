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
    // Extract attributes with new pagination integration
    $variant = $attributes['variant'] ?? 'post';
    $selection_mode = $attributes['selectionMode'] ?? 'auto';
    $selected_posts = $attributes['selectedPosts'] ?? [];
    $display_style = $attributes['displayStyle'] ?? 'teaser-grid';
    $teaser_layout = $attributes['teaserLayout'] ?? '3m';
    $current_page = $attributes['currentPage'] ?? 1;
    $posts_per_page = $attributes['postsPerPage'] ?? 6;
    $selected_category = $attributes['selectedCategory'] ?? 0;
    $selected_tags = $attributes['selectedTags'] ?? [];
    $order_by = $attributes['orderBy'] ?? 'date';
    $order = $attributes['order'] ?? 'DESC';
    $heading_level = $attributes['headingLevel'] ?? 'h4';

    $show_pagination = $attributes['showPagination'] ?? true;
    $pagination_type = $attributes['paginationType'] ?? 'numbers';
    $custom_block_id = $attributes['customBlockId'] ?? '';

    // Generate unique ID for this grid instance
    $grid_id = !empty($custom_block_id) ? $custom_block_id : 'fau-teaser-grid-' . uniqid();

    // Ensure it's a valid heading tag
    $allowed_headings = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
    if (!in_array($heading_level, $allowed_headings)) {
        $heading_level = 'h4';
    }

    // Get current page from URL parameters
    $url_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    if ($url_page === 1) {
        $url_page = get_query_var('paged') ? get_query_var('paged') : 1;
    }
    $current_page = max(1, $url_page);

    // Start building the output
    $wrapper_classes = ['fau-list-item'];
    if ($show_pagination && $pagination_type === 'load-more') {
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
        'data-tags' => !empty($selected_tags) ? implode(',', $selected_tags) : '',
        'data-posts-per-page' => $posts_per_page,
        'data-display-style' => $display_style,
        'data-teaser-layout' => $teaser_layout,
        'data-order-by' => $order_by,
        'data-order' => $order,
        'data-heading-level' => $heading_level,

        'data-show-pagination' => $show_pagination ? 'true' : 'false',
        'data-pagination-type' => $pagination_type,
        'data-nonce' => wp_create_nonce('fau_load_more_nonce'),
        'data-current-page' => $current_page
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

    // Generate teaser items
    $teaser_items = [];
    $total_posts = 0;
    
    if ($selection_mode === 'manual' && !empty($selected_posts)) {
        // Handle manually selected posts
        foreach ($selected_posts as $selected_post) {
            $post = get_post($selected_post['id']);
            if ($post) {
                $variant = $post->post_type === 'post' ? 'post' : 'page';
                $grid_classes = [$teaser_layout];
                $teaser_items[] = fau_elemental_render_teaser_item($post, $variant, $grid_classes, $heading_level);
            }
        }
        $total_posts = count($teaser_items);
    } else {
        // Handle automatic posts selection
        $query_args = [
            'post_type' => $variant,
            'post_status' => 'publish',
            'orderby' => $order_by,
            'order' => $order,
            'posts_per_page' => $posts_per_page,
            'paged' => $current_page,
        ];

        if ($selected_category > 0) {
            $query_args['cat'] = $selected_category;
        }

        if (!empty($selected_tags)) {
            $query_args['tag__in'] = $selected_tags;
        }

        $query = new WP_Query($query_args);
        $total_posts = $query->found_posts;

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post = get_post();
                $variant = $post->post_type === 'post' ? 'post' : 'page';
                $grid_classes = [$teaser_layout];
                $teaser_items[] = fau_elemental_render_teaser_item($post, $variant, $grid_classes, $heading_level);
            }
            wp_reset_postdata();
        }
    }

    // Output teaser items
    if (!empty($teaser_items)) {
        // Show paginated items
        $items_to_show = array_slice($teaser_items, 0, $posts_per_page);
        $output .= implode('', $items_to_show);
    } else {
        $output .= '<p class="no-posts">' . __('No items found.', 'fau-elemental') . '</p>';
    }

    $output .= '</div>'; // Close fau-teaser-grid

    // Add pagination if enabled
    if ($show_pagination && $total_posts > $posts_per_page) {
        $total_pages = ceil($total_posts / $posts_per_page);
        
        if ($pagination_type === 'load-more') {
            $output .= fau_elemental_generate_load_more($current_page, $total_pages, $grid_id);
        } else {
            $output .= fau_elemental_generate_pagination($current_page, $total_pages, $pagination_type);
        }
    }

    $output .= '</section>'; // Close wrapper

    // Enqueue JavaScript if needed
    if ($show_pagination && $pagination_type === 'load-more') {
        if (!wp_script_is('fau-teaser-grid-view', 'enqueued')) {
            wp_enqueue_script('fau-teaser-grid-view', get_template_directory_uri() . '/build/blocks/fau-teaser-grid/view.js', [], '1.0.0-' . time(), true);
            wp_localize_script('fau-teaser-grid-view', 'fauTeaserGrid', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('fau_load_more_nonce'),
                'strings' => [
                    'errorLoadingPosts' => __('Error loading posts', 'fau-elemental'),
                    'loadingError' => __('Loading error', 'fau-elemental'),
                ],
            ]);
        }
    }

    return $output;
}
}

/**
 * Generates smart pagination HTML with first 3 ... last 3 pattern for >7 pages.
 *
 * @param int $current_page Current page number.
 * @param int $total_pages Total number of pages.
 * @param string $pagination_type Type of pagination ('numbers' or 'simple').
 * @return string The pagination HTML.
 */
if ( ! function_exists( 'fau_elemental_generate_pagination' ) ) {
function fau_elemental_generate_pagination($current_page, $total_pages, $pagination_type = 'numbers') {
    if ($total_pages <= 1) {
        return '';
    }

    $output = '<nav class="fau-pagination" role="navigation" aria-label="' . esc_attr__('Posts pagination', 'fau-elemental') . '">';
    $output .= '<div class="pagination-wrapper">';

    // Previous button
    if ($current_page > 1) {
        $prev_url = get_pagenum_link($current_page - 1);
        $output .= sprintf(
            '<a href="%s" class="page-number prev" aria-label="%s"><span class="pagination-icon pagination-icon-prev"></span></a>',
            esc_url($prev_url),
            esc_attr__('Previous page', 'fau-elemental')
        );
    } else {
        $output .= '<span class="page-number prev disabled" aria-disabled="true" aria-label="' . esc_attr__('Previous page', 'fau-elemental') . '"><span class="pagination-icon pagination-icon-prev"></span></span>';
    }

    if ($pagination_type === 'numbers') {
        // Smart pagination logic
        if ($total_pages <= 7) {
            // Show all pages if 7 or fewer
            for ($i = 1; $i <= $total_pages; $i++) {
                if ($i === $current_page) {
                    $output .= sprintf(
                        '<span class="page-number current" aria-current="page">%d</span>',
                        $i
                    );
                } else {
                    $page_url = get_pagenum_link($i);
                    $output .= sprintf(
                        '<a href="%s" class="page-number" aria-label="%s">%d</a>',
                        esc_url($page_url),
                        // translators: page number
                        esc_attr(sprintf(__('Page %d', 'fau-elemental'), $i)),
                        $i
                    );
                }
            }
        } else {
            // Show first 3 ... last 3 pattern
            
            // First 3 pages
            for ($i = 1; $i <= 3; $i++) {
                if ($i === $current_page) {
                    $output .= sprintf(
                        '<span class="page-number current" aria-current="page">%d</span>',
                        $i
                    );
                } else {
                    $page_url = get_pagenum_link($i);
                    $output .= sprintf(
                        '<a href="%s" class="page-number" aria-label="%s">%d</a>',
                        esc_url($page_url),
                        esc_attr(sprintf(__('Page %d', 'fau-elemental'), $i)),
                        $i
                    );
                }
            }

            // Ellipsis
            if ($total_pages > 6) {
                $output .= '<span class="page-ellipsis" aria-hidden="true">...</span>';
            }

            // Last 3 pages
            for ($i = $total_pages - 2; $i <= $total_pages; $i++) {
                if ($i === $current_page) {
                    $output .= sprintf(
                        '<span class="page-number current" aria-current="page">%d</span>',
                        $i
                    );
                } else {
                    $page_url = get_pagenum_link($i);
                    $output .= sprintf(
                        '<a href="%s" class="page-number" aria-label="%s">%d</a>',
                        esc_url($page_url),
                        esc_attr(sprintf(__('Page %d', 'fau-elemental'), $i)),
                        $i
                    );
                }
            }
        }
    }

    // Next button
    if ($current_page < $total_pages) {
        $next_url = get_pagenum_link($current_page + 1);
        $output .= sprintf(
            '<a href="%s" class="page-number next" aria-label="%s"><span class="pagination-icon pagination-icon-next"></span></a>',
            esc_url($next_url),
            esc_attr__('Next page', 'fau-elemental')
        );
    } else {
        $output .= '<span class="page-number next disabled" aria-disabled="true" aria-label="' . esc_attr__('Next page', 'fau-elemental') . '"><span class="pagination-icon pagination-icon-next"></span></span>';
    }

    $output .= '</div>';
    $output .= '</nav>';
    return $output;
}
}

/**
 * Generates load more button HTML.
 *
 * @param int $current_page Current page number.
 * @param int $total_pages Total number of pages.
 * @param string $grid_id Grid ID for JavaScript targeting.
 * @return string The load more HTML.
 */
if ( ! function_exists( 'fau_elemental_generate_load_more' ) ) {
function fau_elemental_generate_load_more($current_page, $total_pages, $grid_id) {
    if ($current_page >= $total_pages) {
        return '';
    }

    $output = '<div class="fau-teaser-grid__load-more-wrapper">';
    $output .= '<div class="wp-block-button is-style-secondary">';
    $output .= sprintf(
        '<button class="wp-block-button__link load-more-button" data-grid-id="%s" data-current-page="%d" data-total-pages="%d" data-default-text="%s" data-loading-text="%s">%s</button>',
        esc_attr($grid_id),
        $current_page,
        $total_pages,
        esc_attr__('Load More', 'fau-elemental'),
        esc_attr__('Loading…', 'fau-elemental'),
        esc_html__('Load More', 'fau-elemental')
    );
    $output .= '</div>';
    $output .= '</div>';
    return $output;
}
}

echo render_block_fau_teaser_grid($attributes, $content, $block); 