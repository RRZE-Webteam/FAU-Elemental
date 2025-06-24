<?php
/**
 * Server-side rendering of the `fau-elemental/fau-pagination` block.
 *
 * @package FAU_Elemental
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Debug: Log that this file is being loaded
error_log('FAU Pagination render.php loaded');

// Add AJAX handler for load more functionality
add_action('wp_ajax_fau_load_more_content', 'fau_elemental_ajax_load_more_content');
add_action('wp_ajax_nopriv_fau_load_more_content', 'fau_elemental_ajax_load_more_content');

if ( ! function_exists( 'fau_elemental_ajax_load_more_content' ) ) {
    /**
     * Generic AJAX handler for load more functionality.
     * Works with different block types by calling specific render functions.
     */
    function fau_elemental_ajax_load_more_content() {
        // Verify nonce for security
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'fau_load_more_nonce')) {
            wp_die('Security check failed');
        }

        // Get the request data
        $block_type = sanitize_text_field($_POST['blockType'] ?? '');
        $page = intval($_POST['page'] ?? 1);
        $attributes = isset($_POST['attributes']) ? json_decode(stripslashes($_POST['attributes']), true) : [];
        
        // Check if attributes were decoded properly
        if (empty($attributes)) {
            wp_die('No attributes available');
        }
        
        $output = '';
        
        // Handle different block types
        switch ($block_type) {
            case 'fau-teaser-grid':
                $output = fau_elemental_render_teaser_grid_content($attributes, $page);
                break;
            case 'fau-facts-grid':
                // Add facts grid handler when needed
                $output = fau_elemental_render_facts_grid_content($attributes, $page);
                break;
            default:
                wp_die('Unknown block type');
        }
        
        // Return the content
        echo $output;
        wp_die();
    }
}

if ( ! function_exists( 'fau_elemental_render_teaser_grid_content' ) ) {
    /**
     * Renders teaser grid content for AJAX load more.
     *
     * @param array $attributes Block attributes.
     * @param int   $page       Page number to load.
     * @return string           The rendered teaser items HTML.
     */
    function fau_elemental_render_teaser_grid_content($attributes, $page) {
        // Extract attributes
        $variant = $attributes['variant'] ?? 'post';
        $posts_per_page = $attributes['postsPerPage'] ?? 15;
        $selected_category = $attributes['category'] ?? 0;
        $order_by = $attributes['orderBy'] ?? 'date';
        $order = $attributes['order'] ?? 'DESC';
        $heading_level = $attributes['headingLevel'] ?? 'h4';
        $teaser_layout = $attributes['teaserLayout'] ?? '3m';
        $display_style = $attributes['displayStyle'] ?? 'teaser-grid';
        
        // Build grid classes
        $grid_classes = ['fau-teaser-grid', $display_style];
        if ($display_style === 'teaser-grid') {
            if ($teaser_layout === '2s-left' || $teaser_layout === '2s-right') {
                $grid_classes[] = 'layout-2s';
                $grid_classes[] = "layout-{$teaser_layout}";
            } else {
                $grid_classes[] = "layout-{$teaser_layout}";
            }
        } elseif ($display_style === 'mini-list') {
            $grid_classes[] = 'style-mini-list';
        }
        
        // Query for posts
        $args = [
            'post_type' => $variant,
            'posts_per_page' => $posts_per_page,
            'paged' => $page,
            'orderby' => $order_by,
            'order' => $order,
        ];

        if ($selected_category) {
            $args['cat'] = $selected_category;
        }

        $query = new WP_Query($args);
        
        $teaser_items = [];
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $teaser_items[] = fau_elemental_render_teaser_item(get_post(), $variant, $grid_classes, $heading_level);
            }
            wp_reset_postdata();
        }
        
        $result = fau_elemental_wrap_teaser_items($teaser_items, $teaser_layout);
        
        return $result;
    }
}

