<?php
/**
 * FAU Elemental Theme Functions
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

// Theme setup and core functionality
require_once get_template_directory() . '/inc/theme-setup.php';

// Asset management
require_once get_template_directory() . '/inc/enqueue-assets.php';

// Block functionality
require_once get_template_directory() . '/inc/blocks/loader.php';
require_once get_template_directory() . '/inc/block-patterns.php';

// Post Types
require_once get_template_directory() . '/inc/post-types/faq.php';

// Theme settings
require_once get_template_directory() . '/inc/theme-settings.php';

/**
 * Register search options menu location
 */
function fau_register_search_options_menu() {
    register_nav_menus(array(
        'search_options_menu' => __('Search Options Menu', 'fau-elemental')
    ));
}
add_action('init', 'fau_register_search_options_menu');

/**
 * Register REST API endpoint for search suggestions
 */
function fau_register_search_suggestions_endpoint() {
    register_rest_route('fau/v1', '/search-suggestions', array(
        'methods' => 'GET',
        'callback' => 'fau_get_search_suggestions',
        'permission_callback' => '__return_true',
        'args' => array(
            'search' => array(
                'required' => true,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ),
        ),
    ));
}
add_action('rest_api_init', 'fau_register_search_suggestions_endpoint');

/**
 * Get search suggestions
 */
function fau_get_search_suggestions($request) {
    $search_term = $request->get_param('search');
    
    $args = array(
        'post_type' => array('post', 'page'),
        'post_status' => 'publish',
        's' => $search_term,
        'posts_per_page' => 5,
        'orderby' => 'relevance',
    );
    
    $query = new WP_Query($args);
    $suggestions = array();
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $suggestions[] = array(
                'title' => get_the_title(),
                'link' => get_permalink(),
                'type' => get_post_type(),
            );
        }
        wp_reset_postdata();
    }
    
    return $suggestions;
}
