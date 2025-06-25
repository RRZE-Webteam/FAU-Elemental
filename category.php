<?php
/**
 * The template for displaying category archive pages
 *
 * @package Fau-Elemental
 */

get_header(); ?>

<main class="wp-block-group category-archive" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);">
    
    <!-- Category Header -->
    <div class="wp-block-group archive-header" style="margin-bottom:var(--wp--preset--spacing--40);">
        <h1 class="wp-block-heading archive-title has-primary-color has-text-color" style="font-size:2.5rem;font-weight:700;">
            <?php echo esc_html(single_cat_title('', false)); ?>
        </h1>
        
        <p class="archive-description has-secondary-color has-text-color" style="font-size:1.1rem;">
            <?php 
            $description = category_description();
            if (empty($description)) {
                $description = sprintf(__('Browse all posts in the %s category. Use the filters below to refine your search and find exactly what you\'re looking for.', 'fau-elemental'), single_cat_title('', false));
            }
            echo wp_kses_post($description);
            ?>
        </p>
        
        <!-- Category Meta Information -->
        <div class="category-meta" style="margin-top:var(--wp--preset--spacing--30);font-size:0.9rem;color:var(--wp--preset--color--secondary);">
            <?php
            $category = get_queried_object();
            $post_count = $category->count;
            printf(
                /* translators: %s: Number of posts */
                _n('This category contains %s post', 'This category contains %s posts', $post_count, 'fau-elemental'),
                number_format_i18n($post_count)
            );
            ?>
        </div>
    </div>

    <!-- List Filters Block -->
    <?php
    $category_id = get_queried_object_id();
    $filter_block_id = 'fau-list-filters-category-' . $category_id;
    $grid_block_id = 'fau-teaser-grid-category-' . $category_id;
    $pagination_block_id = 'fau-pagination-category-' . $category_id;
    
    // Get current page from URL parameters
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    if ($current_page === 1) {
        $current_page = get_query_var('paged') ? get_query_var('paged') : 1;
    }
    
    echo do_blocks('<!-- wp:fau-elemental/fau-list-filters {"enableSearch":true,"searchPlaceholder":"Search in this category...","enableFilters":true,"filterFields":["tags","author","date"],"showMoreFiltersButton":true,"enableViewSwitcher":true,"availableViews":["cards","list","table"],"defaultView":"cards","enableSorting":true,"sortOptions":[{"value":"date","label":"Latest First"},{"value":"title","label":"Alphabetical"},{"value":"modified","label":"Recently Updated"},{"value":"comment_count","label":"Most Discussed"}],"defaultSort":"date","showResultsCount":true,"resultsPerPage":6,"gridWidth":"12","customBlockId":"' . $filter_block_id . '"} /-->');
    ?>

    <!-- Spacer -->
    <div style="height:2rem" aria-hidden="true" class="wp-block-spacer"></div>

    <!-- Teaser Grid Block -->
    <?php
    echo do_blocks('<!-- wp:fau-elemental/fau-teaser-grid {"variant":"post","selectionMode":"auto","displayStyle":"teaser-grid","teaserLayout":"3m","postsPerPage":6,"selectedCategory":' . $category_id . ',"orderBy":"date","order":"DESC","headingLevel":"h3","showLoadMore":false,"showPagination":true,"currentPage":' . $current_page . ',"customBlockId":"' . $grid_block_id . '","filterBlockId":"' . $filter_block_id . '","paginationBlockId":"' . $pagination_block_id . '"} /-->');
    ?>

    <!-- Spacer -->
    <div style="height:2rem" aria-hidden="true" class="wp-block-spacer"></div>

    <!-- Pagination Block -->
    <?php
    // Calculate total pages for current category
    $posts_per_page = 6;
    $count_query = new WP_Query([
        'post_type' => 'post',
        'post_status' => 'publish',
        'cat' => $category_id,
        'posts_per_page' => -1,
        'fields' => 'ids'
    ]);
    $total_posts = $count_query->found_posts; 
    wp_reset_postdata();
    $total_pages = max(1, ceil($total_posts / $posts_per_page));
    
    echo do_blocks('<!-- wp:fau-elemental/fau-pagination {"variant":"basic","currentPage":' . $current_page . ',"totalPages":' . $total_pages . ',"customBlockId":"' . $pagination_block_id . '","gridBlockId":"' . $grid_block_id . '","filterBlockId":"' . $filter_block_id . '"} /-->');
    ?>

    <!-- Related Categories -->
    <?php
    $related_categories = get_categories([
        'exclude' => $category_id,
        'number' => 5,
        'orderby' => 'count',
        'order' => 'DESC'
    ]);
    
    if (!empty($related_categories)) : ?>
        <div style="height:3rem" aria-hidden="true" class="wp-block-spacer"></div>
        
        <div class="wp-block-group related-categories" style="background-color:var(--wp--preset--color--light-grey, #f8f9fa);padding:var(--wp--preset--spacing--40);border-radius:0.5rem;">
            <h2 class="wp-block-heading" style="font-size:1.5rem;margin-bottom:var(--wp--preset--spacing--30);">
                <?php _e('Explore Other Categories', 'fau-elemental'); ?>
            </h2>
            
            <div class="category-links" style="display:flex;flex-wrap:wrap;gap:var(--wp--preset--spacing--20);">
                <?php foreach ($related_categories as $related_cat) : ?>
                    <a href="<?php echo esc_url(get_category_link($related_cat->term_id)); ?>" 
                       class="category-link" 
                       style="display:inline-block;padding:0.5rem 1rem;background:white;border:1px solid var(--wp--preset--color--light-grey, #e0e0e0);border-radius:0.25rem;text-decoration:none;color:var(--wp--preset--color--primary);font-size:0.9rem;transition:all 0.3s ease;">
                        <?php echo esc_html($related_cat->name); ?>
                        <span style="color:var(--wp--preset--color--secondary);margin-left:0.5rem;">(<?php echo $related_cat->count; ?>)</span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</main>

<?php get_footer(); ?> 