if ( ! function_exists( 'fau_elemental_render_facts_grid_content' ) ) {
    /**
     * Renders facts grid content for AJAX load more.
     * Placeholder for future facts grid implementation.
     *
     * @param array $attributes Block attributes.
     * @param int   $page       Page number to load.
     * @return string           The rendered facts items HTML.
     */
    function fau_elemental_render_facts_grid_content($attributes, $page) {
        // Placeholder for facts grid implementation
        return '';
    }
}

if ( ! function_exists( 'render_block_fau_pagination' ) ) {
    /**
     * Renders the `fau-elemental/fau-pagination` block on the server.
     *
     * @param array    $attributes Block attributes.
     * @param string   $content    Block default content.
     * @param WP_Block $block      Block instance.
     * @return string Returns the pagination HTML.
     */
    function render_block_fau_pagination( $attributes, $content, $block ) {
        // Debug: Log that render function is called
        error_log('FAU Pagination render function called');
        error_log('FAU Pagination attributes: ' . var_export($attributes, true));
        
        $variant = $attributes['variant'] ?? 'basic';
        $current_page = $attributes['currentPage'] ?? 1;
        $total_pages = $attributes['totalPages'] ?? 1;
        $base_url = $attributes['baseUrl'] ?? '';
        $page_param = $attributes['pageParam'] ?? 'paged';
        $grid_block_id = $attributes['gridBlockId'] ?? '';
        $filter_block_id = $attributes['filterBlockId'] ?? '';

        // Generate unique ID for this pagination instance
        $pagination_id = 'fau-pagination-' . uniqid();

        // Debug: Log what we received
        error_log('Pagination Block Debug - gridBlockId received: ' . var_export($grid_block_id, true));
        error_log('Pagination Block Debug - filterBlockId received: ' . var_export($filter_block_id, true));

        // Explicitly set wrapper attributes to ensure correct CSS classes
        $wrapper_classes = ['wp-block-fau-elemental-fau-pagination', 'pagination', $variant];
        $wrapper_attributes = sprintf(
            'class="%s" role="navigation" aria-label="%s" id="%s" data-block-id="%s" data-current-page="%d" data-total-pages="%d" data-grid-block-id="%s" data-filter-block-id="%s"',
            esc_attr(implode(' ', $wrapper_classes)),
            esc_attr__('Pagination', 'fau-elemental'),
            esc_attr($pagination_id),
            esc_attr($pagination_id),
            esc_attr($current_page),
            esc_attr($total_pages),
            esc_attr($grid_block_id),
            esc_attr($filter_block_id)
        );

        $output = sprintf('<div %s>', $wrapper_attributes);

        if ($variant === 'load-more') {
            $output .= sprintf(
                '<button class="load-more-button" data-current-page="%d" data-total-pages="%d">%s</button>',
                esc_attr($current_page),
                esc_attr($total_pages),
                esc_html__('Load More', 'fau-elemental')
            );
            
            // Add inline JavaScript for load more functionality
            // Pass the teaser grid attributes if available (for when called from teaser grid)
            $teaser_attributes = $attributes['teaserAttributes'] ?? [];
            $output .= fau_elemental_get_load_more_script($pagination_id, $teaser_attributes);
        } else {
            // Basic pagination - create container that will be controlled by filter JavaScript
            $output .= '<div class="pagination-controls">';
            
            // Check if this is connected to a grid with JS pagination
            $is_js_pagination = !empty($grid_block_id);
            
            if ($is_js_pagination) {
                // For JS pagination, show a placeholder that will be replaced by JavaScript
                $output .= '<div class="pagination-placeholder" aria-live="polite">';
                $output .= '<span class="loading-pagination">' . esc_html__('Loading pagination...', 'fau-elemental') . '</span>';
                $output .= '</div>';
            } elseif ($total_pages > 1) {
                // For server-side pagination, generate the pagination HTML
                $output .= fau_elemental_generate_advanced_pagination($current_page, $total_pages, $base_url, $page_param);
            } else {
                $output .= '<div class="no-pagination">All results shown</div>';
            }
            
            $output .= '</div>';
        }

        $output .= '</div>';
        return $output;
    }
}

