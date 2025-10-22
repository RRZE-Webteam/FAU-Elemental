<?php
declare(strict_types=1);

/**
 * Breadcrumb Functionality
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Maximum length for breadcrumb titles before truncation
 */
if (!defined('FAUE_BREADCRUMB_TITLE_MAX_LENGTH')) {
    define('FAUE_BREADCRUMB_TITLE_MAX_LENGTH', 50);
}

/**
 * Add dark mode class to the parent block group containing breadcrumbs
 *
 * @param string $block_content The block content being rendered
 * @param array  $block         The block data
 * @return string Modified block content with dark mode class if applicable
 */
function faue_breadcrumbs_block_class($block_content, $block): string {
    if ($block['blockName'] === 'core/group' && 
        isset($block['attrs']['className']) && 
        str_contains($block['attrs']['className'], 'breadcrumbs')) {
        $mode = get_theme_mod('faue_breadcrumb_variant_blue');
        if ($mode) {
            $block_content = str_replace('wp-block-group', 'wp-block-group is-style-dark', $block_content);
        }
    }
    return $block_content;
}
add_filter('render_block', 'faue_breadcrumbs_block_class', 10, 2);

/**
 * Get the appropriate title for breadcrumb display
 * Always uses the original post title to maintain consistency with URL slug and navigation
 *
 * @param int $post_id The post ID to get the title for
 * @return string The title to display in breadcrumbs
 */
function faue_get_breadcrumb_title($post_id) {
    return get_the_title($post_id);
}

/**
 * Display breadcrumb navigation with responsive mobile/desktop versions
 *
 * Renders breadcrumbs using server-side conditional logic to avoid duplicate DOM elements.
 * Mobile version shows only the immediate parent, desktop shows full hierarchy.
 * Supports both pages (with page hierarchy) and posts (with category hierarchy).
 *
 * @return void
 */
