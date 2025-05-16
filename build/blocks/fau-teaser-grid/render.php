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
        $current_page = get_query_var('paged') ? get_query_var('paged') : 1;
        $posts_per_page = $attributes['postsPerPage'] ?? 15;
        $selected_category = $attributes['category'] ?? 0;
        $order_by = $attributes['orderBy'] ?? 'date';
        $order = $attributes['order'] ?? 'DESC';
        $heading_level = $attributes['headingLevel'] ?? 'h4';
        
        // Ensure it's a valid heading tag
        $allowed_headings = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
        if (!in_array($heading_level, $allowed_headings)) {
            $heading_level = 'h4'; // Default to h4 if not valid
        }
        
        // Start building the output
        $wrapper_attributes = get_block_wrapper_attributes([
            'class' => 'fau-list-item',
            'role' => 'region',
            'aria-label' => __('Content grid', 'fau-elemental')
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
            foreach ($selected_posts as $selected_post) {
                $post = get_post($selected_post['id']);
                if ($post) {
                    $output .= fau_elemental_render_teaser_item($post, $variant, $grid_classes, $heading_level);
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
                    $output .= fau_elemental_render_teaser_item(get_post(), $variant, $grid_classes, $heading_level);
                }
                wp_reset_postdata();
            } else {
                $output .= sprintf(
                    '<p role="status" class="no-items-found">%s</p>',
                    esc_html__('No items found', 'fau-elemental')
                );
            }
        }

        $output .= '</div>'; // Close teaser grid

        // Add pagination if enabled and there are multiple pages
        if ($show_pagination && isset($query) && $query->found_posts > $posts_per_page && $selection_mode === 'auto') {
            $total_pages = ceil($query->found_posts / $posts_per_page);
            $output .= sprintf(
                '<nav class="pagination" role="navigation" aria-label="%s">',
                esc_attr__('Pagination', 'fau-elemental')
            );
            $output .= paginate_links(array(
                'base' => add_query_arg('paged', '%#%'),
                'format' => '',
                'current' => $current_page,
                'total' => $total_pages,
                'prev_text' => __('Previous page', 'fau-elemental'),
                'next_text' => __('Next page', 'fau-elemental'),
                'type' => 'plain',
                'end_size' => 3,
                'mid_size' => 3
            ));
            $output .= '</nav>';
        }

        $output .= '</section>'; // Close fau-list-item section

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
     * @param string  $heading_level The heading level to use (h1-h6).
     * @return string The rendered teaser item HTML.
     */
    function fau_elemental_render_teaser_item($post, $variant, $grid_classes, $heading_level = 'h4') {
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