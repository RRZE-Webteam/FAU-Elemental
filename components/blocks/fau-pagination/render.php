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



/**
 * Renders the `fau-elemental/fau-pagination` block on the server.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block default content.
 * @param WP_Block $block      Block instance.
 * @return string Returns the pagination HTML.
 */
function render_block_fau_pagination( $attributes, $content, $block ) {

        
        $variant = $attributes['variant'] ?? 'basic';
        $current_page = $attributes['currentPage'] ?? 1;
        $total_pages = $attributes['totalPages'] ?? 1;
        $base_url = $attributes['baseUrl'] ?? '';
        $page_param = $attributes['pageParam'] ?? 'paged';
        $grid_block_id = $attributes['gridBlockId'] ?? '';
        $filter_block_id = $attributes['filterBlockId'] ?? '';
        $custom_block_id = $attributes['customBlockId'] ?? '';

        // Use custom block ID if provided, otherwise generate unique ID
        $pagination_id = !empty($custom_block_id) ? $custom_block_id : 'fau-pagination-' . uniqid();



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
            
            // Always use server-side pagination
            // Client-side JavaScript pagination has been disabled for performance and reliability
            $is_js_pagination = false;
            
            if ($is_js_pagination) {
                // For JS pagination, show a placeholder that will be replaced by JavaScript
                $output .= '<div class="pagination-placeholder" aria-live="polite">';
                // Only show loading pagination if we have more than 1 page (meaning more than ~6 posts)
                if ($total_pages > 1) {
                    $output .= '<span class="loading-pagination">' . esc_html__('Loading pagination...', 'fau-elemental') . '</span>';
                }
                $output .= '</div>';
            } elseif ($total_pages > 1) {
                // For server-side pagination (fallback for template-based pagination)
                $output .= fau_elemental_generate_advanced_pagination($current_page, $total_pages, $base_url, $page_param);
            } else {
                $output .= '<div class="no-pagination">' . esc_html__('All results shown', 'fau-elemental') . '</div>';
            }
            
            $output .= '</div>';
        }

        $output .= '</div>';
        return $output;
    }

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


    /**
     * Generates a pretty permalink pagination URL.
     *
     * @param int    $page_number The page number.
     * @param string $base_url    Base URL (optional).
     * @return string The pagination URL.
     */
    function fau_elemental_generate_pagination_url($page_number, $base_url = '') {
        if (empty($base_url)) {
            $base_url = get_permalink();
        }
        
        // Remove trailing slash
        $base_url = rtrim($base_url, '/');
        
        if ($page_number <= 1) {
            return $base_url . '/';
        }
        
        // Generate pretty permalink: /page/2/
        return $base_url . '/page/' . $page_number . '/';
    }

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
            $prev_url = fau_elemental_generate_pagination_url($current_page - 1, $base_url);
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
            $next_url = fau_elemental_generate_pagination_url($current_page + 1, $base_url);
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
            $page_url = fau_elemental_generate_pagination_url($page_number, $base_url);
            return sprintf(
                '<a href="%s" class="page-number" aria-label="%s">%d</a>',
                esc_url($page_url),
                esc_attr(sprintf(__('Go to page %d', 'fau-elemental'), $page_number)),
                $page_number
            );
        }
    }

// Enqueue the view script
wp_enqueue_script('fau-pagination-view', get_template_directory_uri() . '/build/blocks/fau-pagination/view.js', [], '1.0.0-' . time(), true);

// Actually render the block
echo render_block_fau_pagination($attributes, $content, $block); 