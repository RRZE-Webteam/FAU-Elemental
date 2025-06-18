<?php
/**
 * Server-side rendering of the `fau-elemental/fau-list-filters` block.
 *
 * @package FAU_Elemental
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'render_block_fau_list_filters' ) ) {
    /**
     * Renders the `fau-elemental/fau-list-filters` block on the server.
     *
     * @param array    $attributes Block attributes.
     * @param string   $content    Block default content.
     * @param WP_Block $block      Block instance.
     * @return string Returns the list filters HTML.
     */
    function render_block_fau_list_filters( $attributes, $content, $block ) {
        // Extract attributes with defaults
        $enable_search = $attributes['enableSearch'] ?? true;
        $search_placeholder = $attributes['searchPlaceholder'] ?? __('Search...', 'fau-elemental');
        $enable_filters = $attributes['enableFilters'] ?? true;
        $filter_fields = $attributes['filterFields'] ?? [];
        $show_more_filters = $attributes['showMoreFiltersButton'] ?? false;
        $enable_view_switcher = $attributes['enableViewSwitcher'] ?? true;
        $available_views = $attributes['availableViews'] ?? ['cards', 'table'];
        $default_view = $attributes['defaultView'] ?? 'cards';
        $enable_sorting = $attributes['enableSorting'] ?? true;
        $sort_options = $attributes['sortOptions'] ?? [
            ['value' => 'date', 'label' => __('Date', 'fau-elemental')],
            ['value' => 'title', 'label' => __('Alphabetic', 'fau-elemental')],
            ['value' => 'modified', 'label' => __('Last Modified', 'fau-elemental')]
        ];
        $default_sort = $attributes['defaultSort'] ?? 'date';
        $show_results_count = $attributes['showResultsCount'] ?? true;
        $results_per_page = $attributes['resultsPerPage'] ?? 15;
        $grid_width = $attributes['gridWidth'] ?? '12';

        // Generate unique ID for this block instance
        $block_id = 'fau-list-filters-' . uniqid();

        // Try to find associated teaser grid to scope filter options
        $scoped_post_type = 'post';
        $scoped_category = 0;
        
        // Look for nearby teaser grid blocks to get their configuration
        if (isset($block->context['postId'])) {
            $post_content = get_post_field('post_content', $block->context['postId']);
            if ($post_content) {
                // Parse blocks to find teaser grids
                $blocks = parse_blocks($post_content);
                foreach ($blocks as $parsed_block) {
                    if ($parsed_block['blockName'] === 'fau-elemental/fau-teaser-grid') {
                        $attrs = $parsed_block['attrs'] ?? [];
                        if (!empty($attrs['enableFilterIntegration'])) {
                            $scoped_post_type = $attrs['variant'] ?? 'post';
                            $scoped_category = $attrs['selectedCategory'] ?? 0;
                            break;
                        }
                    }
                }
            }
        }

        // Get scoped filter options based on teaser grid configuration
        $available_filter_options = fau_get_scoped_filter_options($scoped_post_type, $scoped_category);

        // Start building the output
        $wrapper_attributes = get_block_wrapper_attributes([
            'class' => "fau-list-filters grid-width-{$grid_width}",
            'id' => $block_id,
            'data-block-id' => $block_id,
            'data-results-per-page' => $results_per_page
        ]);

        $output = sprintf('<div %s>', $wrapper_attributes);
        
        // Search Section
        if ($enable_search) {
            $output .= fau_list_filters_render_search_section($search_placeholder, $block_id);
        }

        // Filter Section
        if ($enable_filters) {
            $output .= fau_list_filters_render_filter_section(
                $filter_fields, 
                $show_more_filters, 
                $block_id, 
                $available_filter_options
            );
        }

        // Sort Section
        $output .= fau_list_filters_render_sort_section(
            $enable_view_switcher, 
            $available_views, 
            $default_view, 
            $enable_sorting, 
            $sort_options, 
            $default_sort,
            $show_results_count,
            $results_per_page,
            $block_id
        );

        $output .= '</div>';

        // Add AJAX data for frontend filtering
        if (!wp_script_is('fau-list-filters-script', 'enqueued')) {
            wp_enqueue_script('fau-list-filters-script', get_template_directory_uri() . '/build/blocks/fau-list-filters/view.js', [], '1.0.0', true);
        }
        
        wp_localize_script('fau-list-filters-script', 'fauListFilters', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('fau_filter_nonce'),
        ]);

        return $output;
    }
}

