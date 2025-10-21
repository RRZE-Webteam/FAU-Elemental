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
    <main class="wp-block-group blog-homepage archive-page">
        <header class="blog-header is-layout-flow">
            <h1 class="blog-title">
                <?php 
                $blog_title = get_bloginfo('name');
                $page_title = get_theme_mod('blog_homepage_title', $blog_title);
                echo esc_html($page_title);
                ?>
            </h1>
            <?php
            $tagline = get_bloginfo('description');
            if (!empty($tagline)) : ?>
                <p class="blog-tagline"><?php echo esc_html($tagline); ?></p>
            <?php endif; ?>
        </header>

        <?php 
        // Check if there's a front page set and get its content
        $front_page_id = get_option('page_on_front');
        if (get_option('show_on_front') !== 'posts' && $front_page_id) {
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
            // Get total post count for pagination info
            $posts_query = new WP_Query([
                'post_type' => 'post',
                'post_status' => 'publish',
                'posts_per_page' => 1,  // Only header query
                'no_found_rows' => false  // We need found_posts
            ]);
            $post_count = $posts_query->found_posts;
            wp_reset_postdata();

            require_once get_template_directory() . '/components/templates/pages/archive-grid-util.php';
            fau_render_archive_grid(
                [
                    'variant' => 'post',
                    'selectedCategory' => 0,
                ],
                __('Posts listing', 'fau-elemental'),
                $post_count,
            );
        ?>

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
                <div class="is-layout-flow faue-content-wrapper">
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
