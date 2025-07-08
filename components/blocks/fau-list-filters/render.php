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
        $custom_block_id = $attributes['customBlockId'] ?? '';

        // Generate unique ID for this block instance, or use custom ID if provided
        $block_id = !empty($custom_block_id) ? $custom_block_id : 'fau-list-filters-' . uniqid();

        // Get general filter options
        $available_filter_options = fau_get_available_filter_options();

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
            wp_enqueue_script('fau-list-filters-script', get_template_directory_uri() . '/build/blocks/fau-list-filters/view.js', [], '2.0.0-' . time(), true);
        }
        
        wp_localize_script('fau-list-filters-script', 'fauListFilters', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('fau_filter_nonce'),
            'i18n' => [
                'noContentToFilter' => __('No content to filter', 'fau-elemental'),
                'categories' => __('Categories', 'fau-elemental'),
                'year' => __('Year', 'fau-elemental'),
                'search' => __('Search', 'fau-elemental'),
                'addFilters' => __('Add filters:', 'fau-elemental'),
                'removeFilter' => __('Remove %s filter', 'fau-elemental'),
                'allLabel' => __('All %s', 'fau-elemental'),
                'loadingResults' => __('Loading results...', 'fau-elemental'),
                'resultsLoaded' => __('Results loaded', 'fau-elemental'),
                'errorOccurred' => __('An error occurred', 'fau-elemental'),
                'noResultsFound' => __('No results found', 'fau-elemental'),
                'totalResults' => __('Total results: %s', 'fau-elemental'),
            ],
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

        // Get authors
        $authors = get_users(['capability' => 'publish_posts']);
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
        $output .= '<button type="button" class="search-clear search-clear--hidden" aria-label="' . esc_attr__('Clear search', 'fau-elemental') . '"></button>';
        $output .= '</div>';
        $output .= '</div>';

        return $output;
    }
}

if ( ! function_exists( 'fau_list_filters_render_filter_section' ) ) {
	/**
	 * Renders the filter section of the block.
	 *
	 * @param array  $filter_fields      Configured filter fields.
	 * @param bool   $show_more_filters  Whether to show the "more filters" button.
	 * @param string $block_id           The block's unique ID.
	 * @param array  $available_options  Available filter options.
	 * @return string The filter section HTML.
	 */
	function fau_list_filters_render_filter_section($filter_fields, $show_more_filters, $block_id, $available_options) {
		$output = '<div class="fau-list-filters__filter-section">';
		$output .= '<div class="filter-controls">';

		// Dynamic Filters Section
		if ($show_more_filters) {
			$output .= '<div class="dynamic-filters-container">';
			$output .= '<div class="available-filters" style="display: none;">';
			$output .= '<h4>Add filters:</h4>';
			$output .= '<div class="filter-buttons-container"></div>';
			$output .= '</div>';
			$output .= '<div class="added-filters"></div>';
			$output .= '</div>';

			$output .= sprintf(
				'<button type="button" class="show-more-filters" aria-expanded="false" data-available-filters="%s"><span class="show-more-text">Weitere Filtermöglichkeiten +</span><span class="show-less-text" style="display: none;">Weniger Filter –</span></button>',
				esc_attr( wp_json_encode( $available_options ) )
			);
		}

		$output .= '</div>'; // .filter-controls

		// Active filters section
		$output .= '<div class="active-filters active-filters--hidden">';
		$output .= '<div class="active-filters__header"><span class="active-filters__label">Active filters:</span></div>';
		$output .= '<div class="filter-chips"></div>';
		$output .= '<button type="button" class="clear-all-filters clear-all-filters--hidden"><span class="clear-all-text">Clear all</span></button>';
		$output .= '</div>';

		$output .= '</div>'; // .fau-list-filters__filter-section
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

echo render_block_fau_list_filters($attributes, $content, $block);