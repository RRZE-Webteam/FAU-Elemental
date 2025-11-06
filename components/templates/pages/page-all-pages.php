<?php
/**
 * Template Name: All Pages
 * @package FAU-Elemental
 */

get_header(); ?>

<main id="main" class="archive-page">
    <header class="blog-header is-layout-flow">
        <h1 class="blog-title">
            <?php echo esc_html(faue_get_page_title(get_the_ID())); ?>
        </h1>
    </header>

    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <div class="faue-content-wrapper">
            <?php the_content(); ?>
        </div>
    <?php endwhile; endif; ?>

    <?php
        // Get total post count for display
        $count_query = new WP_Query([
            'post_type' => 'page',
            'post_status' => 'publish',
            'posts_per_page' => 1,  // Only header query
            'no_found_rows' => false  // We need found_posts
        ]);
        $post_count = $count_query->found_posts; 
        wp_reset_postdata();

        require_once get_template_directory() . '/components/templates/pages/archive-grid-util.php';
        fau_render_archive_grid(
            [
                'variant' => 'page',
                'selectedCategory' => 0,
            ],
            __('Pages listing', 'fau-elemental'),
            $post_count,
        );
    ?>

</main>

<?php get_footer(); ?> 