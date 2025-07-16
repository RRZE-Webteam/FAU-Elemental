<?php
/**
 * The template for displaying archive pages with filtering and pagination
 * @package Fau-Elemental
 */

// Handle pagination validation before any output
add_action('template_redirect', function() {
    // Only run on archive pages
    if (!is_archive()) {
        return;
    }
    
    // Use WordPress standard pagination query var
    $current_page = max(1, get_query_var('paged', 1));
    
    // Calculate total pages for validation
    $posts_per_page = faue_get_items_per_page();
    $query_args = [
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids'
    ];
    
    // Add archive-specific query parameters
    if (is_category()) {
        $query_args['cat'] = get_queried_object_id();
    } elseif (is_tag()) {
        $query_args['tag_id'] = get_queried_object_id();
    } elseif (is_author()) {
        $query_args['author'] = get_queried_object_id();
    } elseif (is_date()) {
        if (is_year()) {
            $query_args['year'] = get_query_var('year');
        } elseif (is_month()) {
            $query_args['year'] = get_query_var('year');
            $query_args['monthnum'] = get_query_var('monthnum');
        } elseif (is_day()) {
            $query_args['year'] = get_query_var('year');
            $query_args['monthnum'] = get_query_var('monthnum');
            $query_args['day'] = get_query_var('day');
        }
    }
    
    $count_query = new WP_Query($query_args);
    $total_posts = $count_query->found_posts; 
    wp_reset_postdata();
    $total_pages = max(1, ceil($total_posts / $posts_per_page));
    
    // Validate current page and redirect if necessary
    if ($current_page > $total_pages && $total_pages > 0) {
        // Build redirect URL based on archive type
        $redirect_url = '';
        if (is_category()) {
            $redirect_url = get_category_link(get_queried_object_id()) . 'page/' . $total_pages . '/';
        } elseif (is_tag()) {
            $redirect_url = get_tag_link(get_queried_object_id()) . 'page/' . $total_pages . '/';
        } elseif (is_author()) {
            $redirect_url = get_author_posts_url(get_queried_object_id()) . 'page/' . $total_pages . '/';
        } elseif (is_date()) {
            if (is_year()) {
                $redirect_url = get_year_link(get_query_var('year')) . 'page/' . $total_pages . '/';
            } elseif (is_month()) {
                $redirect_url = get_month_link(get_query_var('year'), get_query_var('monthnum')) . 'page/' . $total_pages . '/';
            } elseif (is_day()) {
                $redirect_url = get_day_link(get_query_var('year'), get_query_var('monthnum'), get_query_var('day')) . 'page/' . $total_pages . '/';
            }
        }
        
        if ($redirect_url) {
            wp_safe_redirect($redirect_url);
            exit;
        }
    }
});

get_header(); ?>

<main class="wp-block-group archive-page">
    <header class="archive-header">
        <h1 class="archive-title">
            <?php 
            if (is_category()) {
                echo esc_html(single_cat_title('', false));
            } elseif (is_tag()) {
                echo esc_html(single_tag_title('', false));
            } elseif (is_author()) {
                echo esc_html(get_the_author());
            } elseif (is_date()) {
                if (is_year()) {
                    echo esc_html(get_the_date('Y'));
                } elseif (is_month()) {
                    echo esc_html(get_the_date('F Y'));
                } elseif (is_day()) {
                    echo esc_html(get_the_date());
                }
            } else {
                the_archive_title();
            }
            ?>
        </h1>
    </header>

    <?php if (is_category() && category_description()) : ?>
        <div class="is-layout-flow">
            <div class="category-description">
                <?php echo wp_kses_post(category_description()); ?>
            </div>
        </div>
    <?php endif; ?>

    <?php
    // Get pagination variables (validation already handled in template_redirect)
    $current_page = max(1, get_query_var('paged', 1));
    $pagination_type = faue_get_pagination_type();
    $items_per_page = faue_get_items_per_page();
    ?>

    <section class="content-grid" aria-label="<?php esc_attr_e('Posts listing', 'fau-elemental'); ?>">
        <?php
        // Build filter parameters for teaser grid based on archive type
        $filter_params = '';
        if (is_category()) {
            $filter_params = ',"selectedCategory":' . get_queried_object_id();
        } elseif (is_tag()) {
            $filter_params = ',"selectedTags":[' . get_queried_object_id() . ']';
        } elseif (is_author()) {
            $filter_params = ',"selectedAuthor":' . get_queried_object_id();
        }
        
        echo do_blocks('<!-- wp:fau-elemental/fau-teaser-grid {"variant":"post","selectionMode":"auto","displayStyle":"teaser-grid","teaserLayout":"3m","postsPerPage":' . $items_per_page . ',"orderBy":"date","order":"DESC","headingLevel":"h2","showPagination":true,"paginationType":"' . $pagination_type . '","currentPage":' . $current_page . $filter_params . '} /-->');
        ?>
    </section>
</main>

<?php get_footer(); ?>
