<?php
/**
 * AJAX Handlers for FAU Elemental Theme
 *
 * @package FAU-Elemental
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ============================================================================
// FAU LIST FILTERS AJAX HANDLERS
// ============================================================================

/**
 * AJAX handler for fau-list-filters block filtering
 */
if (!function_exists('fau_filter_teaser_grid_ajax_handler')) {
    function fau_filter_teaser_grid_ajax_handler() {
        // Add error logging for debugging
        error_log('FAU Filter Debug - AJAX Handler Called');
        error_log('FAU Filter Debug - POST data: ' . print_r($_POST, true));
        
        // Verify nonce for security
        $nonce = $_POST['nonce'] ?? '';
        if (!wp_verify_nonce($nonce, 'fau_filter_nonce')) {
            error_log('FAU Filter Debug - Nonce verification failed. Provided: ' . $nonce);
            wp_send_json_error([
                'message' => 'Security check failed',
                'debug' => [
                    'provided_nonce' => $nonce,
                    'expected_action' => 'fau_filter_nonce'
                ]
            ]);
            return;
        }

        error_log('FAU Filter Debug - Nonce verification passed');

        // Get filter parameters
        $search = sanitize_text_field($_POST['search'] ?? '');
        $filters = $_POST['filters'] ?? '{}';
        $sort = sanitize_text_field($_POST['sort'] ?? 'date');
        $page = intval($_POST['page'] ?? 1);
        $per_page = intval($_POST['per_page'] ?? 15);
        $post_type = sanitize_text_field($_POST['post_type'] ?? 'post');
        $category = intval($_POST['category'] ?? 0);
        $grid_post_ids = $_POST['grid_post_ids'] ?? '[]';
        $pagination_enabled = filter_var($_POST['pagination_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $total_posts_override = intval($_POST['total_posts_override'] ?? 0);

        // Debug: Log incoming parameters
        error_log('FAU Filter Debug - Incoming Parameters:');
        error_log('  search: ' . $search);
        error_log('  filters (raw): ' . $filters);
        error_log('  sort: ' . $sort);
        error_log('  page: ' . $page);
        error_log('  per_page: ' . $per_page);
        error_log('  post_type: ' . $post_type);
        error_log('  category: ' . $category);
        error_log('  grid_post_ids (raw): ' . $grid_post_ids);
        error_log('  pagination_enabled: ' . ($pagination_enabled ? 'true' : 'false'));
        error_log('  total_posts_override: ' . $total_posts_override);

        // Decode JSON strings - handle escaped quotes from URLSearchParams
        $filters = stripslashes($filters);
        $filters = json_decode($filters, true);
        if (!is_array($filters)) {
            $filters = [];
        }

        $grid_post_ids = stripslashes($grid_post_ids);
        $grid_post_ids = json_decode($grid_post_ids, true);
        if (!is_array($grid_post_ids)) {
            $grid_post_ids = [];
        }

        // Debug: Log decoded parameters
        error_log('  filters (decoded): ' . print_r($filters, true));
        error_log('  grid_post_ids (decoded): ' . print_r($grid_post_ids, true));

        // Build query arguments with EXPLICIT post type filtering
        $args = [
            'post_type' => $post_type, // Explicitly set post type
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'paged' => $page,
        ];

        // IMPORTANT: When filters are active, search ALL content, not just grid content
        // This allows filters to find content across all pages
        $has_active_filters = !empty($search) || !empty(array_filter($filters)) || $category > 0;
        
        if (!$has_active_filters && !empty($grid_post_ids)) {
            // No filters active - limit to grid content for pagination
            $args['post__in'] = array_map('intval', $grid_post_ids);
            error_log('FAU Filter Debug - No filters: Limiting query to grid post IDs: ' . implode(', ', $args['post__in']));
        } else if ($has_active_filters) {
            // Filters are active - search ALL content, ignore grid limitations
            error_log('FAU Filter Debug - Filters active: Searching ALL content');
            // Don't set post__in when filters are active - we want to search everything
        }

        // Add search
        if (!empty($search)) {
            $args['s'] = $search;
        }

        // Add category filter - but only for posts
        if ($category > 0 && $post_type === 'post') {
            $args['cat'] = $category;
        }

        // Add sorting
        switch ($sort) {
            case 'title':
                $args['orderby'] = 'title';
                $args['order'] = 'ASC';
                break;
            case 'modified':
                $args['orderby'] = 'modified';
                $args['order'] = 'DESC';
                break;
            case 'date':
            default:
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
                break;
        }

        // Process filters
        $tax_queries = [];
        
        foreach ($filters as $filter_name => $filter_data) {
            $filter_type = $filter_data['type'] ?? '';
            $filter_value = $filter_data['value'] ?? '';

            if (empty($filter_value)) {
                continue;
            }

            switch ($filter_type) {
                case 'categories':
                    // Only apply category filter to posts
                    if ($post_type === 'post') {
                        $tax_queries[] = [
                            'taxonomy' => 'category',
                            'field' => 'slug',
                            'terms' => $filter_value,
                        ];
                    }
                    break;
                case 'tags':
                    // Only apply tag filter to posts
                    if ($post_type === 'post') {
                        $tax_queries[] = [
                            'taxonomy' => 'post_tag',
                            'field' => 'slug',
                            'terms' => $filter_value,
                        ];
                    }
                    break;
                case 'authors':
                    $author = get_user_by('slug', $filter_value);
                    if ($author) {
                        $args['author'] = $author->ID;
                    }
                    break;
                case 'years':
                    $args['date_query'] = [
                        [
                            'year' => intval($filter_value),
                        ],
                    ];
                    break;
            }
        }

        // Apply tax queries if we have any
        if (!empty($tax_queries)) {
            $args['tax_query'] = $tax_queries;
            if (count($tax_queries) > 1) {
                $args['tax_query']['relation'] = 'AND';
            }
        }

        // Execute query
        $query = new WP_Query($args);
        $posts = [];

        // Debug: Log query details
        error_log('FAU Filter Debug - Query Args: ' . print_r($args, true));
        error_log('FAU Filter Debug - SQL Query: ' . $query->request);
        error_log('FAU Filter Debug - Found Posts: ' . $query->found_posts);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                
                // Get post object for more reliable data access
                $post_object = get_post($post_id);
                if (!$post_object) {
                    error_log("FAU Filter Debug - Could not get post object for ID: $post_id");
                    continue;
                }
                
                // Use post object for more reliable post type check
                $actual_post_type = $post_object->post_type;
                error_log("FAU Filter Debug - Post ID: $post_id, Title: " . $post_object->post_title . ", Type: $actual_post_type");
                
                // CRITICAL SAFETY CHECK: Skip posts that don't match our expected post_type
                if ($actual_post_type !== $post_type) {
                    error_log("FAU Filter Debug - SKIPPING Post ID: $post_id because it's type '$actual_post_type' but we want '$post_type'");
                    continue;
                }
                
                // Get featured image
                $featured_image = '';
                if (has_post_thumbnail($post_id)) {
                    $featured_image = get_the_post_thumbnail_url($post_id, 'medium');
                }

                // Get categories - only for posts
                $categories = [];
                $category_names = [];
                if ($post_type === 'post') {
                    $categories = get_the_category($post_id);
                    $category_names = array_map(function($cat) { return $cat->name; }, $categories);
                }

                // Get excerpt with improved fallback using post object
                $excerpt = '';
                if (!empty($post_object->post_excerpt)) {
                    $excerpt = $post_object->post_excerpt;
                } else {
                    // Try to get content and create excerpt
                    $content = $post_object->post_content;
                    if (!empty($content)) {
                        // Remove shortcodes and trim
                        $content = strip_shortcodes($content);
                        $content = wp_strip_all_tags($content);
                        $excerpt = wp_trim_words($content, 20, '...');
                    }
                }
                
                // Fallback if still empty
                if (empty($excerpt)) {
                    $excerpt = 'Read more about ' . $post_object->post_title . '...';
                }

                // Debug: Log excerpt generation
                error_log("FAU Filter Debug - Post ID: $post_id, Excerpt: '$excerpt'");

                // Build post data using post object for reliability
                $post_data = [
                    'id' => $post_id,
                    'title' => $post_object->post_title,
                    'excerpt' => $excerpt,
                    'permalink' => get_permalink($post_id),
                    'featured_image' => $featured_image,
                    'categories' => $category_names,
                    'date' => get_the_date('', $post_id),
                    'author' => get_the_author_meta('display_name', $post_object->post_author),
                    'post_type' => $actual_post_type, // Ensure this is always set
                ];
                
                // Debug: Confirm post_type is in the array before adding to posts
                error_log("FAU Filter Debug - Post ID: $post_id, post_type in array: " . $post_data['post_type']);
                error_log("FAU Filter Debug - Post data keys: " . implode(', ', array_keys($post_data)));
                
                $posts[] = $post_data;
            }
            wp_reset_postdata();
        }

        // Debug: Log final response details
        error_log('FAU Filter Debug - Total posts in response: ' . count($posts));
        if (!empty($posts)) {
            error_log('FAU Filter Debug - First post in response: ' . print_r($posts[0], true));
        }
        
        // Prepare response in the format expected by the filter block JavaScript
        $response = [
            'success' => true,
            'posts' => $posts,
            'total' => $query->found_posts,
            'pages' => $query->max_num_pages,
            'current_page' => $page,
            'debug_args' => $args, // For debugging
        ];

        // Debug: Log response structure
        error_log('FAU Filter Debug - Response keys: ' . implode(', ', array_keys($response)));
        error_log('FAU Filter Debug - Response posts count: ' . count($response['posts']));
        error_log('FAU Filter Debug - About to send JSON response');

        wp_send_json($response);
    }
}

/**
 * Register AJAX handlers for fau-list-filters
 */
function fau_elemental_register_filter_ajax_handlers() {
    add_action('wp_ajax_fau_filter_teaser_grid', 'fau_filter_teaser_grid_ajax_handler');
    add_action('wp_ajax_nopriv_fau_filter_teaser_grid', 'fau_filter_teaser_grid_ajax_handler');
}

// Initialize the AJAX handlers
add_action('init', 'fau_elemental_register_filter_ajax_handlers'); 