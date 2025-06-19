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

// AJAX handler for load more functionality
add_action('wp_ajax_fau_load_more_posts', 'fau_load_more_posts_handler');
add_action('wp_ajax_nopriv_fau_load_more_posts', 'fau_load_more_posts_handler');

// Helper functions for AJAX (duplicated from render.php to avoid circular dependencies)
if ( ! function_exists( 'fau_elemental_render_teaser_item_ajax' ) ) {
    /**
     * Renders a single teaser item for AJAX requests.
     *
     * @param WP_Post $post The post object.
     * @param string  $variant The variant type (post or page).
     * @param array   $grid_classes The grid classes.
     * @param string  $heading_level The heading level to use (h1-h6).
     * @return string The rendered teaser item HTML.
     */
    function fau_elemental_render_teaser_item_ajax($post, $variant, $grid_classes, $heading_level = 'h4') {
        $image = get_the_post_thumbnail_url($post->ID, 'full') ?: get_template_directory_uri() . '/assets/images/logo.svg';
        $title = get_the_title($post);
        $excerpt = get_the_excerpt($post);
        $link = get_permalink($post);
        $is_dark_theme = in_array('is-style-dark', $grid_classes);

        $output = sprintf(
            '<article class="teaser-item %s-teaser" data-variant="%s" data-href="%s" tabindex="0" role="button" aria-labelledby="teaser-title-%d">',
            esc_attr($variant),
            esc_attr($variant),
            esc_url($link),
            $post->ID
        );
        
        // Image wrapper
        $output .= '<div class="teaser-image-wrapper">';
        $output .= '<div class="teaser-image">';
        $output .= sprintf(
            '<img src="%s" alt="%s" loading="lazy" />',
            esc_url($image),
            esc_attr($title)
        );
        $output .= '</div>';

        // Add date meta for posts
        if ($variant === 'post') {
            $date_obj = new DateTime($post->post_date);
            $day = $date_obj->format('d');
            $month_year = strtoupper($date_obj->format('M Y'));
            
            $output .= '<div class="teaser-meta">';
            $output .= sprintf(
                '<time datetime="%s">',
                esc_attr($post->post_date)
            );
            $output .= sprintf('<span class="date-day">%s</span>', esc_html($day));
            $output .= sprintf('<span class="date-month-year">%s</span>', esc_html($month_year));
            $output .= '</time>';
            $output .= '</div>';
        }

        $output .= '</div>'; // Close image wrapper

        // Content wrapper
        $content_wrapper_class = 'teaser-content-wrapper';
        if ($is_dark_theme) {
            $content_wrapper_class .= ' dark-theme';
        }
        
        $output .= sprintf('<div class="%s">', esc_attr($content_wrapper_class));
        $output .= '<div class="teaser-content">';
        $output .= '<div class="content-column">';

        // Add category for posts
        if ($variant === 'post') {
            $categories = get_the_category($post->ID);
            if (!empty($categories)) {
                $output .= sprintf(
                    '<span class="category">%s</span>',
                    esc_html($categories[0]->name)
                );
            }
        }

        // Title with specified heading level
        $output .= sprintf(
            '<%s class="clamp-3" id="teaser-title-%d">',
            esc_attr($heading_level),
            $post->ID
        );
        $output .= sprintf('<a href="%s">%s</a>', esc_url($link), esc_html($title));
        $output .= sprintf('</%s>', esc_attr($heading_level));

        // Excerpt
        $output .= '<div class="excerpt clamp-3">';
        $output .= sprintf('<span class="visually-hidden">%s</span>', wp_kses_post($excerpt));
        $output .= sprintf('<span aria-hidden="true">%s</span>', wp_kses_post($excerpt));
        $output .= '</div>';

        $output .= '</div>'; // Close content-column

        // Button
        $output .= '<div class="button-teaser">';
        $output .= sprintf(
            '<a href="%s" class="wp-block-button__link"><span class="screen-reader-text">%s %s</span></a>',
            esc_url($link),
            __('Read more about', 'fau-elemental'),
            esc_html($title)
        );
        $output .= '</div>';

        $output .= '</div>'; // Close teaser-content
        $output .= '</div>'; // Close teaser-content-wrapper
        $output .= '</article>'; // Close teaser-item

        return $output;
    }
}

