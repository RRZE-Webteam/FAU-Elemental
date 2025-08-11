<?php
/**
 * Image Links Migration
 * 
 * Provides backward compatibility for image links custom post type 
 * from previous FAU themes
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main migration orchestrator - synchronizes with current imagelink posts
 */
function fau_elemental_migrate_image_links() {
    $current_posts = fau_elemental_get_current_imagelink_posts();
    
    if (empty($current_posts)) {
        // Clear any existing migrated data since no imagelinks exist
        update_option('fau_elemental_migrated_image_links', array());
        return;
    }
    
    $migrated_logos = fau_elemental_process_imagelink_posts($current_posts);
    fau_elemental_save_current_migrated_data($migrated_logos, count($current_posts));
}

/**
 * Get all current imagelink posts that exist in the database
 */
function fau_elemental_get_current_imagelink_posts() {
    $current_posts = get_posts(array(
        'post_type' => 'imagelink',
        'posts_per_page' => -1,
        'post_status' => array('publish', 'private', 'draft')
    ));
    
    return $current_posts;
}

/**
 * Process an array of imagelink posts and convert them to logo data
 */
function fau_elemental_process_imagelink_posts($posts) {
    $migrated_logos = array();
    
    foreach ($posts as $post) {
        $logo_data = fau_elemental_convert_post_to_logo($post);
        if ($logo_data) {
            $migrated_logos[] = $logo_data;
        }
    }
    
    return $migrated_logos;
}

/**
 * Convert a single imagelink post to logo data format
 */
function fau_elemental_convert_post_to_logo($post) {
    $thumbnail_id = get_post_thumbnail_id($post->ID);
    $thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'medium') : '';
    $link_url = fau_elemental_extract_link_url($post);
    $category_data = fau_elemental_get_category_data($post->ID);
    
    // Only migrate if we have an image or a link
    if (!$thumbnail_id && empty($link_url)) {
        return null;
    }
    
    return array(
        'imageId' => $thumbnail_id,
        'imageUrl' => $thumbnail_url,
        'link' => $link_url,
        'category' => $category_data['name'],
        'originalId' => $post->ID,
        'originalCategoryId' => $category_data['id'],
        'migrated' => true
    );
}

/**
 * Extract link URL from post meta fields or content
 */
function fau_elemental_extract_link_url($post) {
    $url_fields = array(
        'fauval_imagelink_url',    // Found in debug - correct field for current site
        'imagelink_url',           // Common pattern
        'url',                     // Generic
        'link_url', 
        'link', 
        'href', 
        'target_url'
    );
    
    // Check meta fields first
    foreach ($url_fields as $field) {
        $url = get_post_meta($post->ID, $field, true);
        if (!empty($url)) {
            return $url;
        }
    }
    
    // Fallback: check if post content is a URL
    if (filter_var($post->post_content, FILTER_VALIDATE_URL)) {
        return $post->post_content;
    }
    
    return '';
}

/**
 * Get category data for a post (name and ID)
 */
function fau_elemental_get_category_data($post_id) {
    if (!taxonomy_exists('imagelinks_category')) {
        return array('name' => '', 'id' => 0);
    }
    
    $categories = wp_get_post_terms($post_id, 'imagelinks_category');
    if (is_wp_error($categories) || empty($categories)) {
        return array('name' => '', 'id' => 0);
    }
    
    return array(
        'name' => $categories[0]->name,
        'id' => $categories[0]->term_id
    );
}

/**
 * Save the current migrated data (replaces all previous data)
 */
function fau_elemental_save_current_migrated_data($migrated_logos, $total_count) {
    update_option('fau_elemental_migrated_image_links', $migrated_logos);
}

/**
 * Get migrated image links for use in logo grid blocks
 * 
 * @return array Array of migrated logo data
 */
function fau_elemental_get_migrated_image_links() {
    return get_option('fau_elemental_migrated_image_links', array());
}

/**
 * Check if there are migrated image links available
 * 
 * @return bool True if migrated links exist, false otherwise
 */
function fau_elemental_has_migrated_image_links() {
    $migrated_links = fau_elemental_get_migrated_image_links();
    return !empty($migrated_links);
}

/**
 * Manual migration function that can be called from admin
 * 
 * @param bool $force Whether to force migration even if already done
 * @return bool True if migration was performed, false otherwise
 */
function fau_elemental_force_image_links_migration($force = false) {
    if ($force) {
        delete_option('fau_elemental_migrated_image_links');
    }
    
    fau_elemental_migrate_image_links();
    
    return fau_elemental_has_migrated_image_links();
}

/**
 * Check if we should run scheduled migration and run it safely
 */
function fau_elemental_run_scheduled_migration() {
    if (get_option('fau_elemental_schedule_image_links_migration', false)) {
        delete_option('fau_elemental_schedule_image_links_migration');
        fau_elemental_migrate_image_links();
    }
}
// Run migration after WordPress is fully loaded
add_action('wp_loaded', 'fau_elemental_run_scheduled_migration');

/**
 * Register imagelinks_category taxonomy for backward compatibility
 */
function fau_elemental_register_imagelinks_taxonomy() {
    // Only register if it doesn't already exist
    if (!taxonomy_exists('imagelinks_category')) {
        register_taxonomy(
            'imagelinks_category',
            'imagelink',
            array(
                'hierarchical' => true,
                'query_var' => true,
                'show_tagcloud' => false,
                'show_in_quick_edit' => false,
                'show_in_rest' => false,
                'show_in_nav_menus' => false,
                'rewrite' => array(
                    'slug' => 'imagelinks',
                    'with_front' => false
                ),
                'labels' => array(
                    'name' => __('Image Link Categories', 'fau-elemental'),
                    'singular_name' => __('Image Link Category', 'fau-elemental'),
                    'menu_name' => __('Categories', 'fau-elemental'),
                )
            )
        );
    }
}
add_action('init', 'fau_elemental_register_imagelinks_taxonomy', 0);

