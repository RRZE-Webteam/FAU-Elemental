<?php
/**
 * Search API Endpoints
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register REST API routes
 */
function fau_register_rest_routes() {
    register_rest_route('fau/v1', '/search-suggestions', array(
        'methods' => 'GET',
        'callback' => 'fau_get_search_suggestions',
        'permission_callback' => '__return_true',
        'args' => array(
            'search' => array(
                'required' => true,
                'sanitize_callback' => 'sanitize_text_field',
            ),
        ),
    ));
}
add_action('rest_api_init', 'fau_register_rest_routes');



/**
 * Get search suggestions (title-only search)
 *
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response The response object.
 */
function fau_get_search_suggestions($request) {
    $search = $request->get_param('search');
    if (empty($search)) {
        return rest_ensure_response(array());
    }
    
    global $wpdb;
    $results = array();
    
    // Single site search (title-only)
    $search_like = '%' . $wpdb->esc_like($search) . '%';
    $sql = $wpdb->prepare("
        SELECT DISTINCT ID, post_title, post_type, post_date
        FROM {$wpdb->posts} 
        WHERE post_status = 'publish' 
        AND post_type IN ('post', 'page')
        AND post_title LIKE %s
        ORDER BY 
            CASE WHEN post_title LIKE %s THEN 1 ELSE 2 END,
            post_date DESC 
        LIMIT 5
    ", $search_like, $wpdb->esc_like($search) . '%');
    
    $posts = $wpdb->get_results($sql);
    $processed_ids = array();
    
    foreach ($posts as $post) {
        if (!in_array($post->ID, $processed_ids)) {
            $results[] = array(
                'title' => $post->post_title,
                'link' => get_permalink($post->ID),
                'type' => get_post_type_object($post->post_type)->labels->singular_name,
                'site_name' => get_bloginfo('name'),
                'is_current_site' => true
            );
            $processed_ids[] = $post->ID;
        }
    }
    
    // Ensure we return a maximum of 5 results
    $results = array_slice($results, 0, 5);
    
    return rest_ensure_response($results);
}



/**
 * AJAX handler for search options menu
 */
function fau_elemental_get_search_options_menu() {
    // Verify nonce if provided
    if (isset($_POST['nonce']) && !empty($_POST['nonce'])) {
        if (!wp_verify_nonce($_POST['nonce'], 'fau_elemental_nonce')) {
            wp_die('Security check failed');
        }
    }
    
    // Get the search options menu
    $menu_html = '';
    if (has_nav_menu('search_options_menu')) {
        ob_start();
        wp_nav_menu(array(
            'theme_location' => 'search_options_menu',
            'container' => false,
            'menu_class' => 'fau-global-search__menu-list',
            'depth' => 2,
            'fallback_cb' => false,
        ));
        $menu_html = ob_get_clean();
        
        // Add header if menu exists
        if (!empty($menu_html)) {
            $menu_html = '<div class="fau-global-search__menu-header">' . esc_html__('Search Options', 'fau-elemental') . '</div>' . $menu_html;
        }
    }
    
    if (!empty($menu_html)) {
        wp_send_json_success(array('menu_html' => $menu_html));
    } else {
        wp_send_json_error(array('message' => __('No search options menu found', 'fau-elemental')));
    }
}
add_action('wp_ajax_get_search_options_menu', 'fau_elemental_get_search_options_menu');
add_action('wp_ajax_nopriv_get_search_options_menu', 'fau_elemental_get_search_options_menu');

 