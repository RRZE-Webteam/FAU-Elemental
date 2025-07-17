<?php
/**
 * The front page template - Enhanced for both static pages and blog homepage
 *
 * @package Fau-Elemental
 */

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

        <?php
        // Get pagination variables
        $current_page = max(1, get_query_var('paged', 1));
        $pagination_type = faue_get_pagination_type();
        $items_per_page = faue_get_items_per_page();
        ?>

        <section class="content-grid" aria-label="<?php esc_attr_e('Posts listing', 'fau-elemental'); ?>">
            <?php
            echo do_blocks('<!-- wp:fau-elemental/fau-teaser-grid {"variant":"post","selectionMode":"auto","displayStyle":"teaser-grid","teaserLayout":"3m","postsPerPage":' . $items_per_page . ',"orderBy":"date","order":"DESC","headingLevel":"h2","showPagination":true,"paginationType":"' . $pagination_type . '","currentPage":' . $current_page . '} /-->');
            ?>
        </section>
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
