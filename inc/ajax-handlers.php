<?php
/**
 * AJAX handlers for FAU Elemental theme.
 *
 * @package FAU_Elemental
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

        
        // Verify nonce for security
        $nonce = $_POST['nonce'] ?? '';
        if (!wp_verify_nonce($nonce, 'fau_filter_nonce')) {
            wp_send_json_error([
                'message' => 'Security check failed'
            ]);
            return;
        }

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
        } else if ($has_active_filters) {
            // Filters are active - search ALL content, ignore grid limitations
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



        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                
                // Get post object for more reliable data access
                $post_object = get_post($post_id);
                if (!$post_object) {
                    continue;
                }
                
                // Use post object for more reliable post type check
                $actual_post_type = $post_object->post_type;
                
                // CRITICAL SAFETY CHECK: Skip posts that don't match our expected post_type
                if ($actual_post_type !== $post_type) {
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
                    $excerpt = sprintf(__('Read more about %s...', 'fau-elemental'), $post_object->post_title);
                }



                // Include teaser-item.php to get the render function
                if (!function_exists('fau_elemental_render_teaser_item')) {
                    require_once get_template_directory() . '/components/blocks/fau-teaser-grid/teaser-item.php';
                }

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
                    'html_output' => fau_elemental_render_teaser_item($post_object, $post_type, [], 'h2'),
                ];
                
                $posts[] = $post_data;
            }
            wp_reset_postdata();
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

add_action( 'wp_ajax_fau_elemental_filter_posts', 'fau_elemental_filter_posts_callback' );
add_action( 'wp_ajax_nopriv_fau_elemental_filter_posts', 'fau_elemental_filter_posts_callback' );

if ( ! function_exists( 'fau_elemental_filter_posts_callback' ) ) {
	/**
	 * Handles filtering posts via AJAX.
	 */
	function fau_elemental_filter_posts_callback() {
		// Verify nonce for security
		$nonce = $_POST['nonce'] ?? '';
		if ( ! wp_verify_nonce( $nonce, 'fau_filter_nonce' ) ) {
			error_log( 'FAU Filter - Nonce verification failed.' );
			wp_send_json_error( [ 'message' => __( 'Security check failed.', 'fau-elemental' ) ], 403 );
			return;
		}

		// Sanitize and retrieve parameters
		$search               = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';
		$sort_order = isset( $_POST['sort'] ) ? sanitize_text_field( wp_unslash( $_POST['sort'] ) ) : 'date';
		$filters    = isset( $_POST['filters'] ) ? json_decode( stripslashes( sanitize_text_field( wp_unslash( $_POST['filters'] ) ) ), true ) : [];

		$args = [
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => isset( $_POST['posts_per_page'] ) ? absint( $_POST['posts_per_page'] ) : 6,
			'paged'          => $paged,
			's'              => $search_query,
		];

		// Apply sorting.
		switch ( $sort_order ) {
			case 'title':
				$args['orderby'] = 'title';
				$args['order']   = 'ASC';
				break;
			case 'modified':
				$args['orderby'] = 'modified';
				$args['order']   = 'DESC';
				break;
			case 'date':
			default:
				$args['orderby'] = 'date';
				$args['order']   = 'DESC';
				break;
		}
		
		// Apply taxonomy and author filters.
		$tax_query = [];
		if ( ! empty( $filters ) ) {
			foreach ( $filters as $key => $filter ) {
				if ( ! empty( $filter['value'] ) ) {
					if ( 'author' === $filter['type'] ) {
						$args['author_name'] = $filter['value'];
					} else {
						$tax_query[] = [
							'taxonomy' => $filter['type'] === 'categories' ? 'category' : 'post_tag',
							'field'    => 'slug',
							'terms'    => $filter['value'],
						];
					}
				}
			}
		}

		if ( count( $tax_query ) > 1 ) {
			$tax_query['relation'] = 'AND';
		}
		if ( ! empty( $tax_query ) ) {
			$args['tax_query'] = $tax_query;
		}

		$query = new WP_Query( $args );

		// The fau_elemental_render_teaser_item function is in this file.
		require_once get_template_directory() . '/components/blocks/fau-teaser-grid/teaser-item.php';

		$posts_html = [];
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				// Directly call the render function for the post.
				$posts_html[] = [
					'id' => get_the_ID(),
					'html_output' => fau_elemental_render_teaser_item( get_post(), 'post', [], 'h2' ),
				];
			}
		} 
		
		wp_reset_postdata();

		wp_send_json_success( [
			'posts'         => $posts_html,
			'total_posts'   => (int) $query->found_posts,
			'total_pages'   => (int) $query->max_num_pages,
		] );
	}
} 