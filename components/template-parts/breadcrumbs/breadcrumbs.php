<?php
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
define('FAUE_BREADCRUMB_TITLE_MAX_LENGTH', 50);

/**
 * Add dark mode class to the parent block group
 */
function faue_breadcrumbs_block_class($block_content, $block) {
    if ($block['blockName'] === 'core/group' && strpos($block_content, 'breadcrumbs') !== false) {
        $mode = get_theme_mod('faue_breadcrumb_variant_blue');
        if ($mode) {
            $block_content = str_replace('wp-block-group', 'wp-block-group is-style-dark', $block_content);
        }
    }
    return $block_content;
}
add_filter('render_block', 'faue_breadcrumbs_block_class', 10, 2);

/**
 * Display breadcrumb navigation
 */
function faue_breadcrumbs() {
    if (is_front_page()) {
        return;
    }

    // Get the current page's ancestors
    $ancestors = array();
    if (is_page()) {
        $ancestors = get_post_ancestors(get_the_ID());
        // Reverse the array to get ancestors in correct order (furthest parent first)
        $ancestors = array_reverse($ancestors);
    } elseif (is_single()) {
        $categories = get_the_category();
        if ($categories) {
            $ancestors = array($categories[0]->term_id);
        }
    }

    // Get breadcrumb mode from customizer
    $mode = get_theme_mod('faue_breadcrumb_variant_blue');

    // Start breadcrumb navigation
    $wrapper_classes = array('breadcrumbs-wrapper');
    if ($mode) {
        $wrapper_classes[] = 'is-style-dark';
    }
    
    echo '<div class="' . esc_attr(implode(' ', $wrapper_classes)) . '">';
    echo '<nav class="breadcrumbs" aria-label="' . esc_attr__('Breadcrumb navigation', 'fau-elemental') . '">';
    echo '<ol class="breadcrumbs__list" itemscope itemtype="https://schema.org/BreadcrumbList">';

    // Home link (desktop only)
    echo '<li class="breadcrumbs__item breadcrumbs__item--desktop" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
    echo '<a href="' . esc_url(home_url('/')) . '" class="breadcrumbs__link" itemprop="item">';
    echo '<span itemprop="name">' . esc_html__('Start', 'fau-elemental') . '</span>';
    echo '</a>';
    echo '<meta itemprop="position" content="1" />';
    echo '</li>';

    $position = 2;
    $total_items = count($ancestors) + 1; // +1 for current page

    // Mobile: Show only parent
    if (!empty($ancestors)) {
        $parent = $ancestors[count($ancestors) - 1]; // Get the last ancestor (closest parent)
        if (is_page()) {
            $parent_post = get_post($parent);
            $parent_title = $parent_post->post_title;
            $parent_url = get_permalink($parent_post->ID);
        } else {
            $parent_category = get_category($parent);
            $parent_title = $parent_category->name;
            $parent_url = get_category_link($parent_category->term_id);
        }

        // Truncate parent title
        $truncated_parent = strlen($parent_title) > FAUE_BREADCRUMB_TITLE_MAX_LENGTH ? substr($parent_title, 0, FAUE_BREADCRUMB_TITLE_MAX_LENGTH - 3) . '...' : $parent_title;

        echo '<li class="breadcrumbs__item breadcrumbs__item--mobile" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
        echo '<span class="breadcrumbs__chevron"></span>';
        echo '<a href="' . esc_url($parent_url) . '" class="breadcrumbs__link" itemprop="item" title="' . esc_attr($parent_title) . '">';
        echo '<span itemprop="name">' . esc_html($truncated_parent) . '</span>';
        echo '</a>';
        echo '<meta itemprop="position" content="' . ($total_items - 1) . '" />';
        echo '</li>';
    } else {
        // No ancestors: show Start link in mobile with chevron
        echo '<li class="breadcrumbs__item breadcrumbs__item--mobile" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
        echo '<span class="breadcrumbs__chevron"></span>';
        echo '<a href="' . esc_url(home_url('/')) . '" class="breadcrumbs__link" itemprop="item">';
        echo '<span itemprop="name">' . esc_html__('Start', 'fau-elemental') . '</span>';
        echo '</a>';
        echo '<meta itemprop="position" content="1" />';
        echo '</li>';
    }

    // Desktop: Show full hierarchy
    foreach ($ancestors as $ancestor) {
        if (is_page()) {
            $title = get_the_title($ancestor);
            $url = get_permalink($ancestor);
        } else {
            $category = get_category($ancestor);
            $title = $category->name;
            $url = get_category_link($category->term_id);
        }

        // Truncate long titles
        $truncated_title = strlen($title) > FAUE_BREADCRUMB_TITLE_MAX_LENGTH ? substr($title, 0, FAUE_BREADCRUMB_TITLE_MAX_LENGTH - 3) . '...' : $title;

        echo '<li class="breadcrumbs__item breadcrumbs__item--desktop" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
        echo '<a href="' . esc_url($url) . '" class="breadcrumbs__link" itemprop="item" title="' . esc_attr($title) . '">';
        echo '<span itemprop="name">' . esc_html($truncated_title) . '</span>';
        echo '</a>';
        echo '<meta itemprop="position" content="' . $position . '" />';
        echo '</li>';
        $position++;
    }

    // Current page (desktop only)
    $current_title = '';
    if (is_category()) {
        $current_title = single_cat_title('', false);
    } elseif (is_single()) {
        $current_title = get_the_title();
    } elseif (is_page()) {
        $current_title = get_the_title();
    } elseif (is_search()) {
        $current_title = esc_html__('Search Results', 'fau-elemental');
    } elseif (is_404()) {
        $current_title = esc_html__('404 - Page Not Found', 'fau-elemental');
    }

    // Truncate current page title if needed
    $truncated_current = strlen($current_title) > FAUE_BREADCRUMB_TITLE_MAX_LENGTH ? substr($current_title, 0, FAUE_BREADCRUMB_TITLE_MAX_LENGTH - 3) . '...' : $current_title;

    echo '<li class="breadcrumbs__item breadcrumbs__item--current breadcrumbs__item--desktop" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" aria-current="page">';
    echo '<span class="breadcrumbs__current" itemprop="item" title="' . esc_attr($current_title) . '">';
    echo '<span itemprop="name">' . esc_html($truncated_current) . '</span>';
    echo '</span>';
    echo '<meta itemprop="position" content="' . $position . '" />';
    echo '</li>';

    echo '</ol>';
    echo '</nav>';
    echo '</div>';
}