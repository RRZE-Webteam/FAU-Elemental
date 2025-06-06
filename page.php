<?php

/**
 * The template for displaying pages
 *
 * @package Fau-Elemental
 */

get_header();
?>

<?php get_template_part('template-parts/hero-page'); ?>

<main>
    <?php while (have_posts()) : the_post(); ?>

        <div>
            <?php the_content(); ?>
        </div>

    <?php endwhile; ?>
</main>

<?php
get_footer();
