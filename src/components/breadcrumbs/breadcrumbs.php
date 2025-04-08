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
    } elseif (is_single()) {
        $categories = get_the_category();
        if ($categories) {
            $ancestors = array($categories[0]->term_id);
        }
    }

    // Start breadcrumb navigation
    echo '<nav class="breadcrumbs" aria-label="' . esc_attr__('Breadcrumb navigation', 'fau-elemental') . '">';
    echo '<ol class="breadcrumbs__list" itemscope itemtype="https://schema.org/BreadcrumbList">';

    // Home link
    echo '<li class="breadcrumbs__item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
    echo '<a href="' . esc_url(home_url('/')) . '" class="breadcrumbs__link" itemprop="item">';
    echo '<span itemprop="name">' . esc_html__('Home', 'fau-elemental') . '</span>';
    echo '</a>';
    echo '<meta itemprop="position" content="1" />';
    echo '</li>';

    $position = 2;
    $total_items = count($ancestors) + 1; // +1 for current page

    // Mobile: Show only parent link
    if (is_page() && !empty($ancestors)) {
        $parent = get_post(end($ancestors));
        echo '<li class="breadcrumbs__item breadcrumbs__item--mobile" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
        echo '<a href="' . esc_url(get_permalink($parent->ID)) . '" class="breadcrumbs__link" itemprop="item">';
        echo '<span itemprop="name">' . esc_html($parent->post_title) . '</span>';
        echo '</a>';
        echo '<meta itemprop="position" content="' . ($total_items - 1) . '" />';
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
        $truncated_title = strlen($title) > 50 ? substr($title, 0, 47) . '...' : $title;

        echo '<li class="breadcrumbs__item breadcrumbs__item--desktop" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
        echo '<a href="' . esc_url($url) . '" class="breadcrumbs__link" itemprop="item" title="' . esc_attr($title) . '">';
        echo '<span itemprop="name">' . esc_html($truncated_title) . '</span>';
        echo '</a>';
        echo '<meta itemprop="position" content="' . $position . '" />';
        echo '</li>';
        $position++;
    }

    // Current page
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
    $truncated_current = strlen($current_title) > 50 ? substr($current_title, 0, 47) . '...' : $current_title;

    echo '<li class="breadcrumbs__item breadcrumbs__item--current" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
    echo '<span class="breadcrumbs__current" itemprop="item" title="' . esc_attr($current_title) . '">';
    echo '<span itemprop="name">' . esc_html($truncated_current) . '</span>';
    echo '</span>';
    echo '<meta itemprop="position" content="' . $position . '" />';
    echo '</li>';

    echo '</ol>';
    echo '</nav>';
} 