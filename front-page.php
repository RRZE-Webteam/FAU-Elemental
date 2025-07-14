<?php
/**
 * The front page template - Enhanced for both static pages and blog homepage
 *
 * @package Fau-Elemental
 */

// Handle pagination validation for posts display
add_action('template_redirect', function() {
    // Only run when front page is set to show posts
    if (!is_front_page() || !is_home()) {
        return;
    }
    
    // Use WordPress standard pagination query var
    $current_page = max(1, get_query_var('paged', 1));
    
    // Calculate total pages for validation
    $posts_per_page = 6;
    $query_args = [
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids'
    ];
    
    $count_query = new WP_Query($query_args);
    $total_posts = $count_query->found_posts; 
    wp_reset_postdata();
    $total_pages = max(1, ceil($total_posts / $posts_per_page));
    
    // Validate current page and redirect if necessary
    if ($current_page > $total_pages && $total_pages > 0) {
        // Redirect to last valid page
        wp_safe_redirect(home_url('/page/' . $total_pages . '/'));
        exit;
    }
});

get_header();

// Check if we should show posts or a static page
if (is_home() && is_front_page()) {
    // Front page is set to show latest posts
    ?>
    <main class="wp-block-group blog-homepage">
        <header class="blog-header">
            <h1 class="blog-title">
                <?php 
                $blog_title = get_bloginfo('name');
                $page_title = get_theme_mod('blog_homepage_title', $blog_title);
                echo esc_html($page_title);
                ?>
            </h1>
        </header>

        <?php 
        // Check if there's a front page set and get its content
        $front_page_id = get_option('page_on_front');
        if ($front_page_id) {
            $front_page = get_post($front_page_id);
            if ($front_page && !empty($front_page->post_content)) {
                ?>
                <div class="is-layout-flow">
                    <?php echo apply_filters('the_content', $front_page->post_content); ?>
                </div>
                <?php
            }
        }
        ?>

        <section class="content-filters" aria-label="<?php esc_attr_e('Filter and search options', 'fau-elemental'); ?>">
            <?php
            // Generate unique block IDs for front page posts
            $filter_block_id = 'fau-list-filters-front-page';
            $grid_block_id = 'fau-teaser-grid-front-page';
            $pagination_block_id = 'fau-pagination-front-page';
            
            // Get pagination variables (validation already handled in template_redirect)
            $current_page = max(1, get_query_var('paged', 1));
            $posts_per_page = 6;
            $query_args = [
                'post_type' => 'post',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'fields' => 'ids'
            ];
            
            $count_query = new WP_Query($query_args);
            $total_posts = $count_query->found_posts; 
            wp_reset_postdata();
            $total_pages = max(1, ceil($total_posts / $posts_per_page));
            
            echo do_blocks('<!-- wp:fau-elemental/fau-list-filters {"enableSearch":true,"searchPlaceholder":"' . esc_attr__('Search posts...', 'fau-elemental') . '","enableFilters":true,"filterFields":[{"name":"categories","label":"' . esc_attr__('All Topics', 'fau-elemental') . '","type":"taxonomy","taxonomy":"category"},{"name":"tags","label":"' . esc_attr__('All Tags', 'fau-elemental') . '","type":"taxonomy","taxonomy":"post_tag"},{"name":"authors","label":"' . esc_attr__('All Authors', 'fau-elemental') . '","type":"author"}],"showMoreFiltersButton":true,"enableViewSwitcher":true,"availableViews":["cards","table"],"defaultView":"cards","enableSorting":true,"sortOptions":[{"value":"date","label":"' . esc_attr__('Latest First', 'fau-elemental') . '"},{"value":"title","label":"' . esc_attr__('Alphabetical', 'fau-elemental') . '"},{"value":"modified","label":"' . esc_attr__('Recently Updated', 'fau-elemental') . '"}],"defaultSort":"date","showResultsCount":true,"resultsPerPage":6,"gridWidth":"12","customBlockId":"' . $filter_block_id . '"} /-->');
            ?>
        </section>

        <section class="content-grid" aria-label="<?php esc_attr_e('Posts listing', 'fau-elemental'); ?>">
            <?php
            echo do_blocks('<!-- wp:fau-elemental/fau-teaser-grid {"variant":"post","selectionMode":"auto","displayStyle":"teaser-grid","teaserLayout":"3m","postsPerPage":6,"orderBy":"date","order":"DESC","headingLevel":"h2","showLoadMore":false,"showPagination":true,"currentPage":' . $current_page . ',"customBlockId":"' . $grid_block_id . '","filterBlockId":"' . $filter_block_id . '","paginationBlockId":"' . $pagination_block_id . '"} /-->');
            ?>
        </section>

        <nav class="content-pagination" aria-label="<?php esc_attr_e('Posts pagination', 'fau-elemental'); ?>">
            <?php
            echo do_blocks('<!-- wp:fau-elemental/fau-pagination {"variant":"basic","currentPage":' . $current_page . ',"totalPages":' . $total_pages . ',"customBlockId":"' . $pagination_block_id . '","gridBlockId":"' . $grid_block_id . '","filterBlockId":"' . $filter_block_id . '"} /-->');
            ?>
        </nav>
    </main>
    <?php
} else {
    // Front page is set to a static page - display the page content
    ?>
    <main>
        <?php
        while (have_posts()) :
            the_post();
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <?php
                // Show title if the page has one (some front pages might want to hide it)
                $show_title = get_post_meta(get_the_ID(), 'hide_title', true) !== '1';
                if ($show_title && get_the_title()) :
                ?>
                <header class="entry-header">
                    <h1 class="entry-title"><?php the_title(); ?></h1>
                </header>
                <?php endif; ?>

                <div class="entry-content">
                    <?php
                    the_content();
                    
                    wp_link_pages([
                        'before' => '<div class="page-links">' . __('Pages:', 'fau-elemental'),
                        'after'  => '</div>',
                    ]);
                    ?>
                </div>
                
                <?php if (comments_open() || get_comments_number()) : ?>
                    <div class="comments-area">
                        <?php comments_template(); ?>
                    </div>
                <?php endif; ?>
            </article>
            <?php
        endwhile;
        ?>
    </main>
    <?php
}

get_footer();
?>