if (!function_exists('fau_elemental_wrap_teaser_items_ajax')) {
    /**
     * Wraps teaser items in groups of 3 for specific layouts (AJAX version)
     *
     * @param array $items Array of teaser item HTML strings
     * @param string $layout The current layout
     * @return string The wrapped teaser items HTML
     */
    function fau_elemental_wrap_teaser_items_ajax($items, $layout) {
        // Only wrap for l2s and 2sl layouts
        if (!in_array($layout, ['l2s', '2sl'])) {
            return implode('', $items);
        }

        $output = '';
        $item_count = count($items);
        
        for ($i = 0; $i < $item_count; $i += 3) {
            $group_items = array_slice($items, $i, 3);
            if (!empty($group_items)) {
                $output .= '<div class="teaser-group">';
                $output .= implode('', $group_items);
                $output .= '</div>';
            }
        }
        
        return $output;
    }
}

if (!function_exists('fau_load_more_posts_handler')) {
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
                $teaser_items[] = fau_elemental_render_teaser_item_ajax(get_post(), $variant, $grid_classes, $heading_level);
            }
            wp_reset_postdata();
            $html = fau_elemental_wrap_teaser_items_ajax($teaser_items, $teaser_layout);
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
}

if (!function_exists('fau_teaser_grid_ajax_filter')) {
    /**
     * AJAX handler for teaser grid filtering
     */
    function fau_teaser_grid_ajax_filter() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'fau_teaser_grid_filter')) {
            wp_die(__('Security check failed', 'fau-elemental'), 403);
        }

        // Get parameters
        $variant = sanitize_text_field($_POST['variant'] ?? 'post');
        $posts_per_page = absint($_POST['posts_per_page'] ?? 15);
        $current_page = absint($_POST['page'] ?? 1);
        $search_query = sanitize_text_field($_POST['search'] ?? '');
        $filters = $_POST['filters'] ?? [];
        $sort = sanitize_text_field($_POST['sort'] ?? 'date');
        $sort_order = sanitize_text_field($_POST['sort_order'] ?? 'DESC');
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
            'paged' => $current_page,
            'orderby' => $sort,
            'order' => $sort_order,
            'post_status' => 'publish'
        ];

        // Add search
        if (!empty($search_query)) {
            $args['s'] = $search_query;
        }

        // Add filters (categories, tags, etc.)
        if (!empty($filters) && is_array($filters)) {
            foreach ($filters as $filter_type => $filter_value) {
                if (!empty($filter_value)) {
                    switch ($filter_type) {
                        case 'category':
                            $args['cat'] = absint($filter_value);
                            break;
                        case 'tag':
                            $args['tag_id'] = absint($filter_value);
                            break;
                        default:
                            // Handle custom taxonomies
                            if (taxonomy_exists($filter_type)) {
                                $args['tax_query'][] = [
                                    'taxonomy' => $filter_type,
                                    'field' => 'term_id',
                                    'terms' => absint($filter_value)
                                ];
                            }
                            break;
                    }
                }
            }
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

        $response = [
            'success' => true,
            'data' => [
                'html' => '',
                'found_posts' => $query->found_posts,
                'max_num_pages' => $query->max_num_pages,
                'current_page' => $current_page,
                'results_text' => sprintf(
                    __('%d to %d from %d records', 'fau-elemental'),
                    (($current_page - 1) * $posts_per_page) + 1,
                    min($current_page * $posts_per_page, $query->found_posts),
                    $query->found_posts
                )
            ]
        ];

        if ($query->have_posts()) {
            $teaser_items = [];
            while ($query->have_posts()) {
                $query->the_post();
                $teaser_items[] = fau_elemental_render_teaser_item_ajax(get_post(), $variant, $grid_classes, $heading_level);
            }
            wp_reset_postdata();
            $response['data']['html'] = fau_elemental_wrap_teaser_items_ajax($teaser_items, $teaser_layout);
        } else {
            $response['data']['html'] = sprintf(
                '<p role="status" class="no-items-found">%s</p>',
                esc_html__('No items found', 'fau-elemental')
            );
        }

        wp_send_json($response);
    }
}

// Hook the AJAX handlers
add_action('wp_ajax_fau_teaser_grid_filter', 'fau_teaser_grid_ajax_filter');
add_action('wp_ajax_nopriv_fau_teaser_grid_filter', 'fau_teaser_grid_ajax_filter'); 