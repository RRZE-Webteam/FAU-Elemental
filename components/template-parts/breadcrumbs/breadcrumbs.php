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
 * Add dark mode class to the parent block group
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
 * Display breadcrumb navigation
 */
function faue_breadcrumbs(): void {
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
            // Try to find primary category or use the first one
            $primary_category = null;
            
            // Check for Yoast SEO primary category
            if (class_exists('WPSEO_Primary_Term')) {
                // @phpstan-ignore-next-line - WPSEO_Primary_Term and get_the_ID() are WordPress plugin/core functions
                $wpseo_primary_term = new WPSEO_Primary_Term('category', get_the_ID());
                $primary_category_id = $wpseo_primary_term->get_primary_term();
                if ($primary_category_id) {
                    $primary_category = get_category($primary_category_id);
                }
            }
            
            // If no primary category, use the first category or find the most specific one
            if (!$primary_category) {
                $primary_category = $categories[0];
                // Prefer categories with fewer posts (more specific)
                foreach ($categories as $category) {
                    if ($category->count < $primary_category->count) {
                        $primary_category = $category;
                    }
                }
            }
            
            // Build ancestor chain for the selected category
            $category_ancestors = get_ancestors($primary_category->term_id, 'category');
            $category_ancestors = array_reverse($category_ancestors);
            $category_ancestors[] = $primary_category->term_id;
            $ancestors = $category_ancestors;
        }
    }

    // Get breadcrumb mode from customizer
    $mode = get_theme_mod('faue_breadcrumb_variant_blue');
    
    // Determine if we should render mobile or desktop version
    // @phpstan-ignore-next-line - wp_is_mobile() is a valid WordPress function
    $is_mobile = function_exists('wp_is_mobile') ? wp_is_mobile() : false;

    // Start breadcrumb navigation
    $wrapper_classes = array('breadcrumbs-wrapper');
    if ($mode) {
        $wrapper_classes[] = 'is-style-dark';
    }
    
    echo '<div class="' . esc_attr(implode(' ', $wrapper_classes)) . '">';
    echo '<nav class="breadcrumbs" aria-label="' . esc_attr__('Breadcrumb navigation', 'fau-elemental') . '">';
    echo '<ol class="breadcrumbs__list" itemscope itemtype="https://schema.org/BreadcrumbList">';

    $position = 1;
    $total_items = count($ancestors) + 1; // +1 for current page

    if ($is_mobile) {
        // MOBILE VERSION: Show only parent (or Start if no parent)
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
            $truncated_parent = strlen($parent_title) > FAUE_BREADCRUMB_TITLE_MAX_LENGTH ? mb_strimwidth($parent_title, 0, FAUE_BREADCRUMB_TITLE_MAX_LENGTH, '…', 'UTF-8') : $parent_title;

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
    } else {
        // DESKTOP VERSION: Show full hierarchy
        
        // Home link
        echo '<li class="breadcrumbs__item breadcrumbs__item--desktop" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
        echo '<a href="' . esc_url(home_url('/')) . '" class="breadcrumbs__link" itemprop="item">';
        echo '<span itemprop="name">' . esc_html__('Start', 'fau-elemental') . '</span>';
        echo '</a>';
        echo '<meta itemprop="position" content="1" />';
        echo '</li>';
        
        $position = 2;

        // Show all ancestors
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
            $truncated_title = strlen($title) > FAUE_BREADCRUMB_TITLE_MAX_LENGTH ? mb_strimwidth($title, 0, FAUE_BREADCRUMB_TITLE_MAX_LENGTH, '…', 'UTF-8') : $title;

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
        $truncated_current = strlen($current_title) > FAUE_BREADCRUMB_TITLE_MAX_LENGTH ? mb_strimwidth($current_title, 0, FAUE_BREADCRUMB_TITLE_MAX_LENGTH, '…', 'UTF-8') : $current_title;

        echo '<li class="breadcrumbs__item breadcrumbs__item--current breadcrumbs__item--desktop" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" aria-current="page">';
        echo '<span class="breadcrumbs__current" itemprop="item" title="' . esc_attr($current_title) . '">';
        echo '<span itemprop="name">' . esc_html($truncated_current) . '</span>';
        echo '</span>';
        echo '<meta itemprop="position" content="' . $position . '" />';
        echo '</li>';
    }

    echo '</ol>';
    echo '</nav>';
    echo '</div>';
}