/**
 * Content filter to convert [imagelink] shortcodes to logo grid blocks
 * This provides backward compatibility for old content
 */
function fau_elemental_convert_imagelink_shortcodes($content) {
    // Only process if content contains the shortcode
    if (strpos($content, '[imagelink') === false) {
        return $content;
    }

    // Get the shortcode regex pattern
    $pattern = get_shortcode_regex(array('imagelink'));
    
    // Replace shortcodes with block markup
    $content = preg_replace_callback('/' . $pattern . '/', 'fau_elemental_convert_imagelink_shortcode_to_block', $content);
    
    return $content;
}
add_filter('the_content', 'fau_elemental_convert_imagelink_shortcodes', 20);

/**
 * Convert a single [imagelink] shortcode to block markup
 */
function fau_elemental_convert_imagelink_shortcode_to_block($matches) {
    $shortcode = $matches[0];
    $atts = shortcode_parse_atts($matches[3]);
    
    // Parse attributes - only support what the new block actually uses
    $category = isset($atts['cat']) ? $atts['cat'] : '';
    $catid = isset($atts['catid']) ? intval($atts['catid']) : 0;
    
    // Determine category ID
    $category_id = 0;
    if ($catid > 0) {
        $category_id = $catid;
    } elseif (!empty($category)) {
        if (taxonomy_exists('imagelinks_category')) {
            $term = get_term_by('name', $category, 'imagelinks_category');
            if (!$term) {
                $term = get_term_by('slug', $category, 'imagelinks_category');
            }
            if ($term) {
                $category_id = $term->term_id;
            }
        }
    }
    
    // Get logos
    $logos = array();
    
    // Try migrated data first
    if (function_exists('fau_elemental_get_migrated_image_links')) {
        $migrated_links = fau_elemental_get_migrated_image_links();
        
        if ($category_id > 0) {
            // Filter by category - only return logos from the specified category
            foreach ($migrated_links as $link) {
                if (isset($link['originalCategoryId']) && $link['originalCategoryId'] == $category_id) {
                    $logos[] = array(
                        'imageId' => $link['imageId'] ?? 0,
                        'imageUrl' => $link['imageUrl'] ?? '',
                        'link' => $link['link'] ?? '',
                        'category' => $link['category'] ?? '',
                        'title' => $link['title'] ?? '',
                        'migrated' => true
                    );
                }
            }
            
            // If no logos found for the specific category, return empty
            if (empty($logos)) {
                return '';
            }
        } else {
            // No category specified - use all migrated links
            foreach ($migrated_links as $link) {
                $logos[] = array(
                    'imageId' => $link['imageId'] ?? 0,
                    'imageUrl' => $link['imageUrl'] ?? '',
                    'link' => $link['link'] ?? '',
                    'category' => $link['category'] ?? '',
                    'title' => $link['title'] ?? '',
                    'migrated' => true
                );
            }
        }
    }
    
    // If no migrated data found, try current posts
    if (empty($logos) && post_type_exists('imagelink')) {
        $args = array(
            'post_type' => 'imagelink',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'name',
            'order' => 'ASC'
        );
        
        // Only add taxonomy query if a specific category is requested
        if ($category_id > 0 && taxonomy_exists('imagelinks_category')) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'imagelinks_category',
                    'field' => 'term_id',
                    'terms' => $category_id
                )
            );
        }
        
        $imagelink_posts = get_posts($args);
        
        foreach ($imagelink_posts as $post) {
            $logo_data = fau_elemental_convert_post_to_logo($post);
            if ($logo_data) {
                $logos[] = array(
                    'imageId' => $logo_data['imageId'],
                    'imageUrl' => $logo_data['imageUrl'],
                    'link' => $logo_data['link'],
                    'category' => $logo_data['category'],
                    'title' => $logo_data['title'] ?? '',
                    'migrated' => true
                );
            }
        }
    }
    
    // If no logos found, return empty
    if (empty($logos)) {
        return '';
    }
    
    // Build block attributes - only include what the block actually supports
    $block_attributes = array(
        'logos' => $logos
    );
    
    // Create block markup
    $block_markup = '<!-- wp:fau/logo-grid ' . json_encode($block_attributes) . ' -->';
    $block_markup .= '<div class="fau-logo-grid">';
    $block_markup .= '<div class="fau-logo-grid__container">';
    
    foreach ($logos as $logo) {
        if (empty($logo['imageUrl'])) {
            continue;
        }
        
        $block_markup .= '<div class="fau-logo-grid__item">';
        
        if (!empty($logo['link'])) {
            $block_markup .= '<a href="' . esc_url($logo['link']) . '" class="fau-logo-grid__link">';
        }
        
        $block_markup .= '<img src="' . esc_url($logo['imageUrl']) . '" alt="" class="fau-logo-grid__image" loading="lazy" />';
        
        if (!empty($logo['link'])) {
            $block_markup .= '</a>';
        }
        
        $block_markup .= '</div>';
    }
    
    $block_markup .= '</div>';
    $block_markup .= '</div>';
    $block_markup .= '<!-- /wp:fau-elemental/fau-logo-grid -->';
    
    return $block_markup;
} 