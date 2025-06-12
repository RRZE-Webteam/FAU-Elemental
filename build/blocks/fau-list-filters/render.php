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
        $search_placeholder = $attributes['searchPlaceholder'] ?? 'Search...';
        $enable_filters = $attributes['enableFilters'] ?? true;
        $filter_fields = $attributes['filterFields'] ?? [];
        $show_more_filters = $attributes['showMoreFiltersButton'] ?? false;
        $enable_view_switcher = $attributes['enableViewSwitcher'] ?? true;
        $available_views = $attributes['availableViews'] ?? ['cards', 'table'];
        $default_view = $attributes['defaultView'] ?? 'cards';
        $enable_sorting = $attributes['enableSorting'] ?? true;
        $sort_options = $attributes['sortOptions'] ?? [
            ['value' => 'date', 'label' => 'Date'],
            ['value' => 'title', 'label' => 'Alphabetic'],
            ['value' => 'modified', 'label' => 'Last Modified']
        ];
        $default_sort = $attributes['defaultSort'] ?? 'date';
        $show_results_count = $attributes['showResultsCount'] ?? true;
        $results_per_page = $attributes['resultsPerPage'] ?? 15;
        $grid_width = $attributes['gridWidth'] ?? '12';

        // Generate unique ID for this block instance
        $block_id = 'fau-list-filters-' . uniqid();

        // Start building the output
        $wrapper_attributes = get_block_wrapper_attributes([
            'class' => "fau-list-filters grid-width-{$grid_width}",
            'id' => $block_id,
            'data-block-id' => $block_id
        ]);

        $output = sprintf('<div %s>', $wrapper_attributes);
        
        // Search Section
        if ($enable_search) {
            $output .= fau_list_filters_render_search_section($search_placeholder, $block_id);
        }

        // Filter Section
        if ($enable_filters && !empty($filter_fields)) {
            $output .= fau_list_filters_render_filter_section($filter_fields, $show_more_filters, $block_id);
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

        return $output;
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
     * @return string The filter section HTML.
     */
    function fau_list_filters_render_filter_section($filter_fields, $show_more_filters, $block_id) {
        $output = '<div class="fau-list-filters__filter-section">';
        
        // Filter controls
        $output .= '<div class="filter-controls">';
        
        foreach ($filter_fields as $index => $field) {
            $filter_id = $block_id . '-filter-' . $index;
            $field_name = $field['name'] ?? 'Filter ' . ($index + 1);
            $field_options = $field['options'] ?? [];
            $is_hidden = $show_more_filters && $index >= 3; // Hide filters after the 3rd one initially
            
            $filter_class = 'filter-field';
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
                '<select id="%s" class="filter-select" data-filter-name="%s">',
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
        
        // Show more filters button
        if ($show_more_filters && count($filter_fields) > 3) {
            $output .= '<button type="button" class="show-more-filters" aria-expanded="false">';
            $output .= esc_html__('Show more filters', 'fau-elemental');
            $output .= '</button>';
        }
        
        $output .= '</div>'; // Close filter-controls
        
        // Active filters (chips)
        $output .= '<div class="active-filters" style="display: none;">';
        $output .= '<div class="filter-chips"></div>';
        $output .= '<button type="button" class="clear-all-filters" style="display: none;">';
        $output .= esc_html__('Clear all', 'fau-elemental');
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
            $output .= '<span class="results-text">0 to 0 from 0 records</span>';
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
                
                $output .= sprintf(
                    '<button type="button" class="%s" id="%s" data-view="%s" aria-pressed="%s">',
                    esc_attr($button_class),
                    esc_attr($view_id),
                    esc_attr($view),
                    $is_active ? 'true' : 'false'
                );
                $output .= sprintf(
                    '<span class="view-icon view-icon-%s"></span>',
                    esc_attr($view)
                );
                $output .= sprintf(
                    '<span class="view-label">%s</span>',
                    esc_html(ucfirst($view))
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