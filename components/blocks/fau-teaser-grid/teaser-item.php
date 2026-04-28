<?php
/**
 * Shared teaser item functions for the `fau-elemental/fau-teaser-grid` block.
 *
 * @package FAU_Elemental
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Renders a single teaser item.
 *
 * @param WP_Post $post The post object.
 * @param string  $variant The variant type (post or page).
 * @param array   $grid_classes The grid classes.
 * @param string  $heading_level The heading level to use (h1-h6).
 * @return string The rendered teaser item HTML.
 */
if ( ! function_exists( 'fau_elemental_render_teaser_item' ) ) {
function fau_elemental_render_teaser_item($post, $variant, $grid_classes, $heading_level = 'h4') {
    $title = get_the_title($post);
    $excerpt = strip_shortcodes(get_the_excerpt($post));
    $link = get_permalink($post);
    $is_dark_theme = in_array('is-style-dark', $grid_classes);

    // Make whole article clickable
    $output = sprintf('<a class="teaser-item-link teaser-item" href="%s">', esc_url($link));

    $output .= sprintf(
        '<article class="%s-teaser" data-variant="%s" data-href="%s" tabindex="0" role="button" aria-labelledby="teaser-title-%d">',
        esc_attr($variant),
        esc_attr($variant),
        esc_url($link),
        $post->ID
    );
    
    // Image wrapper - use responsive images with proper srcset/sizes
    $output .= '<div class="teaser-image-wrapper">';
    $output .= '<div class="teaser-image">';
    
    // Check for custom teaser image first, then featured image, then fallback
    $teaser_img_id = faue_get_teaser_image_id($post->ID);
    $featured_img_id = $teaser_img_id ? $teaser_img_id : get_post_thumbnail_id($post->ID);

    if ($featured_img_id) {
        // Use wp_get_attachment_image for responsive images with srcset
        // Sizes: 3 columns on desktop (440px each), 2 columns on medium (50vw), 1 column on mobile (100vw)
        $output .= wp_get_attachment_image($featured_img_id, 'medium_large', false, [
            'alt' => $title,
            'loading' => 'lazy',
            'sizes' => '(max-width: 999px) 100vw, (max-width: 1199px) 50vw, 440px'
        ]);
    } else {
        // Fallback image - get responsive version if available
        $fallback_image_html = faue_get_post_fallback_image_html($post->ID, $title, 'medium_large', [
            'sizes' => '(max-width: 999px) 100vw, (max-width: 1199px) 50vw, 440px'
        ]);
        if ($fallback_image_html) {
            $output .= $fallback_image_html;
        } else {
            // Last resort: simple img tag with fallback URL
            $fallback_url = faue_get_post_fallback_image($post->ID, 'medium_large');
            $output .= sprintf(
                '<img src="%s" alt="%s" loading="lazy">',
                esc_url($fallback_url),
                esc_attr($title)
            );
        }
    }
    
    $output .= '</div>';

    // Add date meta for posts (visible)
    if ($variant === 'post') {
        $timestamp = strtotime($post->post_date);
        $day = date_i18n('d', $timestamp);
        $month_year = strtoupper(date_i18n('M Y', $timestamp));
        
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
    
    // Add hidden date meta for pages (for sorting purposes)
    if ($variant === 'page') {
        $output .= sprintf(
            '<time datetime="%s" class="date-meta-hidden" data-created="%s" data-modified="%s"></time>',
            esc_attr($post->post_date),
            esc_attr($post->post_date),
            esc_attr($post->post_modified)
        );
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
        '<%s class="clamp-3" id="teaser-title-%d">%s</%s>',
        esc_attr($heading_level),
        $post->ID,
        esc_html($title),
        esc_attr($heading_level)
    );

    // Excerpt
    $output .= '<div class="excerpt clamp-3">';
    $output .= sprintf('<span class="visually-hidden">%s</span>', wp_kses_post($excerpt));
    $output .= sprintf('<span aria-hidden="true">%s</span>', wp_kses_post($excerpt));
    $output .= '</div>';

    $output .= '</div>'; // Close content-column

    // Button
    $output .= '<div class="button-teaser">';
    $output .= sprintf(
        '<span class="wp-block-button__link"><span class="screen-reader-text">%s %s</span></span>',
        __('Read more about', 'fau-elemental'),
        esc_html($title)
    );
    $output .= '</div>';

    $output .= '</div>'; // Close teaser-content
    $output .= '</div>'; // Close teaser-content-wrapper
    $output .= '</article>'; // Close teaser-item
    $output .= '</a>'; // Close teaser teaser-item-link
    
    return $output;
}
}

/**
 * Wraps teaser items in groups of 3 for specific layouts
 *
 * @param array $items Array of teaser item HTML strings
 * @param string $layout The current layout
 * @return string The wrapped teaser items HTML
 */
if ( ! function_exists( 'fau_elemental_wrap_teaser_items' ) ) {
    function fau_elemental_wrap_teaser_items($items, $layout) {
        if (in_array($layout, ['l2s', '2sl'])) {
            $output = '';
            $item_count = count($items);
            $group_number = 0;
            
            for ($i = 0; $i < $item_count; $i += 3) {
                $group_items = array_slice($items, $i, 3);
                if (!empty($group_items)) {
                    // Keep items in their original order
                    // For 2sl layout, assign different classes for visual positioning
                    $item_position = 0;
                    foreach ($group_items as $index => $item) {
                        $item_position++;
                        $li_class = 'teaser-group-item';
                        
                        // Assign classes based on layout and position
                        if ($layout === '2sl' && count($group_items) === 3) {
                            // For 2sl: first two are small, third is large
                            if ($item_position === 1) {
                                $li_class .= ' teaser-group-item-2'; // Small top (visually on left)
                            } elseif ($item_position === 2) {
                                $li_class .= ' teaser-group-item-3'; // Small bottom (visually on left)
                            } else {
                                $li_class .= ' teaser-group-item-1'; // Large (visually on right)
                            }
                        } else {
                            // For l2s layout, use standard numbering
                            $li_class .= ' teaser-group-item-' . $item_position;
                        }
                        
                        $li_class .= ' teaser-group-' . $group_number;
                        $output .= '<li class="' . esc_attr($li_class) . '" data-group="' . esc_attr($group_number) . '" data-position="' . esc_attr($item_position) . '">' . $item . '</li>';
                    }
                    $group_number++;
                }
            }
            
            return $output;
        }
        
        $wrapped_items = array_map(function($item) {
            return '<li>' . $item . '</li>';
        }, $items);
        
        return implode('', $wrapped_items);
    }
    
} 