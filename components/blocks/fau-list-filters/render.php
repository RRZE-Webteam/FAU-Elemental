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
    // Change default for showMoreFiltersButton to true for better UX
    $show_more_filters = $attributes['showMoreFiltersButton'] ?? true;
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
    $available_filter_options = fau_get_available_filter_options($filter_fields);

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
        'nonce' => wp_create_nonce('fau_teaser_grid_filter'),
    ]);

    return $output;
}

/**
 * Get available filter options from WordPress content.
 *
 * @param array $filter_fields The configured filter fields.
 * @return array Available filter options.
 */
function fau_get_available_filter_options($filter_fields = []) {
    // Determine the post type context from any filter field
    $context_post_type = 'post'; // Default to post
    foreach ($filter_fields as $field) {
        if (isset($field['postType'])) {
            $context_post_type = $field['postType'];
            break; // Use the first postType found
        }
    }
    
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

    // Get categories used by the context post type
    $categories = get_terms([
        'taxonomy' => 'category',
        'hide_empty' => true,
        'object_ids' => get_posts([
            'post_type' => $context_post_type,
            'post_status' => 'publish',
            'numberposts' => -1,
            'fields' => 'ids'
        ])
    ]);
    
    foreach ($categories as $category) {
        $filter_options['categories']['options'][] = [
            'value' => $category->slug,
            'label' => $category->name,
            'count' => $category->count
        ];
    }

    // Get tags used by the context post type
    $tags = get_terms([
        'taxonomy' => 'post_tag',
        'hide_empty' => true,
        'object_ids' => get_posts([
            'post_type' => $context_post_type,
            'post_status' => 'publish',
            'numberposts' => -1,
            'fields' => 'ids'
        ])
    ]);
    
    foreach ($tags as $tag) {
        $filter_options['tags']['options'][] = [
            'value' => $tag->slug,
            'label' => $tag->name,
            'count' => $tag->count
        ];
    }

    // Get authors for the context post type
    $authors = get_users( [ 'capability' => [ 'edit_posts' ] ] );
    foreach ($authors as $author) {
        $post_count = count_user_posts($author->ID, $context_post_type);
        if ($post_count > 0) {
            $filter_options['authors']['options'][] = [
                'value' => $author->user_nicename,
                'label' => $author->display_name,
                'count' => $post_count
            ];
        }
    }

    // Get years for the context post type
    global $wpdb;
    $years = $wpdb->get_results($wpdb->prepare("
        SELECT DISTINCT YEAR(post_date) as year, COUNT(*) as count 
        FROM {$wpdb->posts} 
        WHERE post_status = 'publish' 
        AND post_type = %s 
        GROUP BY YEAR(post_date) 
        ORDER BY year DESC
    ", $context_post_type));
    
    foreach ($years as $year_data) {
        $filter_options['years']['options'][] = [
            'value' => $year_data->year,
            'label' => $year_data->year,
            'count' => $year_data->count
        ];
    }

    return apply_filters('fau_list_filters_available_options', $filter_options);
}

/**
 * Renders the search section of the list filters.
 *
 * @param string $placeholder The search placeholder text.
 * @param string $block_id The unique block ID.
 * @return string The search section HTML.
 */
function fau_list_filters_render_search_section($placeholder, $block_id) {
    $search_id = $block_id . '-search';
    
    $output = '<section class="fau-list-filters__search-section" aria-labelledby="' . esc_attr($search_id) . '-label">';
    $output .= '<h3 id="' . esc_attr($search_id) . '-label" class="screen-reader-text">' . esc_html__('Search content', 'fau-elemental') . '</h3>';
    $output .= '<div class="search-wrapper">';
    $output .= sprintf(
        '<input type="search" id="%s" class="search-input" placeholder="%s" aria-label="%s" />',
        esc_attr($search_id),
        esc_attr($placeholder),
        esc_attr__('Search content', 'fau-elemental')
    );
    $output .= '<button type="button" class="search-clear search-clear--hidden" aria-label="' . esc_attr__('Clear search', 'fau-elemental') . '"></button>';
    $output .= '</div>';
    $output .= '</section>';

    return $output;
}

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
    $output = '<section class="fau-list-filters__filter-section" aria-labelledby="' . esc_attr($block_id) . '-filters-label">';
    $output .= '<h3 id="' . esc_attr($block_id) . '-filters-label" class="screen-reader-text">' . esc_html__('Filter content', 'fau-elemental') . '</h3>';
    $output .= '<div class="filter-controls">';

    // Render configured/preset filters
    foreach ($filter_fields as $index => $field) {
        if (!empty($field['filterType'])) {
            $options_key = $field['filterType'];
            if ($options_key === 'taxonomy' && !empty($field['taxonomy'])) {
                $options_key = $field['taxonomy'];
            }
            
            $options = $available_options[$options_key]['options'] ?? [];
            $label = $field['label'] ?? sprintf(__('All %s', 'fau-elemental'), $available_options[$options_key]['label'] ?? 'Items');
            
            $output .= sprintf(
                '<div class="filter-field filter-field--configured"><label for="%1$s-filter-%2$s" class="filter-label">%3$s</label><select id="%1$s-filter-%2$s" class="filter-select" data-filter-name="%4$s" data-filter-type="%5$s" data-taxonomy="%6$s">',
                esc_attr($block_id),
                esc_attr($index),
                esc_html($label),
                esc_attr($field['filterType']),
                esc_attr($field['filterType']),
                esc_attr($field['taxonomy'] ?? '')
            );
            
            $output .= sprintf('<option value="">%s</option>', esc_html($label));
            foreach ($options as $option) {
                $output .= sprintf(
                    '<option value="%1$s">%2$s (%3$s)</option>',
                    esc_attr($option['value']),
                    esc_html($option['label']),
                    esc_html($option['count'])
                );
            }
            $output .= '</select></div>';
        }
    }

    // Dynamic Filters Section
    if ($show_more_filters && !empty($available_options)) {
        $output .= '<div class="dynamic-filters-container">';
        $output .= '<div class="available-filters" style="display: none;">';
        $output .= '<h4>' . esc_html__( 'Add filters:', 'fau-elemental' ) . '</h4>';
        $output .= '<div class="filter-buttons-container"></div>';
        $output .= '</div>';
        $output .= '<div class="added-filters"></div>';
        $output .= '</div>';

        $output .= sprintf(
            '<button type="button" class="show-more-filters" aria-expanded="false" data-available-filters="%s"><span class="show-more-text">%s</span><span class="show-less-text" style="display: none;">%s</span></button>',
            esc_attr( wp_json_encode( $available_options ) ),
            esc_html__( 'Weitere Filtermöglichkeiten +', 'fau-elemental' ),
            esc_html__( 'Weniger Filter –', 'fau-elemental' )
        );
    }

    $output .= '</div>'; // .filter-controls

    // Active filters section
    $output .= '<div class="active-filters active-filters--hidden">';
    $output .= '<div class="active-filters__header"><span class="active-filters__label">' . esc_html__( 'Active filters:', 'fau-elemental' ) . '</span></div>';
    $output .= '<div class="filter-chips"></div>';
    $output .= '<button type="button" class="clear-all-filters clear-all-filters--hidden"><span class="clear-all-text">' . esc_html__( 'Clear all', 'fau-elemental' ) . '</span></button>';
    $output .= '</div>';

    $output .= '</section>'; // .fau-list-filters__filter-section
    return $output;
}

/**
 * Renders an individual filter control.
 *
 * @param array  $field             The filter field configuration.
 * @param int    $index             The index of the filter.
 * @param string $block_id          The block's unique ID.
 * @param array  $available_options All available options for filters.
 * @return string The HTML for the filter control.
 */
function fau_list_filters_render_single_filter( $field, $index, $block_id, $available_options ) {
    $filter_type = $field['filterType'] ?? 'categories';
    $label       = $field['label'] ?? '';
    $options_key = $filter_type;

    if ($options_key === 'taxonomy' && !empty($field['taxonomy'])) {
        $options_key = $field['taxonomy'];
    }
    
    $options = $available_options[$options_key]['options'] ?? [];
    $label = $label ?: sprintf(__('All %s', 'fau-elemental'), $available_options[$options_key]['label'] ?? 'Items');
    
    $output = sprintf(
        '<div class="filter-field filter-field--configured"><label for="%1$s-filter-%2$s" class="filter-label">%3$s</label><select id="%1$s-filter-%2$s" class="filter-select" data-filter-name="%4$s" data-filter-type="%5$s" data-taxonomy="%6$s">',
        esc_attr($block_id),
        esc_attr($index),
        esc_html($label),
        esc_attr($field['filterType']),
        esc_attr($field['filterType']),
        esc_attr($field['taxonomy'] ?? '')
    );
    
    $output .= sprintf('<option value="">%s</option>', esc_html($label));
    foreach ($options as $option) {
        $output .= sprintf(
            '<option value="%1$s">%2$s (%3$s)</option>',
            esc_attr($option['value']),
            esc_html($option['label']),
            esc_html($option['count'])
        );
    }
    $output .= '</select></div>';

    return $output;
}

/**
 * Renders the sort section of the block.
 *
 * @param bool   $enable_view_switcher Whether to show the view switcher.
 * @param array  $available_views      Available view types.
 * @param string $default_view         The default view type.
 * @param bool   $enable_sorting       Whether to show the sorting dropdown.
 * @param array  $sort_options         Available sort options.
 * @param string $default_sort         The default sort option.
 * @param bool   $show_results_count   Whether to show the results count.
 * @param int    $results_per_page     Number of results per page.
 * @param string $block_id             The block's unique ID.
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

echo render_block_fau_list_filters($attributes, $content, $block);