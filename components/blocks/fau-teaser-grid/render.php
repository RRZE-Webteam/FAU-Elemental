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
    $selected_author = $attributes['selectedAuthor'] ?? 0;
    $selected_year = $attributes['selectedYear'] ?? 0;
    $selected_month = $attributes['selectedMonth'] ?? 0;
    $selected_day = $attributes['selectedDay'] ?? 0;
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
        'data-author' => $selected_author,
        'data-year' => $selected_year,
        'data-month' => $selected_month,
        'data-day' => $selected_day,
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
        $output .= fau_elemental_wrap_teaser_items($teaser_items, $teaser_layout);
    } else {
        $output .= '<p class="no-posts">' . __('No items found.', 'fau-elemental') . '</p>';
    }

    $output .= '</div>'; // Close fau-teaser-grid

    // Add pagination if enabled
    if ($show_pagination && $total_posts > $posts_per_page) {
        $total_pages = (int) ceil($total_posts / $posts_per_page);
        
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