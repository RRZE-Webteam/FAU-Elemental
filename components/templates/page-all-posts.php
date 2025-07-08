<?php
/**
 * Template Name: All Posts
 * @package FAU-Elemental
 */

// Handle pagination validation before any output
add_action('template_redirect', function() {
    // Only run on this specific template
    if (!is_page() || get_page_template_slug() !== 'page-all-posts.php') {
        return;
    }
    
    // Use WordPress standard pagination query var
    $current_page = max(1, get_query_var('paged', 1));
    
    // Calculate total pages for validation
    $posts_per_page = 6;
    $count_query = new WP_Query([
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids'
    ]);
    $total_posts = $count_query->found_posts; 
    wp_reset_postdata();
    $total_pages = max(1, ceil($total_posts / $posts_per_page));
    
    // Validate current page and redirect if necessary
    if ($current_page > $total_pages && $total_pages > 0) {
        // Redirect to last valid page
        wp_safe_redirect(get_permalink() . 'page/' . $total_pages . '/');
        exit;
    }
});

get_header(); ?>

<main class="wp-block-group all-posts-page">
    <header class="page-header">
        <h1 class="page-title">
            <?php echo esc_html(get_the_title()); ?>
        </h1>
        
        <p class="page-description">
            <?php 
            $page_description = get_post_meta(get_the_ID(), 'page_description', true);
            if (empty($page_description)) {
                $page_description = __('Browse and filter through all our posts using the options below. Use pagination to navigate through multiple pages.', 'fau-elemental');
            }
            echo esc_html($page_description);
            ?>
        </p>
    </header>

    <section class="content-filters" aria-label="<?php esc_attr_e('Filter and search options', 'fau-elemental'); ?>">
        <?php
        $filter_block_id = 'fau-list-filters-all-posts-page';
        $grid_block_id = 'fau-teaser-grid-all-posts-page';
        $pagination_block_id = 'fau-pagination-all-posts-page';
        
        // Get pagination variables (validation already handled in template_redirect)
        $current_page = max(1, get_query_var('paged', 1));
        $posts_per_page = 6;
        $count_query = new WP_Query([
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ]);
        $total_posts = $count_query->found_posts; 
        wp_reset_postdata();
        $total_pages = max(1, ceil($total_posts / $posts_per_page));
        
        echo do_blocks('<!-- wp:fau-elemental/fau-list-filters {"enableSearch":true,"searchPlaceholder":"' . esc_attr__('Search posts...', 'fau-elemental') . '","enableFilters":true,"filterFields":[{"name":"categories","label":"' . esc_attr__('All Topics', 'fau-elemental') . '","type":"taxonomy","taxonomy":"category"},{"name":"tags","label":"' . esc_attr__('All Tags', 'fau-elemental') . '","type":"taxonomy","taxonomy":"post_tag"},{"name":"authors","label":"' . esc_attr__('All Authors', 'fau-elemental') . '","type":"author"}],"showMoreFiltersButton":true,"enableViewSwitcher":true,"availableViews":["cards","table"],"defaultView":"cards","enableSorting":true,"sortOptions":[{"value":"date","label":"' . esc_attr__('Latest First', 'fau-elemental') . '"},{"value":"title","label":"' . esc_attr__('Alphabetical', 'fau-elemental') . '"},{"value":"modified","label":"' . esc_attr__('Recently Updated', 'fau-elemental') . '"}],"defaultSort":"date","showResultsCount":true,"resultsPerPage":6,"gridWidth":"12","customBlockId":"' . $filter_block_id . '"} /-->');
        ?>
    </section>

    <section class="content-grid" aria-label="<?php esc_attr_e('Posts listing', 'fau-elemental'); ?>">
        <?php
        echo do_blocks('<!-- wp:fau-elemental/fau-teaser-grid {"variant":"post","selectionMode":"auto","displayStyle":"teaser-grid","teaserLayout":"3m","postsPerPage":6,"selectedCategory":0,"orderBy":"date","order":"DESC","headingLevel":"h2","showLoadMore":false,"showPagination":true,"currentPage":' . $current_page . ',"customBlockId":"' . $grid_block_id . '","filterBlockId":"' . $filter_block_id . '","paginationBlockId":"' . $pagination_block_id . '"} /-->');
        ?>
    </section>

    <nav class="content-pagination" aria-label="<?php esc_attr_e('Posts pagination', 'fau-elemental'); ?>">
        <?php
        echo do_blocks('<!-- wp:fau-elemental/fau-pagination {"variant":"basic","currentPage":' . $current_page . ',"totalPages":' . $total_pages . ',"customBlockId":"' . $pagination_block_id . '","gridBlockId":"' . $grid_block_id . '","filterBlockId":"' . $filter_block_id . '"} /-->');
        ?>
    </nav>
</main>

<?php get_footer(); ?> 