if ( ! function_exists( 'fau_get_available_filter_options' ) ) {
    /**
     * Get available filter options from WordPress content.
     *
     * @return array Available filter options.
     */
    function fau_get_available_filter_options() {
        $filter_options = [
            'categories' => [
                'label' => __('Categories', 'fau-elemental'),
                'options' => []
            ],
            'tags' => [
                'label' => __('Tags', 'fau-elemental'),
                'options' => []
            ],
            'post_types' => [
                'label' => __('Content Types', 'fau-elemental'),
                'options' => []
            ],
            'authors' => [
                'label' => __('Authors', 'fau-elemental'),
                'options' => []
            ],
            'years' => [
                'label' => __('Year', 'fau-elemental'),
                'options' => []
            ]
        ];

        // Get categories
        $categories = get_categories(['hide_empty' => true]);
        foreach ($categories as $category) {
            $filter_options['categories']['options'][] = [
                'value' => $category->slug,
                'label' => $category->name,
                'count' => $category->count
            ];
        }

        // Get tags
        $tags = get_tags(['hide_empty' => true]);
        foreach ($tags as $tag) {
            $filter_options['tags']['options'][] = [
                'value' => $tag->slug,
                'label' => $tag->name,
                'count' => $tag->count
            ];
        }

        // Get post types
        $post_types = get_post_types(['public' => true], 'objects');
        foreach ($post_types as $post_type) {
            if ($post_type->name !== 'attachment') {
                $count = wp_count_posts($post_type->name);
                $filter_options['post_types']['options'][] = [
                    'value' => $post_type->name,
                    'label' => $post_type->labels->name,
                    'count' => $count->publish ?? 0
                ];
            }
        }

        // Get authors
        $authors = get_users(['who' => 'authors']);
        foreach ($authors as $author) {
            $post_count = count_user_posts($author->ID);
            if ($post_count > 0) {
                $filter_options['authors']['options'][] = [
                    'value' => $author->user_nicename,
                    'label' => $author->display_name,
                    'count' => $post_count
                ];
            }
        }

        // Get years from posts
        global $wpdb;
        $years = $wpdb->get_results("
            SELECT DISTINCT YEAR(post_date) as year, COUNT(*) as count 
            FROM {$wpdb->posts} 
            WHERE post_status = 'publish' 
            AND post_type IN ('post', 'page') 
            GROUP BY YEAR(post_date) 
            ORDER BY year DESC
        ");
        
        foreach ($years as $year_data) {
            $filter_options['years']['options'][] = [
                'value' => $year_data->year,
                'label' => $year_data->year,
                'count' => $year_data->count
            ];
        }

        return apply_filters('fau_list_filters_available_options', $filter_options);
    }
}

if ( ! function_exists( 'fau_list_filters_render_search_section' ) ) {
    /**
     * Renders the search section of the list filters.
     *
     * @param string $placeholder The search placeholder text.
     * @param string $block_id The unique block ID.
     * @return string The search section HTML.
     */
    function fau_list_filters_render_search_section($placeholder, $block_id) {
        $search_id = $block_id . '-search';
        
        $output = '<div class="fau-list-filters__search-section">';
        $output .= '<div class="search-wrapper">';
        $output .= sprintf(
            '<input type="search" id="%s" class="search-input" placeholder="%s" aria-label="%s" />',
            esc_attr($search_id),
            esc_attr($placeholder),
            esc_attr__('Search content', 'fau-elemental')
        );
        $output .= '<button type="button" class="search-clear" aria-label="' . esc_attr__('Clear search', 'fau-elemental') . '" style="display: none;"></button>';
        $output .= '</div>';
        $output .= '</div>';

        return $output;
    }
}

if ( ! function_exists( 'fau_list_filters_render_filter_section' ) ) {
    /**
     * Renders the filter section of the list filters.
     *
     * @param array  $filter_fields The filter field configurations.
     * @param bool   $show_more_filters Whether to show the "show more" button.
     * @param string $block_id The unique block ID.
     * @param array  $available_options Available filter options from WordPress.
     * @return string The filter section HTML.
     */
    function fau_list_filters_render_filter_section($filter_fields, $show_more_filters, $block_id, $available_options) {
        $output = '<div class="fau-list-filters__filter-section">';
        
        // Filter controls
        $output .= '<div class="filter-controls">';
        
        // Render configured filter fields
        foreach ($filter_fields as $index => $field) {
            $filter_id = $block_id . '-filter-' . $index;
            $field_name = $field['name'] ?? 'Filter ' . ($index + 1);
            $field_options = $field['options'] ?? [];
            $is_hidden = $show_more_filters && $index >= 3; // Hide filters after the 3rd one initially
            
            $filter_class = 'filter-field filter-field--configured';
            if ($is_hidden) {
                $filter_class .= ' hidden';
            }
            
            $output .= sprintf('<div class="%s">', esc_attr($filter_class));
            $output .= sprintf(
                '<label for="%s" class="filter-label">%s</label>',
                esc_attr($filter_id),
                esc_html($field_name)
            );
            $output .= sprintf(
                '<select id="%s" class="filter-select" data-filter-name="%s" data-filter-type="configured">',
                esc_attr($filter_id),
                esc_attr($field_name)
            );
            $output .= sprintf(
                '<option value="">%s</option>',
                esc_html(sprintf(__('All %s', 'fau-elemental'), $field_name))
            );
            
            foreach ($field_options as $option) {
                $output .= sprintf(
                    '<option value="%s">%s</option>',
                    esc_attr($option['value']),
                    esc_html($option['label'])
                );
            }
            
            $output .= '</select>';
            $output .= '</div>';
        }
        
        // Container for dynamically added filters (initially hidden)
        $output .= '<div class="dynamic-filters-container" style="display: none;"></div>';
        
        // Show more filters button (only if there are available filters to add)
        $configured_filter_types = array_column($filter_fields, 'type');
        $available_filter_types = array_keys($available_options);
        $unused_filters = array_diff($available_filter_types, $configured_filter_types);
        
        if (!empty($unused_filters)) {
            $output .= '<button type="button" class="show-more-filters" aria-expanded="false" data-available-filters="' . esc_attr(json_encode($available_options)) . '">';
            $output .= '<span class="show-more-text">' . esc_html__('Weitere Filtermöglichkeiten', 'fau-elemental') . ' +</span>';
            $output .= '<span class="show-less-text" style="display: none;">' . esc_html__('Weniger Filter', 'fau-elemental') . ' -</span>';
            $output .= '</button>';
        }
        
        $output .= '</div>'; // Close filter-controls
        
        // Active filters (chips)
        $output .= '<div class="active-filters" style="display: none;">';
        $output .= '<div class="active-filters__header">';
        $output .= '<span class="active-filters__label">' . esc_html__('Active filters:', 'fau-elemental') . '</span>';
        $output .= '</div>';
        $output .= '<div class="filter-chips"></div>';
        $output .= '<button type="button" class="clear-all-filters" style="display: none;">';
        $output .= '<span class="clear-all-text">' . esc_html__('Clear all', 'fau-elemental') . '</span>';
        $output .= '</button>';
        $output .= '</div>';
        
        $output .= '</div>'; // Close filter-section

        return $output;
    }
}

if ( ! function_exists( 'fau_list_filters_render_sort_section' ) ) {
    /**
     * Renders the sort section of the list filters.
     *
     * @param bool   $enable_view_switcher Whether view switching is enabled.
     * @param array  $available_views Available view options.
     * @param string $default_view The default view.
     * @param bool   $enable_sorting Whether sorting is enabled.
     * @param array  $sort_options Available sort options.
     * @param string $default_sort The default sort option.
     * @param bool   $show_results_count Whether to show results count.
     * @param int    $results_per_page Results per page.
     * @param string $block_id The unique block ID.
     * @return string The sort section HTML.
     */
    function fau_list_filters_render_sort_section($enable_view_switcher, $available_views, $default_view, $enable_sorting, $sort_options, $default_sort, $show_results_count, $results_per_page, $block_id) {
        $output = '<div class="fau-list-filters__sort-section">';
        
        // Results count
        if ($show_results_count) {
            $output .= '<div class="results-count" role="status" aria-live="polite">';
            $output .= '<span class="results-text">' . esc_html__('Loading results...', 'fau-elemental') . '</span>';
            $output .= '</div>';
        }
        
        // Sort and view controls
        $output .= '<div class="sort-controls">';
        
        // View switcher
        if ($enable_view_switcher && count($available_views) > 1) {
            $output .= '<div class="view-switcher" role="group" aria-label="' . esc_attr__('Switch view', 'fau-elemental') . '">';
            
            foreach ($available_views as $view) {
                $view_id = $block_id . '-view-' . $view;
                $is_active = $view === $default_view;
                $button_class = 'view-button';
                if ($is_active) {
                    $button_class .= ' active';
                }
                
                $view_labels = [
                    'cards' => __('Cards', 'fau-elemental'),
                    'table' => __('Table', 'fau-elemental'),
                    'list' => __('List', 'fau-elemental')
                ];
                
                $output .= sprintf(
                    '<button type="button" class="%s" id="%s" data-view="%s" aria-pressed="%s" title="%s">',
                    esc_attr($button_class),
                    esc_attr($view_id),
                    esc_attr($view),
                    $is_active ? 'true' : 'false',
                    esc_attr($view_labels[$view] ?? ucfirst($view))
                );
                $output .= sprintf(
                    '<span class="view-icon view-icon-%s" aria-hidden="true"></span>',
                    esc_attr($view)
                );
                $output .= sprintf(
                    '<span class="view-label sr-only">%s</span>',
                    esc_html($view_labels[$view] ?? ucfirst($view))
                );
                $output .= '</button>';
            }
            
            $output .= '</div>';
        }
        
        // Sort dropdown
        if ($enable_sorting && !empty($sort_options)) {
            $sort_id = $block_id . '-sort';
            
            $output .= '<div class="sort-dropdown">';
            $output .= sprintf(
                '<label for="%s" class="sort-label">%s</label>',
                esc_attr($sort_id),
                esc_html__('Sort by:', 'fau-elemental')
            );
            $output .= sprintf(
                '<select id="%s" class="sort-select">',
                esc_attr($sort_id)
            );
            
            foreach ($sort_options as $option) {
                $selected = $option['value'] === $default_sort ? ' selected' : '';
                $output .= sprintf(
                    '<option value="%s"%s>%s</option>',
                    esc_attr($option['value']),
                    $selected,
                    esc_html($option['label'])
                );
            }
            
            $output .= '</select>';
            $output .= '</div>';
        }
        
        $output .= '</div>'; // Close sort-controls
        $output .= '</div>'; // Close sort-section

        return $output;
    }
}

// AJAX handler for filtering teaser grid content
add_action('wp_ajax_fau_filter_teaser_grid', 'fau_filter_teaser_grid_ajax_handler');
add_action('wp_ajax_nopriv_fau_filter_teaser_grid', 'fau_filter_teaser_grid_ajax_handler');

if (!function_exists('fau_filter_teaser_grid_ajax_handler')) {
    function fau_filter_teaser_grid_ajax_handler() {
        // Verify nonce for security
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'fau_filter_nonce')) {
            wp_die('Security check failed');
        }

        // Get filter parameters
        $search = sanitize_text_field($_POST['search'] ?? '');
        $filters = $_POST['filters'] ?? [];
        $sort = sanitize_text_field($_POST['sort'] ?? 'date');
        $page = intval($_POST['page'] ?? 1);
        $per_page = intval($_POST['per_page'] ?? 15);
        $post_type = sanitize_text_field($_POST['post_type'] ?? 'post');
        $category = intval($_POST['category'] ?? 0);

        // Debug logging for received data
        error_log('FAU Filter Debug - Raw $_POST filters: ' . print_r($_POST['filters'] ?? [], true));

        // Build query arguments starting with teaser grid's base configuration
        $args = [
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'paged' => $page,
            'meta_query' => [],
            'tax_query' => [],
        ];

        // NOTE: We'll handle category filtering through tax_query for consistency
        // Do not use $args['cat'] to avoid conflicts

        // Add search on top of existing configuration
        if (!empty($search)) {
            $args['s'] = $search;
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

        // Process filters - build tax_query array
        $tax_queries = [];
        
        // Always add the base category from teaser grid if it exists
        if ($category > 0) {
            $tax_queries[] = [
                'taxonomy' => 'category',
                'field' => 'term_id',
                'terms' => $category,
            ];
        }

        foreach ($filters as $filter_name => $filter_data) {
            $filter_type = $filter_data['type'] ?? '';
            $filter_value = $filter_data['value'] ?? '';

            if (empty($filter_value)) {
                continue;
            }

            // Debug logging for filter processing
            error_log("FAU Filter Debug - Processing filter: $filter_name");
            error_log("FAU Filter Debug - Filter type: $filter_type");
            error_log("FAU Filter Debug - Filter value: $filter_value");

            // Handle different filter types
            // For configured filters, we need to determine the actual filter type from the name
            $actual_filter_type = $filter_type;
            if ($filter_type === 'configured') {
                // For configured filters, determine the type from the filter name
                $filter_name_lower = strtolower($filter_name);
                if (strpos($filter_name_lower, 'categor') !== false) {
                    $actual_filter_type = 'categories';
                } elseif (strpos($filter_name_lower, 'tag') !== false) {
                    $actual_filter_type = 'tags';
                } elseif (strpos($filter_name_lower, 'author') !== false) {
                    $actual_filter_type = 'authors';
                } elseif (strpos($filter_name_lower, 'year') !== false) {
                    $actual_filter_type = 'years';
                }
            }

            error_log("FAU Filter Debug - Actual filter type determined: $actual_filter_type");

            switch ($actual_filter_type) {
                case 'categories':
                    // IMPORTANT: Only allow category filters if no base category is set
                    // If a base category is set, category filters are not allowed to override it
                    if ($category == 0) {
                        $tax_queries[] = [
                            'taxonomy' => 'category',
                            'field' => 'slug',
                            'terms' => $filter_value,
                        ];
                        error_log("FAU Filter Debug - Added category tax_query for: $filter_value");
                    } else {
                        error_log("FAU Filter Debug - Skipped category filter because base category is set: $category");
                    }
                    // If base category is set, ignore additional category filters
                    // This ensures we stay within the editor's chosen category
                    break;
                case 'tags':
                    $tax_queries[] = [
                        'taxonomy' => 'post_tag',
                        'field' => 'slug',
                        'terms' => $filter_value,
                    ];
                    error_log("FAU Filter Debug - Added tag tax_query for: $filter_value");
                    break;
                case 'authors':
                    $author = get_user_by('slug', $filter_value);
                    if ($author) {
                        $args['author'] = $author->ID;
                        error_log("FAU Filter Debug - Added author filter for: $filter_value (ID: {$author->ID})");
                    }
                    break;
                case 'years':
                    $args['date_query'] = [
                        [
                            'year' => intval($filter_value),
                        ],
                    ];
                    error_log("FAU Filter Debug - Added year filter for: $filter_value");
                    break;
                default:
                    error_log("FAU Filter Debug - Unknown filter type: $actual_filter_type");
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

        // Debug logging
        error_log('FAU Filter Debug - Query Args: ' . print_r($args, true));
        error_log('FAU Filter Debug - Found Posts: ' . $query->found_posts);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                
                // Get featured image
                $featured_image = '';
                if (has_post_thumbnail($post_id)) {
                    $featured_image = get_the_post_thumbnail_url($post_id, 'medium');
                }

                // Get categories
                $categories = get_the_category($post_id);
                $category_names = array_map(function($cat) { return $cat->name; }, $categories);

                // Get excerpt
                $excerpt = get_the_excerpt($post_id);
                if (empty($excerpt)) {
                    $excerpt = wp_trim_words(get_the_content($post_id), 20);
                }

                $posts[] = [
                    'id' => $post_id,
                    'title' => get_the_title($post_id),
                    'excerpt' => $excerpt,
                    'permalink' => get_permalink($post_id),
                    'featured_image' => $featured_image,
                    'categories' => $category_names,
                    'date' => get_the_date('', $post_id),
                    'author' => get_the_author_meta('display_name', get_post_field('post_author', $post_id)),
                ];
            }
            wp_reset_postdata();
        }

        // Prepare response
        $response = [
            'success' => true,
            'posts' => $posts,
            'total' => $query->found_posts,
            'pages' => $query->max_num_pages,
            'current_page' => $page,
            'debug_args' => $args, // Add debug info
        ];

        error_log('FAU Filter Debug - Response: ' . print_r($response, true));

        wp_send_json($response);
    }
}

