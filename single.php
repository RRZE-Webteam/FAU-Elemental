<?php

/**
 * The template for displaying single posts
 *
 * @package FAU-Elemental
 */

get_header();
?>

<main id="main" class="site-main" role="main">
    <?php
    if (have_posts()) :
        while (have_posts()) :
            the_post();
            ?>
            <article id="post-<?php echo esc_attr(get_the_ID()); ?>" <?php post_class(); ?>>
                <?php
                // Include the post header template part
                get_template_part('components/template-parts/hero-post/hero-post');
                ?>

                <div class="is-layout-flow">
                    <?php the_content(); ?>
                </div>

                <?php
                // Include post meta (conditionally displayed)
                get_template_part('components/template-parts/post-meta/post-meta');
                ?>
            </article>
            <?php
        endwhile;
    endif;
    ?>
</main>

<?php
get_footer();
