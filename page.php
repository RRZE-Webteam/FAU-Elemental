<?php
/**
 * The template for displaying pages
 *
 * @package Fau-Elemental
 */

get_header();

if (have_posts()) {
    the_post();
    get_template_part('components/template-parts/hero-page/hero-page');
    rewind_posts();
}
?>

<main id="main" class="site-main">
    <?php while (have_posts()) : the_post(); ?>

        <div class="wp-block-post-content">
            <?php the_content(); ?>
        </div>

    <?php endwhile; ?>

</main>

<?php
get_footer();