// Function to get scoped filter options based on specific query
if (!function_exists('fau_get_scoped_filter_options')) {
    function fau_get_scoped_filter_options($post_type = 'post', $category = 0) {
        $filter_options = [
            'tags' => [
                'label' => __('Tags', 'fau-elemental'),
                'options' => []
            ],
            'authors' => [
                'label' => __('Authors', 'fau-elemental'),
                'options' => []
            ],
            'years' => [
                'label' => __('Year', 'fau-elemental'),
                'options' => []
            ]
        ];

        // Only include category filters if no base category is set
        // This prevents users from changing the category that the editor configured
        if ($category == 0) {
            $filter_options['categories'] = [
                'label' => __('Categories', 'fau-elemental'),
                'options' => []
            ];
        }

        // Build base query for scoping
        $base_args = [
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
        ];

        if ($category > 0) {
            $base_args['tax_query'] = [
                [
                    'taxonomy' => 'category',
                    'field' => 'term_id',
                    'terms' => $category,
                ]
            ];
        }

        $scoped_query = new WP_Query($base_args);
        $post_ids = $scoped_query->posts;

        if (empty($post_ids)) {
            return $filter_options;
        }

        // Get categories used by these posts (only if no base category is set)
        if ($category == 0 && isset($filter_options['categories'])) {
            $categories = wp_get_object_terms($post_ids, 'category');
            foreach ($categories as $cat) {
                if (!is_wp_error($cat)) {
                    $filter_options['categories']['options'][] = [
                        'value' => $cat->slug,
                        'label' => $cat->name,
                        'count' => $cat->count
                    ];
                }
            }
        }

        // Get tags used by these posts
        $tags = wp_get_object_terms($post_ids, 'post_tag');
        foreach ($tags as $tag) {
            if (!is_wp_error($tag)) {
                $filter_options['tags']['options'][] = [
                    'value' => $tag->slug,
                    'label' => $tag->name,
                    'count' => $tag->count
                ];
            }
        }

        // Get authors of these posts
        global $wpdb;
        $post_ids_placeholders = implode(',', array_fill(0, count($post_ids), '%d'));
        $author_ids = $wpdb->get_col($wpdb->prepare("
            SELECT DISTINCT post_author 
            FROM {$wpdb->posts} 
            WHERE ID IN ($post_ids_placeholders)
            AND post_author > 0
        ", ...$post_ids));

        foreach ($author_ids as $author_id) {
            $author = get_userdata($author_id);
            if ($author) {
                $post_count = $wpdb->get_var($wpdb->prepare("
                    SELECT COUNT(*) 
                    FROM {$wpdb->posts} 
                    WHERE post_author = %d 
                    AND ID IN ($post_ids_placeholders)",
                    $author_id, ...$post_ids
                ));

                $filter_options['authors']['options'][] = [
                    'value' => $author->user_nicename,
                    'label' => $author->display_name,
                    'count' => $post_count
                ];
            }
        }

        // Get years from these posts
        $years = $wpdb->get_results($wpdb->prepare("
            SELECT DISTINCT YEAR(post_date) as year, COUNT(*) as count 
            FROM {$wpdb->posts} 
            WHERE ID IN ($post_ids_placeholders)
            GROUP BY YEAR(post_date) 
            ORDER BY year DESC
        ", ...$post_ids));
        
        foreach ($years as $year_data) {
            $filter_options['years']['options'][] = [
                'value' => $year_data->year,
                'label' => $year_data->year,
                'count' => $year_data->count
            ];
        }

        return apply_filters('fau_list_filters_scoped_options', $filter_options, $post_type, $category);
    }
} 