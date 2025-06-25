<?php
/**
 * The template for displaying archive pages with filtering and pagination
 *
 * @package Fau-Elemental
 */

get_header(); ?>

<main class="wp-block-group" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);">
    
    <!-- Archive Header -->
    <div class="wp-block-group" style="margin-bottom:var(--wp--preset--spacing--40);">
        <h1 class="wp-block-heading has-primary-color has-text-color" style="font-size:2.5rem;font-weight:700;">
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
        
        <?php if (is_category() || is_tag() || is_author()) : ?>
            <p class="has-secondary-color has-text-color" style="font-size:1.1rem;">
                <?php 
                if (is_category()) {
                    $description = category_description();
                    if (empty($description)) {
                        $description = sprintf(__('Browse all posts in the %s category. Use the filters below to refine your search.', 'fau-elemental'), single_cat_title('', false));
                    }
                } elseif (is_tag()) {
                    $description = tag_description();
                    if (empty($description)) {
                        $description = sprintf(__('Browse all posts tagged with %s. Use the filters below to refine your search.', 'fau-elemental'), single_tag_title('', false));
                    }
                } elseif (is_author()) {
                    $description = get_the_author_meta('description');
                    if (empty($description)) {
                        $description = sprintf(__('Browse all posts by %s. Use the filters below to refine your search.', 'fau-elemental'), get_the_author());
                    }
                }
                echo wp_kses_post($description);
                ?>
            </p>
        <?php endif; ?>
    </div>

    <!-- List Filters Block -->
    <?php
    $archive_type = '';
    $filter_params = '';
    
    if (is_category()) {
        $archive_type = 'category-' . get_queried_object_id();
        $filter_params = ',"selectedCategory":' . get_queried_object_id();
    } elseif (is_tag()) {
        $archive_type = 'tag-' . get_queried_object_id();
        $filter_params = ',"selectedTags":[' . get_queried_object_id() . ']';
    } elseif (is_author()) {
        $archive_type = 'author-' . get_queried_object_id();
        $filter_params = ',"selectedAuthor":' . get_queried_object_id();
    } elseif (is_date()) {
        if (is_year()) {
            $archive_type = 'year-' . get_query_var('year');
        } elseif (is_month()) {
            $archive_type = 'month-' . get_query_var('year') . '-' . get_query_var('monthnum');
        } elseif (is_day()) {
            $archive_type = 'day-' . get_query_var('year') . '-' . get_query_var('monthnum') . '-' . get_query_var('day');
        }
    } else {
        $archive_type = 'general-archive';
    }
    
    $filter_block_id = 'fau-list-filters-archive-' . $archive_type;
    $grid_block_id = 'fau-teaser-grid-archive-' . $archive_type;
    $pagination_block_id = 'fau-pagination-archive-' . $archive_type;
    
    // Get current page from URL parameters
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    if ($current_page === 1) {
        $current_page = get_query_var('paged') ? get_query_var('paged') : 1;
    }
    
    echo do_blocks('<!-- wp:fau-elemental/fau-list-filters {"enableSearch":true,"searchPlaceholder":"Search posts...","enableFilters":true,"filterFields":[],"showMoreFiltersButton":true,"enableViewSwitcher":true,"availableViews":["cards","table"],"defaultView":"cards","enableSorting":true,"sortOptions":[{"value":"date","label":"Latest First"},{"value":"title","label":"Alphabetical"},{"value":"modified","label":"Recently Updated"}],"defaultSort":"date","showResultsCount":true,"resultsPerPage":6,"gridWidth":"12","customBlockId":"' . $filter_block_id . '"} /-->');
    ?>

    <!-- Spacer -->
    <div style="height:2rem" aria-hidden="true" class="wp-block-spacer"></div>

    <!-- Teaser Grid Block -->
    <?php
    echo do_blocks('<!-- wp:fau-elemental/fau-teaser-grid {"variant":"post","selectionMode":"auto","displayStyle":"teaser-grid","teaserLayout":"3m","postsPerPage":6,"orderBy":"date","order":"DESC","headingLevel":"h3","showLoadMore":false,"showPagination":true,"currentPage":' . $current_page . ',"customBlockId":"' . $grid_block_id . '","filterBlockId":"' . $filter_block_id . '","paginationBlockId":"' . $pagination_block_id . '"' . $filter_params . '} /-->');
    ?>

    <!-- Spacer -->
    <div style="height:2rem" aria-hidden="true" class="wp-block-spacer"></div>

    <!-- Pagination Block -->
    <?php
    // Calculate total pages for current archive
    $posts_per_page = 6;
    $query_args = [
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids'
    ];
    
    // Add archive-specific parameters
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
    
    echo do_blocks('<!-- wp:fau-elemental/fau-pagination {"variant":"basic","currentPage":' . $current_page . ',"totalPages":' . $total_pages . ',"customBlockId":"' . $pagination_block_id . '","gridBlockId":"' . $grid_block_id . '","filterBlockId":"' . $filter_block_id . '"} /-->');
    ?>

</main>

<?php get_footer(); ?>
