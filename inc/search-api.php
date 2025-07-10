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
    
    // Network-wide search for suggestions (title-only)
    if (is_multisite()) {
        // Get all sites in the network
        $sites = get_sites(array(
            'network_id' => get_current_network_id(),
            'public' => 1,
            'archived' => 0,
            'mature' => 0,
            'spam' => 0,
            'deleted' => 0,
            'number' => 10 // Limit to 10 sites for performance
        ));
        
        $current_blog_id = get_current_blog_id();
        
        // Search current site first (prioritize current site results - title only)
        $search_like = '%' . $wpdb->esc_like($search) . '%';
        $current_site_sql = $wpdb->prepare("
            SELECT DISTINCT ID, post_title, post_type, post_date
            FROM {$wpdb->posts} 
            WHERE post_status = 'publish' 
            AND post_type IN ('post', 'page')
            AND post_title LIKE %s
            ORDER BY 
                CASE WHEN post_title LIKE %s THEN 1 ELSE 2 END,
                post_date DESC 
            LIMIT 3
        ", $search_like, $wpdb->esc_like($search) . '%');
        
        $current_site_posts = $wpdb->get_results($current_site_sql);
        $processed_ids = array(); // Track processed post IDs to avoid duplicates
        
        foreach ($current_site_posts as $post) {
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
        
        // Search other sites (limit to 2 results from other sites - title only)
        $other_sites_count = 0;
        foreach ($sites as $site) {
            if ($site->blog_id === $current_blog_id || $other_sites_count >= 2) {
                continue;
            }
            
            if (switch_to_blog($site->blog_id)) {
                $site_table = $wpdb->get_blog_prefix($site->blog_id) . 'posts';
                $site_sql = $wpdb->prepare("
                    SELECT DISTINCT ID, post_title, post_type, post_date
                    FROM {$site_table} 
                    WHERE post_status = 'publish' 
                    AND post_type IN ('post', 'page')
                    AND post_title LIKE %s
                    ORDER BY 
                        CASE WHEN post_title LIKE %s THEN 1 ELSE 2 END,
                        post_date DESC 
                    LIMIT 1
                ", $search_like, $wpdb->esc_like($search) . '%');
                
                $site_posts = $wpdb->get_results($site_sql);
                
                foreach ($site_posts as $post) {
                    // Check for duplicate titles across sites
                    $duplicate_found = false;
                    foreach ($results as $existing_result) {
                        if (strtolower($existing_result['title']) === strtolower($post->post_title)) {
                            $duplicate_found = true;
                            break;
                        }
                    }
                    
                    if (!$duplicate_found) {
                        $results[] = array(
                            'title' => $post->post_title,
                            'link' => get_permalink($post->ID),
                            'type' => get_post_type_object($post->post_type)->labels->singular_name,
                            'site_name' => get_bloginfo('name'),
                            'is_current_site' => false
                        );
                        $other_sites_count++;
                    }
                    break; // Only one result per other site
                }
                
                restore_current_blog();
            }
        }
    } else {
        // Single site search fallback (title-only)
        $search_like = '%' . $wpdb->esc_like($search) . '%';
        $single_site_sql = $wpdb->prepare("
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
        
        $posts = $wpdb->get_results($single_site_sql);
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

/**
 * Track search queries when they happen (network-wide)
 */
function fau_elemental_track_search_query() {
    if (isset($_GET['s']) && !empty($_GET['s'])) {
        $search_term = sanitize_text_field($_GET['s']);
        
        // For network-wide tracking, store on main site
        if (is_multisite()) {
            $main_site_id = get_main_site_id();
            $current_blog_id = get_current_blog_id();
            
            // Switch to main site for network-wide tracking
            if ($main_site_id && $current_blog_id !== $main_site_id) {
                switch_to_blog($main_site_id);
            }
        }
        
        // Get current count from network-wide stats
        $searches = get_option('fau_elemental_network_search_stats', array());
        
        // Increment count for this term
        if (isset($searches[$search_term])) {
            $searches[$search_term]++;
        } else {
            $searches[$search_term] = 1;
        }
        
        // Keep only top 50 searches to prevent database bloat
        if (count($searches) > 50) {
            arsort($searches);
            $searches = array_slice($searches, 0, 50, true);
        }
        
        update_option('fau_elemental_network_search_stats', $searches);
        
        // Restore current blog if we switched
        if (is_multisite() && isset($main_site_id) && isset($current_blog_id) && $current_blog_id !== $main_site_id) {
            restore_current_blog();
        }
    }
}
add_action('template_redirect', 'fau_elemental_track_search_query');

/**
 * AJAX handler for frequent searches (network-wide)
 */
function fau_elemental_get_frequent_searches() {
    // For network-wide data, get from main site
    if (is_multisite()) {
        $main_site_id = get_main_site_id();
        $current_blog_id = get_current_blog_id();
        
        // Switch to main site for network-wide data
        if ($main_site_id && $current_blog_id !== $main_site_id) {
            switch_to_blog($main_site_id);
        }
    }
    
    // Get network-wide search statistics
    $searches = get_option('fau_elemental_network_search_stats', array());
    
    // Restore current blog if we switched
    if (is_multisite() && isset($main_site_id) && isset($current_blog_id) && $current_blog_id !== $main_site_id) {
        restore_current_blog();
    }
    
    if (empty($searches)) {
        // Return empty array if no search data available yet
        wp_send_json_success(array('searches' => array()));
        return;
    }
    
    // Sort by frequency and get top 5
    arsort($searches);
    $top_searches = array_slice(array_keys($searches), 0, 5);
    
    wp_send_json_success(array('searches' => $top_searches));
}
add_action('wp_ajax_get_frequent_searches', 'fau_elemental_get_frequent_searches');
add_action('wp_ajax_nopriv_get_frequent_searches', 'fau_elemental_get_frequent_searches'); 