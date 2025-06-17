<?php
/**
 * REST API Endpoints
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
    register_rest_route('fau/v1', '/frequent-queries', array(
        'methods' => 'GET',
        'callback' => 'fau_get_frequent_queries',
        'permission_callback' => '__return_true',
        'args' => array(
            'per_page' => array(
                'default' => 5,
                'sanitize_callback' => 'absint',
            ),
        ),
    ));

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
 * Get frequent queries
 * This function retrieves the most frequently searched queries from the search logs
 */
function fau_get_frequent_queries($request) {
    $per_page = $request->get_param('per_page');
    
    // Get search logs from options
    $search_logs = get_option('fau_search_logs', array());
    
    // Sort by frequency
    arsort($search_logs);
    
    // Get top queries
    $frequent_queries = array_slice($search_logs, 0, $per_page, true);
    
    // Format the response
    $queries = array();
    foreach ($frequent_queries as $query => $count) {
        $queries[] = array(
            'title' => $query,
            'link' => home_url('/?s=' . urlencode($query)),
            'count' => $count
        );
    }
    
    return rest_ensure_response($queries);
}

/**
 * Get search suggestions
 *
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response The response object.
 */
function fau_get_search_suggestions($request) {
    $search = $request->get_param('search');
    if (empty($search)) {
        return rest_ensure_response(array());
    }
    
    $args = array(
        'post_type' => array('post', 'page'),
        'post_status' => 'publish',
        'posts_per_page' => 5,
        's' => $search,
    );
    
    $query = new WP_Query($args);
    $results = array();
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $results[] = array(
                'title' => get_the_title(),
                'link' => get_permalink(),
            );
        }
    }
    wp_reset_postdata();
    
    return rest_ensure_response($results);
}

/**
 * Log search queries
 */
function fau_log_search_query($query) {
    if (!is_admin() && !empty($query) && is_search()) {
        $search_logs = get_option('fau_search_logs', array());
        $search_term = sanitize_text_field($query);
        
        if (!empty($search_term)) {
            if (isset($search_logs[$search_term])) {
                $search_logs[$search_term]++;
            } else {
                $search_logs[$search_term] = 1;
            }
            
            // Keep only the top 100 searches
            arsort($search_logs);
            $search_logs = array_slice($search_logs, 0, 100, true);
            
            update_option('fau_search_logs', $search_logs);
        }
    }
}
add_action('pre_get_posts', function($query) {
    if (!is_admin() && $query->is_main_query() && is_search()) {
        fau_log_search_query(get_search_query());
    }
});