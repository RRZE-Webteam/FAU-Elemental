<?php
/**
 * Template Name: All Pages
 * @package FAU-Elemental
 */

get_header(); ?>

<main class="wp-block-group all-pages-page">
    <header class="page-header">
        <h1 class="page-title">
            <?php echo esc_html(get_the_title()); ?>
        </h1>
        
        <p class="page-description">
            <?php 
            $page_description = get_post_meta(get_the_ID(), 'page_description', true);
            if (empty($page_description)) {
                $page_description = __('Browse and filter through all our pages using the options below. Use pagination to navigate through multiple pages.', 'fau-elemental');
            }
            echo esc_html($page_description);
            ?>
        </p>
    </header>

    <section class="content-filters" aria-label="<?php esc_attr_e('Filter and search options', 'fau-elemental'); ?>">
        <?php
        $filter_block_id = 'fau-list-filters-all-pages-page';
        $grid_block_id = 'fau-teaser-grid-all-pages-page';
        $pagination_block_id = 'fau-pagination-all-pages-page';
        
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        
        echo do_blocks('<!-- wp:fau-elemental/fau-list-filters {"enableSearch":true,"searchPlaceholder":"' . esc_attr__('Search pages...', 'fau-elemental') . '","enableFilters":true,"filterFields":[{"name":"page_template","label":"' . esc_attr__('All Templates', 'fau-elemental') . '","type":"meta","meta_key":"_wp_page_template"},{"name":"parent_page","label":"' . esc_attr__('All Parent Pages', 'fau-elemental') . '","type":"hierarchy"}],"showMoreFiltersButton":false,"enableViewSwitcher":true,"availableViews":["cards","table"],"defaultView":"cards","enableSorting":true,"sortOptions":[{"value":"title","label":"' . esc_attr__('Alphabetical', 'fau-elemental') . '"},{"value":"date","label":"' . esc_attr__('Latest First', 'fau-elemental') . '"},{"value":"modified","label":"' . esc_attr__('Recently Updated', 'fau-elemental') . '"}],"defaultSort":"title","showResultsCount":true,"resultsPerPage":12,"gridWidth":"12","customBlockId":"' . $filter_block_id . '"} /-->');
        ?>
    </section>

    <section class="content-grid" aria-label="<?php esc_attr_e('Pages listing', 'fau-elemental'); ?>">
        <?php
        echo do_blocks('<!-- wp:fau-elemental/fau-teaser-grid {"variant":"page","selectionMode":"auto","displayStyle":"teaser-grid","teaserLayout":"3m","postsPerPage":12,"selectedCategory":0,"orderBy":"title","order":"ASC","headingLevel":"h2","showLoadMore":false,"showPagination":true,"currentPage":' . $current_page . ',"customBlockId":"' . $grid_block_id . '","filterBlockId":"' . $filter_block_id . '","paginationBlockId":"' . $pagination_block_id . '"} /-->');
        ?>
    </section>

    <nav class="content-pagination" aria-label="<?php esc_attr_e('Pages pagination', 'fau-elemental'); ?>">
        <?php
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
    </nav>
</main>

<?php get_footer(); ?> 