if ( ! function_exists( 'fau_elemental_get_load_more_script' ) ) {
    /**
     * Generates inline JavaScript for load more functionality.
     *
     * @param string $pagination_id The unique ID of the pagination container.
     * @param array  $teaser_attributes The teaser grid attributes for AJAX.
     * @return string The inline script HTML.
     */
    function fau_elemental_get_load_more_script($pagination_id, $teaser_attributes = []) {
        $ajax_url = admin_url('admin-ajax.php');
        $nonce = wp_create_nonce('fau_load_more_nonce');
        
        // Escape values for JavaScript
        $escaped_pagination_id = esc_js($pagination_id);
        $escaped_ajax_url = esc_js($ajax_url);
        $escaped_nonce = esc_js($nonce);
        
        // Properly encode attributes for JavaScript
        $attributes_json = json_encode($teaser_attributes);
        $escaped_teaser_attributes = addslashes($attributes_json);
        
        // Get the script content with proper escaping
        $script_content = "
(function() {
    const paginationContainer = document.getElementById('{$escaped_pagination_id}');
    if (!paginationContainer) {
        return;
    }

    const loadMoreButton = paginationContainer.querySelector('.load-more-button');
    if (!loadMoreButton) {
        return;
    }

    loadMoreButton.addEventListener('click', async function(e) {
        e.preventDefault();
        
        const currentPage = parseInt(this.dataset.currentPage);
        const totalPages = parseInt(this.dataset.totalPages);
        
        if (currentPage >= totalPages) {
            this.disabled = true;
            return;
        }

        // Find the teaser grid container
        const parentSection = paginationContainer.parentElement;
        
        if (!parentSection) {
            return;
        }
        
        // Find the teaser grid container within the section
        let itemsContainer = parentSection.querySelector('.fau-teaser-grid');
        
        if (!itemsContainer) {
            // Alternative: look for any container with teaser items
            itemsContainer = parentSection.querySelector('[class*=\"teaser-grid\"]');
        }
        
        if (!itemsContainer) {
            // Last resort: look for any container with teaser-item children
            const allDivs = parentSection.querySelectorAll('div');
            for (const div of allDivs) {
                if (div.querySelector('.teaser-item')) {
                    itemsContainer = div;
                    break;
                }
            }
        }
        
        if (!itemsContainer) {
            return;
        }
        
        try {
            const formData = new FormData();
            formData.append('action', 'fau_load_more_content');
            formData.append('nonce', '{$escaped_nonce}');
            formData.append('blockType', 'fau-teaser-grid');
            formData.append('page', currentPage + 1);
            formData.append('attributes', '{$escaped_teaser_attributes}');
            
            const response = await fetch('{$escaped_ajax_url}', {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) {
                return;
            }
            
            const newItems = await response.text();
            
            if (!newItems || newItems.trim() === '') {
                return;
            }
            
            // Append the new items
            itemsContainer.insertAdjacentHTML('beforeend', newItems);
            
            // Update the current page
            this.dataset.currentPage = currentPage + 1;
            
            // Disable the button if we've reached the last page
            if (currentPage + 1 >= totalPages) {
                this.disabled = true;
            }
        } catch (error) {
            // Silently handle errors
        }
    });
})();
        ";
        
        return sprintf('<script type="text/javascript">%s</script>', $script_content);
    }
}

if ( ! function_exists( 'fau_elemental_generate_advanced_pagination' ) ) {
    /**
     * Generates advanced pagination HTML with previous/next buttons and ellipsis.
     *
     * @param int    $current_page Current page number.
     * @param int    $total_pages  Total number of pages.
     * @param string $base_url     Base URL for pagination links.
     * @param string $page_param   URL parameter name for page number.
     * @return string The pagination HTML.
     */
    function fau_elemental_generate_advanced_pagination($current_page, $total_pages, $base_url, $page_param) {
        if ($total_pages <= 1) {
            return '';
        }

        // Ensure we're working with integers
        $current_page = intval($current_page);
        $total_pages = intval($total_pages);

        $output = '<div class="pagination-controls">';

        // Previous button
        if ($current_page > 1) {
            $prev_url = add_query_arg($page_param, $current_page - 1, $base_url);
            $output .= sprintf(
                '<a href="%s" class="page-nav prev" aria-label="%s"><span aria-hidden="true">‹</span></a>',
                esc_url($prev_url),
                esc_attr__('Previous page', 'fau-elemental')
            );
        } else {
            $output .= '<span class="page-nav prev disabled" aria-hidden="true">‹</span>';
        }

        // Page numbers
        $output .= '<div class="page-numbers">';

        if ($total_pages <= 6) {
            // Show all pages if 6 or fewer
            for ($i = 1; $i <= $total_pages; $i++) {
                $output .= fau_elemental_generate_page_link($i, $current_page, $base_url, $page_param);
            }
        } else {
            // Sliding window pagination logic
            $pages_to_show = [];
            
            if ($current_page <= 2) {
                // Pages 1-2: Show 1,2,3 ... 8,9,10 (first 3, last 3)
                $pages_to_show = [1, 2, 3, '...', $total_pages - 2, $total_pages - 1, $total_pages];
            } elseif ($current_page == 3) {
                // Page 3: Show ..., 2,3,4, ..., 8,9,10
                $pages_to_show = ['...', 2, 3, 4, '...', $total_pages - 2, $total_pages - 1, $total_pages];
            } elseif ($current_page >= $total_pages - 2) {
                // Last 3 pages: Show 1,2,3, ..., 7,8,9 (first 3, last 3)
                $pages_to_show = [1, 2, 3, '...', $total_pages - 2, $total_pages - 1, $total_pages];
            } else {
                // Middle pages (4,5,6): Pure sliding window
                // Show 1, ..., current-1, current, current+1, ..., last
                $pages_to_show = [
                    1, 
                    '...', 
                    $current_page - 1, 
                    $current_page, 
                    $current_page + 1, 
                    '...', 
                    $total_pages
                ];
            }
            
            // Output the pages
            foreach ($pages_to_show as $page) {
                if ($page === '...') {
                    $output .= '<span class="page-ellipsis" aria-hidden="true">…</span>';
                } else {
                    $output .= fau_elemental_generate_page_link($page, $current_page, $base_url, $page_param);
                }
            }
        }

        $output .= '</div>'; // Close page-numbers

        // Next button
        if ($current_page < $total_pages) {
            $next_url = add_query_arg($page_param, $current_page + 1, $base_url);
            $output .= sprintf(
                '<a href="%s" class="page-nav next" aria-label="%s"><span aria-hidden="true">›</span></a>',
                esc_url($next_url),
                esc_attr__('Next page', 'fau-elemental')
            );
        } else {
            $output .= '<span class="page-nav next disabled" aria-hidden="true">›</span>';
        }

        $output .= '</div>'; // Close pagination-controls

        return $output;
    }
}

if ( ! function_exists( 'fau_elemental_generate_page_link' ) ) {
    /**
     * Generates a single page link.
     *
     * @param int    $page_number  The page number to generate.
     * @param int    $current_page The current active page.
     * @param string $base_url     Base URL for the link.
     * @param string $page_param   URL parameter name for page number.
     * @return string The page link HTML.
     */
    function fau_elemental_generate_page_link($page_number, $current_page, $base_url, $page_param) {
        // Ensure we're comparing integers
        $page_number = intval($page_number);
        $current_page = intval($current_page);
        
        $is_current = $page_number === $current_page;
        
        if ($is_current) {
            return sprintf(
                '<span class="page-number current" aria-current="page">%d</span>',
                $page_number
            );
        } else {
            $page_url = add_query_arg($page_param, $page_number, $base_url);
            return sprintf(
                '<a href="%s" class="page-number" aria-label="%s">%d</a>',
                esc_url($page_url),
                esc_attr(sprintf(__('Go to page %d', 'fau-elemental'), $page_number)),
                $page_number
            );
        }
    }
}

// Actually render the block
echo render_block_fau_pagination($attributes, $content, $block); 