function faue_breadcrumbs(): void {
    if (is_front_page()) {
        return;
    }

    $ancestors = array();
    if (is_page()) {
        $ancestors = get_post_ancestors(get_the_ID());
        $ancestors = array_reverse($ancestors);
    } elseif (is_single()) {
        $terms = get_the_terms(get_the_ID(), 'category');
        if ($terms && !is_wp_error($terms)) {
            $child_categories = wp_list_filter($terms, array('parent' => 0), 'NOT');
            
            $primary_category = null;
            if (!empty($child_categories)) {
                $primary_category = reset($child_categories);
            } else {
                $primary_category = reset($terms);
            }
            
            $category_ancestors = get_ancestors($primary_category->term_id, 'category');
            $category_ancestors = array_reverse($category_ancestors);
            $category_ancestors[] = $primary_category->term_id;
            $ancestors = $category_ancestors;
        }
    }

    $mode = get_theme_mod('faue_breadcrumb_variant_blue');

    $wrapper_classes = array('breadcrumbs-wrapper');
    if ($mode) {
        $wrapper_classes[] = 'is-style-dark';
    }
    
    echo '<div class="' . esc_attr(implode(' ', $wrapper_classes)) . '">';
    echo '<nav class="breadcrumbs" aria-label="' . esc_attr__('Breadcrumb navigation', 'fau-elemental') . '">';
    echo '<ol class="breadcrumbs__list" itemscope itemtype="https://schema.org/BreadcrumbList">';

    $position = 1;
    $total_items = count($ancestors) + 1;

    // Always render mobile breadcrumbs (CSS will control visibility)
    if (!empty($ancestors)) {
        $parent = $ancestors[count($ancestors) - 1];
        if (is_page()) {
            $parent_post = get_post($parent);
            $parent_title = faue_get_breadcrumb_title($parent_post->ID);
            $parent_url = get_permalink($parent_post->ID);
        } else {
            $parent_category = get_category($parent);
            $parent_title = $parent_category->name;
            $parent_url = get_category_link($parent_category->term_id);
        }

        $truncated_parent = strlen($parent_title) > FAUE_BREADCRUMB_TITLE_MAX_LENGTH ? mb_strimwidth($parent_title, 0, FAUE_BREADCRUMB_TITLE_MAX_LENGTH, '…', 'UTF-8') : $parent_title;

        echo '<li class="breadcrumbs__item breadcrumbs__item--mobile" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
        echo '<span class="breadcrumbs__chevron"></span>';
        echo '<a href="' . esc_url($parent_url) . '" class="breadcrumbs__link" itemprop="item" title="' . esc_attr($parent_title) . '">';
        echo '<span itemprop="name">' . esc_html($truncated_parent) . '</span>';
        echo '</a>';
        echo '<meta itemprop="position" content="' . ($total_items - 1) . '">';
        echo '</li>';
    } else {
        echo '<li class="breadcrumbs__item breadcrumbs__item--mobile" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
        echo '<span class="breadcrumbs__chevron"></span>';
        echo '<a href="' . esc_url(home_url('/')) . '" class="breadcrumbs__link" itemprop="item">';
        echo '<span itemprop="name">' . esc_html__('Start', 'fau-elemental') . '</span>';
        echo '</a>';
        echo '<meta itemprop="position" content="1">';
        echo '</li>';
    }

    // Always render desktop breadcrumbs (CSS will control visibility)
    echo '<li class="breadcrumbs__item breadcrumbs__item--desktop breadcrumbs__item--home" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
    echo '<a href="' . esc_url(home_url('/')) . '" class="breadcrumbs__link" itemprop="item">';
    echo '<span itemprop="name">' . esc_html__('Start', 'fau-elemental') . '</span>';
    echo '</a>';
    echo '<meta itemprop="position" content="1">';
    echo '</li>';
    
    $position = 2;

    foreach ($ancestors as $ancestor) {
        if (is_page()) {
            $title = faue_get_breadcrumb_title($ancestor);
            $url = get_permalink($ancestor);
        } else {
            $category = get_category($ancestor);
            $title = $category->name;
            $url = get_category_link($category->term_id);
        }

        $truncated_title = strlen($title) > FAUE_BREADCRUMB_TITLE_MAX_LENGTH ? mb_strimwidth($title, 0, FAUE_BREADCRUMB_TITLE_MAX_LENGTH, '…', 'UTF-8') : $title;

        echo '<li class="breadcrumbs__item breadcrumbs__item--desktop" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
        echo '<a href="' . esc_url($url) . '" class="breadcrumbs__link" itemprop="item" title="' . esc_attr($title) . '">';
        echo '<span itemprop="name">' . esc_html($truncated_title) . '</span>';
        echo '</a>';
        echo '<meta itemprop="position" content="' . $position . '">';
        echo '</li>';
        $position++;
    }

    $current_title = '';
    if (is_category()) {
        $current_title = single_cat_title('', false);
    } elseif (is_single()) {
        $current_title = faue_get_breadcrumb_title(get_the_ID());
    } elseif (is_page()) {
        $current_title = faue_get_breadcrumb_title(get_the_ID());
    } elseif (is_search()) {
        $current_title = esc_html__('Search Results', 'fau-elemental');
    } elseif (is_404()) {
        $current_title = esc_html__('404 - Page Not Found', 'fau-elemental');
    }

    $truncated_current = strlen($current_title) > FAUE_BREADCRUMB_TITLE_MAX_LENGTH ? mb_strimwidth($current_title, 0, FAUE_BREADCRUMB_TITLE_MAX_LENGTH, '…', 'UTF-8') : $current_title;

    echo '<li class="breadcrumbs__item breadcrumbs__item--current breadcrumbs__item--desktop" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" aria-current="page">';
    echo '<span class="breadcrumbs__current" itemprop="item" title="' . esc_attr($current_title) . '">';
    echo '<span itemprop="name">' . esc_html($truncated_current) . '</span>';
    echo '</span>';
    echo '<meta itemprop="position" content="' . $position . '">';
    echo '</li>';

    echo '</ol>';
    echo '</nav>';
    echo '</div>';
}