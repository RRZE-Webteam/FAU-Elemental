<?php
/**
 * Template Name: All Pages
 * 
 * A template for displaying all pages with filtering, search functionality, and pagination.
 * 
 * @package FAU-Elemental
 */

get_header(); ?>

<main class="wp-block-group" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);">
    
    <!-- Page Header -->
    <div class="wp-block-group" style="margin-bottom:var(--wp--preset--spacing--40);">
        <h1 class="wp-block-heading has-primary-color has-text-color" style="font-size:2.5rem;font-weight:700;">
            <?php echo esc_html(get_the_title()); ?>
        </h1>
        
        <p class="has-secondary-color has-text-color" style="font-size:1.1rem;">
            <?php 
            $page_description = get_post_meta(get_the_ID(), 'page_description', true);
            if (empty($page_description)) {
                $page_description = __('Browse and filter through all our pages using the options below. Use pagination to navigate through multiple pages.', 'fau-elemental');
            }
            echo esc_html($page_description);
            ?>
        </p>
    </div>

    <!-- List Filters Block -->
    <?php
    $filter_block_id = 'fau-list-filters-all-pages-page';
    $grid_block_id = 'fau-teaser-grid-all-pages-page';
    $pagination_block_id = 'fau-pagination-all-pages-page';
    
    // Get current page from URL parameters (simple approach)
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    
    // Debug: Output what we're trying to pass
    error_log('Template Debug - Block IDs:');
    error_log('Template Debug - filter_block_id: ' . $filter_block_id);
    error_log('Template Debug - grid_block_id: ' . $grid_block_id);
    error_log('Template Debug - pagination_block_id: ' . $pagination_block_id);
    
    echo do_blocks('<!-- wp:fau-elemental/fau-list-filters {"enableSearch":true,"searchPlaceholder":"Search pages...","enableFilters":true,"filterFields":[],"showMoreFiltersButton":true,"enableViewSwitcher":true,"availableViews":["cards","table"],"defaultView":"cards","enableSorting":true,"sortOptions":[{"value":"date","label":"Latest First"},{"value":"title","label":"Alphabetical"},{"value":"modified","label":"Recently Updated"}],"defaultSort":"title","showResultsCount":true,"resultsPerPage":12,"gridWidth":"12","customBlockId":"' . $filter_block_id . '"} /-->');
    ?>

    <!-- Spacer -->
    <div style="height:2rem" aria-hidden="true" class="wp-block-spacer"></div>

    <!-- Teaser Grid Block -->
    <?php
    echo do_blocks('<!-- wp:fau-elemental/fau-teaser-grid {"variant":"page","selectionMode":"auto","displayStyle":"teaser-grid","teaserLayout":"3m","postsPerPage":12,"selectedCategory":0,"orderBy":"title","order":"ASC","headingLevel":"h3","showLoadMore":false,"showPagination":true,"currentPage":' . $current_page . ',"customBlockId":"' . $grid_block_id . '","filterBlockId":"' . $filter_block_id . '","paginationBlockId":"' . $pagination_block_id . '"} /-->');
    ?>

    <!-- Spacer -->
    <div style="height:2rem" aria-hidden="true" class="wp-block-spacer"></div>

    <!-- Pagination Block -->
    <?php
    // Calculate total pages using WP_Query for better compatibility
    $posts_per_page = 12;
    $count_query = new WP_Query([
        'post_type' => 'page',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids'
    ]);
    $total_posts = $count_query->found_posts; 
    wp_reset_postdata();
    $total_pages = max(1, ceil($total_posts / $posts_per_page));
    
    echo do_blocks('<!-- wp:fau-elemental/fau-pagination {"variant":"basic","currentPage":' . $current_page . ',"totalPages":' . $total_pages . ',"customBlockId":"' . $pagination_block_id . '","gridBlockId":"' . $grid_block_id . '","filterBlockId":"' . $filter_block_id . '"} /-->');
    ?>

</main>

<?php get_footer(); ?> 