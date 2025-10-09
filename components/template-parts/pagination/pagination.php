<?php
/**
 * Pagination utilities for FAU Elemental theme.
 */

if (!function_exists('fau_elemental_generate_pagination')) {
    /**
     * Generate pagination HTML for FAU Elemental theme.
     *
     * @param int $current_page
     * @param int $total_pages
     * @param string $pagination_type
     * @param bool|null $is_mobile If null, auto-detect using wp_is_mobile().
     * @return string
     */
    function fau_elemental_generate_pagination($current_page, $total_pages, $pagination_type = 'numbers', $is_mobile = null) {
        if ($is_mobile === null) {
            $is_mobile = function_exists('wp_is_mobile') ? wp_is_mobile() : false;
        }
        if ($total_pages <= 1) {
            return '';
        }

        $output = '<nav class="fau-pagination" role="navigation" aria-label="' . esc_attr__('Posts pagination', 'fau-elemental') . '">';
        $output .= '<div class="pagination-wrapper">';

        // Previous button
        if ($current_page > 1) {
            $prev_url = get_pagenum_link($current_page - 1);
            $output .= sprintf(
                '<a href="%s" class="page-number prev" aria-label="%s"><span class="pagination-icon pagination-icon-prev"></span></a>',
                esc_url($prev_url),
                esc_attr__('Previous page', 'fau-elemental')
            );
        } else {
            $output .= '<span class="page-number prev disabled" aria-disabled="true" aria-label="' . esc_attr__('Previous page', 'fau-elemental') . '"><span class="pagination-icon pagination-icon-prev"></span></span>';
        }

        if ($pagination_type === 'numbers') {
            if ($is_mobile) {
                // MOBILE LOGIC
                if ($total_pages <= 5) {
                    for ($i = 1; $i <= $total_pages; $i++) {
                        if ($i === $current_page) {
                            $output .= sprintf('<span class="page-number current" aria-current="page">%d</span>', $i);
                        } else {
                            $page_url = get_pagenum_link($i);
                            /* translators: %d: page number */
                            $output .= sprintf('<a href="%s" class="page-number" aria-label="%s">%d</a>', esc_url($page_url), esc_attr(sprintf(__('Page %d', 'fau-elemental'), $i)), $i);
                        }
                    }
                } elseif ($current_page == 1 || $current_page >= $total_pages) {
                    // 1,2,...,N-1,N
                    for ($i = 1; $i <= 2; $i++) {
                        if ($i === $current_page) {
                            $output .= sprintf('<span class="page-number current" aria-current="page">%d</span>', $i);
                        } else {
                            $page_url = get_pagenum_link($i);
                            $output .= sprintf('<a href="%s" class="page-number" aria-label="%s">%d</a>', esc_url($page_url), esc_attr(sprintf(__('Page %d', 'fau-elemental'), $i)), $i);
                        }
                    }
                    $output .= '<span class="page-ellipsis" aria-hidden="true">...</span>';
                    for ($i = $total_pages - 1; $i <= $total_pages; $i++) {
                        $page_url = get_pagenum_link($i);
                        if ($i === $current_page) {
                            $output .= sprintf('<span class="page-number current" aria-current="page">%d</span>', $i);
                        } else {
                            $output .= sprintf('<a href="%s" class="page-number" aria-label="%s">%d</a>', esc_url($page_url), esc_attr(sprintf(__('Page %d', 'fau-elemental'), $i)), $i);
                        }
                    }
                } elseif ($current_page == 2) {
                    // 1,2,3,...,N
                    for ($i = 1; $i <= 3; $i++) {
                        if ($i === $current_page) {
                            $output .= sprintf('<span class="page-number current" aria-current="page">%d</span>', $i);
                        } else {
                            $page_url = get_pagenum_link($i);
                            $output .= sprintf('<a href="%s" class="page-number" aria-label="%s">%d</a>', esc_url($page_url), esc_attr(sprintf(__('Page %d', 'fau-elemental'), $i)), $i);
                        }
                    }
                    $output .= '<span class="page-ellipsis" aria-hidden="true">...</span>';
                    $output .= '<a href="' . esc_url(get_pagenum_link($total_pages)) . '" class="page-number" aria-label="' . esc_attr(sprintf(__('Page %d', 'fau-elemental'), $total_pages)) . '">' . $total_pages . '</a>';
                } elseif ($current_page >= $total_pages - 1) {
                    // 1,...,N-2,N-1,N
                    $output .= '<a href="' . esc_url(get_pagenum_link(1)) . '" class="page-number" aria-label="' . esc_attr(sprintf(__('Page %d', 'fau-elemental'), 1)) . '">1</a>';
                    $output .= '<span class="page-ellipsis" aria-hidden="true">...</span>';
                    for ($i = $total_pages - 2; $i <= $total_pages; $i++) {
                        if ($i === $current_page) {
                            $output .= sprintf('<span class="page-number current" aria-current="page">%d</span>', $i);
                        } else {
                            $page_url = get_pagenum_link($i);
                            $output .= sprintf('<a href="%s" class="page-number" aria-label="%s">%d</a>', esc_url($page_url), esc_attr(sprintf(__('Page %d', 'fau-elemental'), $i)), $i);
                        }
                    }
                } else {
                    // 1,...,X,...,N
                    $output .= '<a href="' . esc_url(get_pagenum_link(1)) . '" class="page-number" aria-label="' . esc_attr(sprintf(__('Page %d', 'fau-elemental'), 1)) . '">1</a>';
                    $output .= '<span class="page-ellipsis" aria-hidden="true">...</span>';
                    $output .= sprintf('<span class="page-number current" aria-current="page">%d</span>', $current_page);
                    $output .= '<span class="page-ellipsis" aria-hidden="true">...</span>';
                    $output .= '<a href="' . esc_url(get_pagenum_link($total_pages)) . '" class="page-number" aria-label="' . esc_attr(sprintf(__('Page %d', 'fau-elemental'), $total_pages)) . '">' . $total_pages . '</a>';
                }
            } else {
                // DESKTOP LOGIC
                if ($total_pages <= 7) {
                    for ($i = 1; $i <= $total_pages; $i++) {
                        if ($i === $current_page) {
                            $output .= sprintf('<span class="page-number current" aria-current="page">%d</span>', $i);
                        } else {
                            $page_url = get_pagenum_link($i);
                            $output .= sprintf('<a href="%s" class="page-number" aria-label="%s">%d</a>', esc_url($page_url), esc_attr(sprintf(__('Page %d', 'fau-elemental'), $i)), $i);
                        }
                    }
                } elseif ($current_page < 3 || $current_page > $total_pages - 2) {
                    // 1,2,3,...,N-2,N-1,N
                    for ($i = 1; $i <= 3; $i++) {
                        if ($i === $current_page) {
                            $output .= sprintf('<span class="page-number current" aria-current="page">%d</span>', $i);
                        } else {
                            $page_url = get_pagenum_link($i);
                            $output .= sprintf('<a href="%s" class="page-number" aria-label="%s">%d</a>', esc_url($page_url), esc_attr(sprintf(__('Page %d', 'fau-elemental'), $i)), $i);
                        }
                    }
                    $output .= '<span class="page-ellipsis" aria-hidden="true">...</span>';
                    for ($i = $total_pages - 2; $i <= $total_pages; $i++) {
                        $page_url = get_pagenum_link($i);
                        if ($i === $current_page) {
                            $output .= sprintf('<span class="page-number current" aria-current="page">%d</span>', $i);
                        } else {
                            $output .= sprintf('<a href="%s" class="page-number" aria-label="%s">%d</a>', esc_url($page_url), esc_attr(sprintf(__('Page %d', 'fau-elemental'), $i)), $i);
                        }
                    }
                } elseif ($current_page == 3) {
                    // 1,2,3,4,...,N
                    for ($i = 1; $i <= 4; $i++) {
                        if ($i === $current_page) {
                            $output .= sprintf('<span class="page-number current" aria-current="page">%d</span>', $i);
                        } else {
                            $page_url = get_pagenum_link($i);
                            $output .= sprintf('<a href="%s" class="page-number" aria-label="%s">%d</a>', esc_url($page_url), esc_attr(sprintf(__('Page %d', 'fau-elemental'), $i)), $i);
                        }
                    }
                    $output .= '<span class="page-ellipsis" aria-hidden="true">...</span>';
                    $output .= '<a href="' . esc_url(get_pagenum_link($total_pages)) . '" class="page-number" aria-label="' . esc_attr(sprintf(__('Page %d', 'fau-elemental'), $total_pages)) . '">' . $total_pages . '</a>';
                } elseif ($current_page == $total_pages - 2) {
                    // 1,...,N-3,N-2,N-1,N
                    $output .= '<a href="' . esc_url(get_pagenum_link(1)) . '" class="page-number" aria-label="' . esc_attr(sprintf(__('Page %d', 'fau-elemental'), 1)) . '">1</a>';
                    $output .= '<span class="page-ellipsis" aria-hidden="true">...</span>';
                    for ($i = $total_pages - 3; $i <= $total_pages; $i++) {
                        if ($i === $current_page) {
                            $output .= sprintf('<span class="page-number current" aria-current="page">%d</span>', $i);
                        } else {
                            $page_url = get_pagenum_link($i);
                            $output .= sprintf('<a href="%s" class="page-number" aria-label="%s">%d</a>', esc_url($page_url), esc_attr(sprintf(__('Page %d', 'fau-elemental'), $i)), $i);
                        }
                    }
                } elseif ($current_page > 3 && $current_page < $total_pages - 2) {
                    // 1,...,X-1,X,X+1,...,N
                    $output .= '<a href="' . esc_url(get_pagenum_link(1)) . '" class="page-number" aria-label="' . esc_attr(sprintf(__('Page %d', 'fau-elemental'), 1)) . '">1</a>';
                    $output .= '<span class="page-ellipsis" aria-hidden="true">...</span>';
                    for ($i = $current_page - 1; $i <= $current_page + 1; $i++) {
                        if ($i === $current_page) {
                            $output .= sprintf('<span class="page-number current" aria-current="page">%d</span>', $i);
                        } else {
                            $page_url = get_pagenum_link($i);
                            $output .= sprintf('<a href="%s" class="page-number" aria-label="%s">%d</a>', esc_url($page_url), esc_attr(sprintf(__('Page %d', 'fau-elemental'), $i)), $i);
                        }
                    }
                    $output .= '<span class="page-ellipsis" aria-hidden="true">...</span>';
                    $output .= '<a href="' . esc_url(get_pagenum_link($total_pages)) . '" class="page-number" aria-label="' . esc_attr(sprintf(__('Page %d', 'fau-elemental'), $total_pages)) . '">' . $total_pages . '</a>';
                }
            }
        }

        // Next button
        if ($current_page < $total_pages) {
            $next_url = get_pagenum_link($current_page + 1);
            $output .= sprintf(
                '<a href="%s" class="page-number next" aria-label="%s"><span class="pagination-icon pagination-icon-next"></span></a>',
                esc_url($next_url),
                esc_attr__('Next page', 'fau-elemental')
            );
        } else {
            $output .= '<span class="page-number next disabled" aria-disabled="true" aria-label="' . esc_attr__('Next page', 'fau-elemental') . '"><span class="pagination-icon pagination-icon-next"></span></span>';
        }

        $output .= '</div>';
        $output .= '</nav>';
        return $output;
    }
}
