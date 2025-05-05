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

if ( ! function_exists( 'render_block_fau_teaser_grid' ) ) {
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
        $show_pagination = $attributes['showPagination'] ?? true;
        $current_page = $attributes['currentPage'] ?? 1;
        $total_pages = $attributes['totalPages'] ?? 1;
        $posts_per_page = $attributes['postsPerPage'] ?? 15;
        $selected_category = $attributes['category'] ?? 0;
        $order_by = $attributes['orderBy'] ?? 'date';
        $order = $attributes['order'] ?? 'DESC';

        // Start building the output
        $wrapper_attributes = get_block_wrapper_attributes([
            'class' => 'fau-list-item'
        ]);

        $grid_classes = ['fau-teaser-grid', $display_style];
        if ($display_style === 'teaser-grid') {
            $grid_classes[] = "layout-{$teaser_layout}";
        }

        $output = sprintf('<div %s>', $wrapper_attributes);
        $output .= sprintf('<div class="%s">', esc_attr(implode(' ', $grid_classes)));

        if ($selection_mode === 'manual' && !empty($selected_posts)) {
            // Handle manually selected posts
            foreach ($selected_posts as $selected_post) {
                $post = get_post($selected_post['id']);
                if ($post) {
                    $output .= fau_elemental_render_teaser_item($post, $variant, $grid_classes);
                }
            }
        } else {
            // Handle automatic posts
            $args = [
                'post_type' => $variant,
                'posts_per_page' => $posts_per_page,
                'paged' => $current_page,
                'orderby' => $order_by,
                'order' => $order,
            ];

            if ($selected_category) {
                $args['cat'] = $selected_category;
            }

            $query = new WP_Query($args);

            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    $output .= fau_elemental_render_teaser_item(get_post(), $variant, $grid_classes);
                }
                wp_reset_postdata();
            } else {
                $output .= '<p>No items found</p>';
            }
        }

        $output .= '</div>'; // Close teaser grid

        // Add pagination if enabled and there are multiple pages
        if ($show_pagination && $total_pages > 1 && $selection_mode === 'auto') {
            $output .= fau_elemental_generate_pagination($current_page, $total_pages);
        }

        $output .= '</div>'; // Close fau-list-item

        return $output;
    }
}

if ( ! function_exists( 'fau_elemental_render_teaser_item' ) ) {
    /**
     * Renders a single teaser item.
     *
     * @param WP_Post $post The post object.
     * @param string  $variant The variant type (post or page).
     * @param array   $grid_classes The grid classes.
     * @return string The rendered teaser item HTML.
     */
    function fau_elemental_render_teaser_item($post, $variant, $grid_classes) {
        $image = get_the_post_thumbnail_url($post->ID, 'full') ?: get_template_directory_uri() . '/assets/images/logo.svg';
        $title = get_the_title($post);
        $excerpt = get_the_excerpt($post);
        $link = get_permalink($post);
        $is_dark_theme = in_array('is-style-dark', $grid_classes);

        $output = '<div class="teaser-item ' . esc_attr($variant) . '-teaser">';
        
        // Image wrapper
        $output .= '<div class="teaser-image-wrapper">';
        $output .= '<div class="teaser-image">';
        $output .= sprintf('<img src="%s" alt="%s" />', esc_url($image), esc_attr($title));
        $output .= '</div>';

        // Add date meta for posts
        if ($variant === 'post') {
            $date_obj = new DateTime($post->post_date);
            $day = $date_obj->format('d');
            $month_year = strtoupper($date_obj->format('M Y'));
            
            $output .= '<div class="teaser-meta">';
            $output .= '<time>';
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
                $output .= sprintf('<span class="category">%s</span>', esc_html($categories[0]->name));
            }
        }

        // Title
        $output .= '<h4 class="clamp-3">';
        $output .= sprintf('<span class="visually-hidden">%s</span>', esc_html($title));
        $output .= sprintf('<span aria-hidden="true">%s</span>', esc_html($title));
        $output .= '</h4>';

        // Excerpt
        $output .= '<div class="excerpt clamp-3">';
        $output .= sprintf('<span class="visually-hidden">%s</span>', wp_kses_post($excerpt));
        $output .= sprintf('<span aria-hidden="true">%s</span>', wp_kses_post($excerpt));
        $output .= '</div>';

        $output .= '</div>'; // Close content-column

        // Button
        $output .= '<div class="button-teaser">';
        $output .= sprintf('<a href="%s" class="wp-block-button__link"></a>', esc_url($link));
        $output .= '</div>';

        $output .= '</div>'; // Close teaser-content
        $output .= '</div>'; // Close teaser-content-wrapper
        $output .= '</div>'; // Close teaser-item

        return $output;
    }
}

if ( ! function_exists( 'fau_elemental_generate_pagination' ) ) {
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
        $output .= sprintf(
            '<button class="page-number prev%s" data-page="%d"%s>Prev</button>',
            $prev_disabled,
            $current_page - 1,
            $prev_disabled ? ' disabled' : ''
        );

        // Page numbers
        for ($i = 1; $i <= $total_pages; $i++) {
            if ($i === 1 || $i === $total_pages || ($i >= $current_page - 1 && $i <= $current_page + 1)) {
                $active = $current_page === $i ? ' active' : '';
                $output .= sprintf(
                    '<button class="page-number%s" data-page="%d">%d</button>',
                    $active,
                    $i,
                    $i
                );
            } elseif ($i === $current_page - 2 || $i === $current_page + 2) {
                $output .= '<span class="page-ellipsis">...</span>';
            }
        }

        // Next button
        $next_disabled = $current_page === $total_pages ? ' disabled' : '';
        $output .= sprintf(
            '<button class="page-number next%s" data-page="%d"%s>Next</button>',
            $next_disabled,
            $current_page + 1,
            $next_disabled ? ' disabled' : ''
        );

        $output .= '</div>';
        return $output;
    }
} 