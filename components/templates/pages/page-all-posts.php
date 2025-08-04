<?php
/**
 * Template Name: All Posts
 * @package FAU-Elemental
 */

get_header(); ?>

<main class="wp-block-group archive-page">
    <header class="blog-header is-layout-flow">
        <h1 class="blog-title">
            <?php echo esc_html(get_the_title()); ?>
        </h1>
    </header>

    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <div class="is-layout-flow">
            <?php the_content(); ?>
        </div>
    <?php endwhile; endif; ?>

    <?php
        // Get total post count for display
        $count_query = new WP_Query([
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => 1,  // Only header query
            'no_found_rows' => false  // We need found_posts
        ]);
        $post_count = $count_query->found_posts; 
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

<?php get_footer(); ?> 